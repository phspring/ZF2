<?php
namespace PhSpring\ZF2\Mvc\Controller;

use Zend\Mvc\Controller\ControllerManager as ZCM;
use PhSpring\Reflection\ReflectionClass;
use PhSpring\ZF2\Engine\GeneratedController;
use Zend\Mvc\Controller\AbstractActionController;
use PhSpring\Annotations\Controller;
use Zend\Code\Reflection\ClassReflection;
use Zend\Code\Generator\DocBlockGenerator;
use Zend\ServiceManager\ServiceManager;

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
     * @return null|\stdClass
     * @throws Exception\ServiceNotCreatedException If resolved class does not exist
     */
    protected function createFromInvokable($canonicalName, $requestedName)
    {
        $invokable = $this->invokableClasses[$canonicalName];
        
        if (! class_exists($invokable)) {
            throw new Exception\ServiceNotFoundException(sprintf('%s: failed retrieving "%s%s" via invokable class "%s"; class does not exist', get_class($this) . '::' . __FUNCTION__, $canonicalName, ($requestedName ? '(alias: ' . $requestedName . ')' : ''), $invokable));
        }
        
        $ref = new ReflectionClass($invokable);
        if(!(in_array(GeneratedController::class, $ref->getInterfaceNames()) || $invokable instanceof AbstractActionController)){
            if($ref->hasAnnotation(Controller::class)){
                $generated = \Zend\Code\Generator\ClassGenerator::fromReflection(new ClassReflection($invokable));
                $interfaces = $generated->getImplementedInterfaces();
                $interfaces[]=GeneratedController::class;
                $generated->setImplementedInterfaces($interfaces);
                $generated->setDocBlock(new DocBlockGenerator());
                //$config =$this->getServiceLocator()->get('servicemanager')->get('Config'); 
                //$this->serviceLocator->get('ServiceManager')->get('ServiceManager')->get('ServiceManager')->get('Config');
                //var_dump($config);
                echo '<pre>'.$generated->generate();
                die();
            }
            
        }
        
        if (null === $this->creationOptions || (is_array($this->creationOptions) && empty($this->creationOptions))) {
            $instance = new $invokable();
        } else {
            $instance = new $invokable($this->creationOptions);
        }
        
        return $instance;
    }
}
