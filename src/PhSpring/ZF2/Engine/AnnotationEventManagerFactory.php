<?php
namespace PhSpring\ZF2\Engine;

use Zend\Mvc\Service\EventManagerFactory;
use Zend\ServiceManager\ServiceLocatorInterface;
use Zend\EventManager\SharedEventManager;

class AnnotationEventManagerFactory extends EventManagerFactory
{

    /**
     *
     * @var array
     */
    private $config;

    public function createService(ServiceLocatorInterface $serviceLocator)
    {
        $em = $serviceLocator->get('SharedEventManager');
        $config = $this->getConfig($serviceLocator);
        foreach ($config as $event) {
            $em->attachAggregate(new $event());
        }
        return $em;
    }

    protected function getConfig(ServiceLocatorInterface $serviceLocator)
    {
        if ($this->config) {
            return $this->config;
        }
        $this->config = (array) $serviceLocator->get('Config')['annotation_events'];
        return $this->config;
    }
}