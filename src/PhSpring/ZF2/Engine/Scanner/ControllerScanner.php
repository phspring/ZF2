<?php
namespace PhSpring\ZF2\Engine\Scanner;

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
    public function canCreateServiceWithName(ServiceLocatorInterface $serviceLocator, $canonicalName, $requestedName)
    {
        return class_exists($requestedName);
        
    }

    public function createServiceWithName(ServiceLocatorInterface $serviceLocator, $canonicalName, $requestedName)
    {
        $concreteController = $serviceLocator->getServiceLocator()->get('ComponentScanner')->getController($canonicalName, $requestedName);        
        $this->creationOptions = (array)$this->creationOptions;
        array_unshift($this->creationOptions, $serviceLocator->getServiceLocator()->get('ServiceManager'));
        if (null === $this->creationOptions || (is_array($this->creationOptions) && empty($this->creationOptions))) {
            $instance = new $concreteController();
        } else {
            $instance = new $concreteController($this->creationOptions);
        }
        
        return $instance;
        
    }
}
