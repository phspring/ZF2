<?php
namespace PhSpring\ZF2\Mvc\Service;

use Zend\Mvc\Service\ServiceListenerFactory as ZendServiceListenerFactory;
use Zend\ServiceManager\ServiceLocatorInterface;

class ServiceListenerFactory extends ZendServiceListenerFactory
{
    public function createService(ServiceLocatorInterface $serviceLocator){
        $this->defaultServiceConfig['factories']['ControllerLoader']=\PhSpring\ZF2\Mvc\Controller\ControllerManager\ControllerLoaderFactory::class;
        return parent::createService($serviceLocator);
    }
}
