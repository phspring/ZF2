<?php
namespace PhSpring\ZF2\Annotation;

use PhSpring\ZF2\Engine\AbstractAnnotationListener;
use Zend\EventManager\Event;
use PhSpring\ZF2\Engine\ClassGenerator;
use PhSpring\Annotations\Controller;
use Zend\Code\Generator\MethodGenerator;
use PhSpring\Reflection\ReflectionClass;
use PhSpring\ZF2\Annotations\CliController;
use Zend\Mvc\Controller\AbstractConsoleController;
use Zend\Mvc\Controller\AbstractActionController;
use PhSpring\ZF2\Engine\GeneratedControllerInterface;
use PhSpring\Reflection\ReflectionProperty;

class AutowiredAnnotationListener extends AbstractAnnotationListener
{

    /*
     * (non-PHPdoc)
     * @see \PhSpring\ZF2\Engine\AbstractAnnotationListener::onAfterClass()
     */
    public function onAfterClass()
    {
        echo 'elkaptam:' . __METHOD__ . PHP_EOL;
    }

    /*
     * (non-PHPdoc)
     * @see \PhSpring\ZF2\Engine\AbstractAnnotationListener::onAfterMethod()
     */
    public function onAfterMethod()
    {
        echo 'elkaptam:' . __METHOD__ . PHP_EOL;
    }

    /*
     * (non-PHPdoc)
     * @see \PhSpring\ZF2\Engine\AbstractAnnotationListener::onBeforeClass()
     */
    public function onBeforeClass(Event $event)
    {
        $reflection = $this->getReflection($event);
        $target = $event->getTarget();
        /* @var $property ReflectionProperty */
        foreach($reflection->getProperties() as $property){
            
            
        }
        
    }

    /*
     * (non-PHPdoc)
     * @see \PhSpring\ZF2\Engine\AbstractAnnotationListener::onBeforeMethod()
     */
    public function onBeforeMethod()
    {
        echo 'elkaptam:' . __METHOD__ . PHP_EOL;
    }

}
