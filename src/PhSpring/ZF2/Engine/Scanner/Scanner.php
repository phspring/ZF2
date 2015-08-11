<?php
namespace PhSpring\ZF2\Engine\Scanner;

use Zend\Code\Reflection\ClassReflection;
use PhSpring\Annotations\Controller;
use PhSpring\Reflection\ReflectionClass;
use Zend\ServiceManager\ServiceLocatorInterface;
use PhSpring\ZF2\Mvc\View\Http\InjectTemplateListener;
use Zend\Mvc\MvcEvent;
class Scanner
{

    /**
     *
     * @var \RecursiveDirectoryIterator
     */
    private $directories;

    /**
     *
     * @var ServiceLocatorInterface
     */
    private $serviceLocator;

    /**
     *
     * @var \Zend\Cache\Storage\Adapter\Filesystem
     */
    private $cache;

    function __construct(\Iterator $directories, ServiceLocatorInterface $serviceLocator)
    {
        $this->directories = $directories;
        $this->serviceLocator = $serviceLocator;
        $this->cache = $serviceLocator->get('phsCache');
    }

    public function getController($canonicalName, $requestedName)
    {
        $ref = new ReflectionClass($requestedName);
        if (! $this->cache->hasItem($canonicalName)) {
            $this->cache->addItem($canonicalName, $requestedName);
            if (! $requestedName instanceof AbstractActionController) {
                if ($ref->hasAnnotation(Controller::class)) {
                    $parts = str_split(md5($ref->getFileName()), 2);
                    array_unshift($parts, $this->cache->getOptions()->getCacheDir());
                    $path = implode(DIRECTORY_SEPARATOR, $parts);
                    if (! file_exists($path)) {
                        mkdir($path, 0755, true);
                    }
                    $generator = $this->serviceLocator->get('ControllerGenerator');
                    $class = $generator($requestedName);
                    $classPath = $path . DIRECTORY_SEPARATOR . pathinfo($ref->getFileName(), PATHINFO_BASENAME);
                    file_put_contents($classPath, '<?php' . PHP_EOL . $class->generate());
                    
                    $this->cache->setItem($canonicalName, [
                        'name' => $class->getInvokableClassName(),
                        'class_path' => $classPath,
                        'template' => preg_replace('/\\\\/', '/', preg_replace('/(\\\\)?Controller/', '', $requestedName))
                    ]);
                }
            }
        }
        
        $data = $this->cache->getItem($canonicalName);
        if (is_array($data)) {
            $requestedName = $data['name'];
            $eventManager = $this->serviceLocator->get('EventManager');
            $sharedEvents = $eventManager->getSharedManager();
            $injectTemplateListener = new InjectTemplateListener();
            $injectTemplateListener->setControllerMap([
                $requestedName => $data['template']
            ]);
            $sharedEvents->attach('Zend\Stdlib\DispatchableInterface', MvcEvent::EVENT_DISPATCH, array(
                $injectTemplateListener,
                'injectTemplate'
            ), - 81);
            require_once $data['class_path'];
        }
        return $requestedName;
    }

    public function getControllers()
    {
        $controllers = [];
        foreach ($this->directories as $file) {
            $t = token_get_all(file_get_contents($file->getPathName()));
            $namespace = $class = '';
            while (next($t)) {
                $v = current($t);
                switch ($v[0]) {
                    case T_NAMESPACE:
                        
                        $namespace = $this->getNameSpace($t);
                        break;
                    case T_CLASS:
                        $class = $this->getClass($t);
                        break;
                }
                if ($class) {
                    $class = $namespace . '\\' . $class;
                    break;
                }
            }
            if ($class) {
                $ref = new ReflectionClass($class);
                if ($class instanceof \Zend\Mvc\Controller\AbstractActionController || $ref->hasAnnotation(Controller::class)) {
                    array_push($controllers, $class);
                }
            }
        }
        return $controllers;
    }

    private function getNameSpace(&$t)
    {
        ! defined('T_NS_SEPARATOR') && define('T_NS_SEPARATOR', '\\');
        $ns = '';
        $c = 0;
        while (current($t) != ';') {
            $v = current($t);
            if (is_array($v) && in_array($v[0], [
                T_VARIABLE,
                T_NS_SEPARATOR,
                T_STRING
            ])) {
                $ns .= $v[1];
            }
            next($t);
        }
        return $ns;
    }

    private function getClass(&$t)
    {
        $class = '';
        $c = 0;
        while ($class === '') {
            $v = current($t);
            if ($c ++ === 40) {
                break;
            }
            if (is_array($v) && in_array($v[0], [
                T_VARIABLE,
                T_STRING
            ])) {
                return $v[1];
            }
            next($t);
        }
    }
}
