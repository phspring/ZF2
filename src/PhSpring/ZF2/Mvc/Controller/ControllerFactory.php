<?php
namespace PhSpring\ZF2\Mvc\Controller;

use Zend\ServiceManager\FactoryInterface;
use \PhSpring\ZF2\Mvc\Controller\ControllerManager;


class ControllerFactory implements FactoryInterface
{
 /* (non-PHPdoc)
     * @see \Zend\ServiceManager\FactoryInterface::createService()
     */
    public function createService(\Zend\ServiceManager\ServiceLocatorInterface $serviceLocator)
    {
var_dump($serviceLocator);die();        
        
    }

    
}
