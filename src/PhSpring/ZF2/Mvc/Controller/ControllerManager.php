<?php
namespace PhSpring\ZF2\Mvc\Controller;

use Zend\Mvc\Controller\ControllerManager as ZCM;
use PhSpring\Reflection\ReflectionClass;
use PhSpring\Annotations\Controller;
use Zend\Stdlib\DispatchableInterface;
use ProxyManager\Generator\ClassGenerator;
use Zend\Code\Reflection\ClassReflection;

class ControllerManager extends ZCM
{

    /*
     * (non-PHPdoc)
     * @see \Zend\Mvc\Controller\ControllerManager::validatePlugin()
     */
    public function validatePlugin($plugin)
    {
        if ((new ReflectionClass($plugin))->hasAnnotation(Controller::class)) {
            // we're okay
            return;
        }
        return parent::validatePlugin($plugin);
    }

    /**
     * Override: do not use peering service managers
     *
     * @param string $name            
     * @param array $options            
     * @param bool $usePeeringServiceManagers            
     * @return mixed
     */
    public function get($name, $options = array(), $usePeeringServiceManagers = false)
    {
        $instance = parent::get($name, $options, $usePeeringServiceManagers);
        if((new ReflectionClass($instance))->hasAnnotation(Controller::class) && !$instance instanceof DispatchableInterface){
            //PhSpring\ZF2\Proxy\ClassGenerator
            echo ClassGenerator::fromReflection((new ClassReflection($instance)))->setImplementedInterfaces([DispatchableInterface::class])->generate();
            die('Megvagy: '.__FILE__.':'.__LINE__);
        }
    }
}
