<?php
namespace PhSpring\ZF2\Mvc\Controller\ControllerManager;

use Zend\Mvc\Service\ControllerLoaderFactory;
use Zend\ServiceManager\ServiceLocatorInterface;

class ControllerLoaderFactory extends ControllerLoaderFactory
{
    public function createService(ServiceLocatorInterface $serviceLocator){
        die('na! Megvagy!');
        parent::createService($serviceLocator);
    }
}