<?php
namespace PhSpring\ZF2\ServiceManager;

use Zend\ServiceManager\ServiceManager as Zend_ServiceManager;
use PhSpring\Reflection\ReflectionClass;

class ServiceManager extends Zend_ServiceManager
{

    private $autoAddInvokableClass = true;

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
    protected function createFromInvokable($canonicalName, $requestedName)
    {
        try {
            $cache = $this->get('phsCache');
        } catch (Exception $e) {
            return parent::createFromInvokable($canonicalName, $requestedName);
        }
        $invokable = $this->invokableClasses[$canonicalName];
        $originInvokable = $invokable;
        
        if (! class_exists($invokable)) {
            throw new ServiceNotFoundException(sprintf('%s: failed retrieving "%s%s" via invokable class "%s"; class does not exist', get_class($this) . '::' . __FUNCTION__, $canonicalName, ($requestedName ? '(alias: ' . $requestedName . ')' : ''), $invokable));
        }
        
        $ref = new ReflectionClass($invokable);
        /* @var $cache \Zend\Cache\Storage\Adapter\Filesystem */
        $cache = $this->get('phsCache');
        if (1 == 1 || ! $cache->hasItem($canonicalName)) {
            $cache->addItem($canonicalName, $invokable);
            if (! $invokable instanceof AbstractActionController) {
                if ($ref->hasAnnotation(Controller::class)) {
                    $generator = $this->get('ControllerGenerator');
                    $class = $generator($invokable);
                    $cache->setItem($canonicalName, [
                        'name' => $class->getInvokableClassName(),
                        'content' => $class->generate(),
                        'template' => preg_replace('/\\\\/', '/', preg_replace('/(\\\\)?Controller/', '', $invokable))
                    ]);
                }
            }
        }
        
        $data = $cache->getItem($canonicalName);
        if (is_array($data)) {
            $invokable = $data['name'];
            $content = $data['content'];
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
            eval($content);
        }
        /* */
        $this->creationOptions = (array) $this->creationOptions;
        array_unshift($this->creationOptions, $this->getServiceLocator()->get('ServiceManager'));
        // echo ($content);die();
        if (null === $this->creationOptions || (is_array($this->creationOptions) && empty($this->creationOptions))) {
            $instance = new $invokable();
        } else {
            $instance = new $invokable($this->creationOptions);
        }
        
        return $instance;
    }

    /**
     * Retrieve a service from the manager by name
     *
     * Allows passing an array of options to use when creating the instance.
     * createFromInvokable() will use these and pass them to the instance
     * constructor if not null and a non-empty array.
     *
     * @param string $name            
     * @param array $options            
     * @param bool $usePeeringServiceManagers            
     * @return object
     */
    public function get($name, $options = array(), $usePeeringServiceManagers = true)
    {
        // Allow specifying a class name directly; registers as an invokable class
        if (! $this->has($name) && $this->autoAddInvokableClass && class_exists($name)) {
            $this->setInvokableClass($name, $name);
        }
        
        $this->creationOptions = $options;
        $instance = parent::get($name, $usePeeringServiceManagers);
        $this->creationOptions = null;
        // $this->validatePlugin($instance);
        return $instance;
    }

    static function getPhsModulConfig()
    {
        return include __DIR__ . '/../../../../config/module.config.php';
    }
}