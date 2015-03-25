<?php
namespace PhSpring\ZF2;

use Zend\Mvc\MvcEvent;
use PhSpring\ZF2\Engine\AnnotationEventManagerFactory;
class Module
{
    public function onBootstrap(MvcEvent $e)
    {
        $eventManager        = $e->getApplication()->getEventManager();
		$app = $e->getApplication();
		$sm = $app->getServiceManager();
		$generator = $app->getServiceManager()->get('ControllerGenerator');
		$em = $generator->getEventManager();
		$config = $sm->get('Config');
		if(array_key_exists('annotation_events', $config)){
		    foreach ($config['annotation_events'] as $event) {
		        $em->attachAggregate(new $event());
		    }
		    
		}
    }
    
    public function getConfig()
    {
        return include __DIR__ . '/../../../config/module.config.php';
    }
    
    public function getAutoloaderConfig()
    {
        return array(
            'Zend\Loader\StandardAutoloader' => array(
                'namespaces' => array(
                    __NAMESPACE__ => __DIR__ . '/src/' . __NAMESPACE__,
                ),
            ),
        );
    }
}
