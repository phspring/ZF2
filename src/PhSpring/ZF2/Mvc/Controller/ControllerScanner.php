<?php
namespace PhSpring\ZF2\Mvc\Controller;

use Zend\ServiceManager\AbstractFactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;
use PhSpring\Annotations\Controller;
use PhSpring\Reflection\ReflectionClass;
use PhSpring\ZF2\Engine\GeneratedControllerInterface;
use stdClass;
use Zend\Mvc\Controller\AbstractActionController;
use Zend\Mvc\Controller\ControllerManager as ZCM;
use Zend\ServiceManager\Exception\ServiceNotCreatedException;
use Zend\ServiceManager\Exception\ServiceNotFoundException;
use PhSpring\ZF2\Engine\ClassGenerator;
use PhSpring\ZF2\Mvc\View\Http\InjectTemplateListener;
use Zend\Mvc\MvcEvent;

class ControllerScanner implements AbstractFactoryInterface
{
protected $creationOptions = null;
    public function canCreateServiceWithName(ServiceLocatorInterface $serviceLocator, $name, $requestedName)
    {
        return class_exists($requestedName);
        
    }

    public function createServiceWithName(ServiceLocatorInterface $serviceLocator, $name, $requestedName)
    {
        $canonicalName = $name;
        $ref = new ReflectionClass($requestedName);
        /* @var $cache \Zend\Cache\Storage\Adapter\Filesystem */
        $cache = $serviceLocator->getServiceLocator()->get('phsCache');
        
        
        if (1 == 1 || ! $cache->hasItem($canonicalName)) {
            $cache->addItem($canonicalName, $requestedName);
            if (! $requestedName instanceof AbstractActionController) {
                if ($ref->hasAnnotation(Controller::class)) {
                    $parts = str_split(md5($ref->getFileName()), 2);
                    array_unshift($parts, $cache->getOptions()->getCacheDir());
                    $path = implode(DIRECTORY_SEPARATOR, $parts);
                    if(!file_exists($path)){
                        mkdir($path, 0755, true);
                    }
                    $generator = $serviceLocator->getServiceLocator()->get('ControllerGenerator');
                    $class = $generator($requestedName);
                    $classPath = $path.DIRECTORY_SEPARATOR.pathinfo($ref->getFileName(), PATHINFO_BASENAME);
                    file_put_contents($classPath, '<?php'.PHP_EOL.$class->generate());
        
                    $cache->setItem($canonicalName, [
                        'name' => $class->getInvokableClassName(),
                        'class_path'=>$classPath,
                        'template' => preg_replace('/\\\\/', '/', preg_replace('/(\\\\)?Controller/', '', $requestedName))
                    ]);
                }
            }
        }
        
        $data = $cache->getItem($canonicalName);
        if (is_array($data)) {
            $requestedName = $data['name'];
            $eventManager = $serviceLocator->getServiceLocator()->get('EventManager');
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
        /* */
        $this->creationOptions = (array)$this->creationOptions;
        array_unshift($this->creationOptions, $serviceLocator->getServiceLocator()->get('ServiceManager'));
        //echo ($content);die();
        if (null === $this->creationOptions || (is_array($this->creationOptions) && empty($this->creationOptions))) {
            $instance = new $requestedName();
        } else {
            $instance = new $requestedName($this->creationOptions);
        }
        
        return $instance;
        
    }
}
