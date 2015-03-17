<?php
namespace PhSpring\ZF2\Mvc\Controller\ControllerManager;

use Zend\Mvc\Service\ControllerLoaderFactory as ZCLFactory;
use Zend\ServiceManager\ServiceLocatorInterface;
use PhSpring\ZF2\Mvc\Controller\ControllerManager;

class ControllerLoaderFactory extends ZCLFactory
{

    public function createService(ServiceLocatorInterface $serviceLocator)
    {
        $controllerLoader = new ControllerManager();
        $controllerLoader->setServiceLocator($serviceLocator);
        $controllerLoader->addPeeringServiceManager($serviceLocator);
        
        $config = $serviceLocator->get('Config');
        
        if (isset($config['di']) && isset($config['di']['allowed_controllers']) && $serviceLocator->has('Di')) {
            $controllerLoader->addAbstractFactory($serviceLocator->get('DiStrictAbstractServiceFactory'));
        }
        
        return $controllerLoader;
    }
}