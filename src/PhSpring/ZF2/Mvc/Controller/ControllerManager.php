<?php
namespace PhSpring\ZF2\Mvc\Controller;

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

class ControllerManager extends ZCM
{

    /**
     * Attempt to create an instance via an invokable class
     *
     * Overrides parent implementation by passing $creationOptions to the
     * constructor, if non-null.
     *
     * @param string $canonicalName
     * @param string $requestedName
     * @return null|stdClass
     * @throws ServiceNotCreatedException If resolved class does not exist
     */
    protected function _createFromInvokable($canonicalName, $requestedName)
    {
        $invokable = $this->invokableClasses[$canonicalName];
        $originInvokable = $invokable;

        if (! class_exists($invokable)) {
            throw new ServiceNotFoundException(sprintf('%s: failed retrieving "%s%s" via invokable class "%s"; class does not exist', get_class($this) . '::' . __FUNCTION__, $canonicalName, ($requestedName ? '(alias: ' . $requestedName . ')' : ''), $invokable));
        }

        //$ref = new ReflectionClass($invokable);
        /* @var $cache \Zend\Cache\Storage\Adapter\Filesystem */
        $cache = $this->getServiceLocator()->get('phsCache');
        var_dump(__METHOD__);die;
        
        
//         if (1 == 1 || ! $cache->hasItem($canonicalName)) {
//             $cache->addItem($canonicalName, $invokable);
//             if (! $invokable instanceof AbstractActionController) {
//                 if ($ref->hasAnnotation(Controller::class)) {
//                     $parts = str_split(md5($ref->getFileName()), 2);
//                     array_unshift($parts, $cache->getOptions()->getCacheDir());
//                     $path = implode(DIRECTORY_SEPARATOR, $parts);
//                     if(!file_exists($path)){
//                         mkdir($path, 0755, true);
//                     }
//                     $generator = $this->getServiceLocator()->get('ControllerGenerator');
//                     $class = $generator($invokable);
//                     $classPath = $path.DIRECTORY_SEPARATOR.pathinfo($ref->getFileName(), PATHINFO_BASENAME);
//                     file_put_contents($classPath, '<?php'.PHP_EOL.$class->generate());
                    
//                     $cache->setItem($canonicalName, [
//                         'name' => $class->getInvokableClassName(),
//                         'class_path'=>$classPath,
//                         'template' => preg_replace('/\\\\/', '/', preg_replace('/(\\\\)?Controller/', '', $invokable))
//                     ]);
//                 }
//             }
//         }

        $data = $cache->getItem($canonicalName);
        if (is_array($data)) {
            $invokable = $data['name'];
            $eventManager = $this->getServiceLocator()->get('EventManager');
            $sharedEvents = $eventManager->getSharedManager();
            $injectTemplateListener = new InjectTemplateListener();
            $injectTemplateListener->setControllerMap([
                $invokable => $data['template']
            ]);
            $sharedEvents->attach('Zend\Stdlib\DispatchableInterface', MvcEvent::EVENT_DISPATCH, array(
                $injectTemplateListener,
                'injectTemplate'
            ), - 81);
            require_once $data['class_path'];
        }
        /* */
        $this->creationOptions = (array)$this->creationOptions;
        array_unshift($this->creationOptions, $this->getServiceLocator()->get('ServiceManager'));
        //echo ($content);die();
        if (null === $this->creationOptions || (is_array($this->creationOptions) && empty($this->creationOptions))) {
            $instance = new $invokable();
        } else {
            $instance = new $invokable($this->creationOptions);
        }

        return $instance;
    }
    private function getConfig(){
        $this->get('Config');
        
    } 
    
}
