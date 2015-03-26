<?php
namespace PhSpring\ZF2\Engine;

use Zend\EventManager\ListenerAggregateInterface;
use Zend\EventManager\EventManagerInterface;
use Zend\Mvc\MvcEvent;
use Zend\EventManager\Event;

abstract class AbstractAnnotationListener implements ListenerAggregateInterface
{

    const EVENT_ANNOTATION_CLASS_BEFORE = 'PhSpring.Annotation.onBeforeClass';

    const EVENT_ANNOTATION_CLASS_AFTER = 'PhSpring.Annotation.onAfterClass';

    const EVENT_ANNOTATION_METHOD_BEFORE = 'PhSpring.Annotation.onBeforeMethod';

    const EVENT_ANNOTATION_METHOD_AFTER = 'PhSpring.Annotation.onAfterMethod';

    /**
     *
     * @var \Zend\Stdlib\CallbackHandler[]
     */
    private $listeners = array();

    public function detach(EventManagerInterface $events)
    {
        foreach ($this->listeners as $index => $listener) {
            if ($events->detach($listener)) {
                unset($this->listeners[$index]);
            }
        }
    }

    public function attach(EventManagerInterface $events)
    {
        $this->listeners[] = $events->attach(self::EVENT_ANNOTATION_CLASS_BEFORE, array(
            $this,
            'onBeforeClass'
        ), 1000);
        $this->listeners[] = $events->attach(self::EVENT_ANNOTATION_CLASS_AFTER, array(
            $this,
            'onAfterClass'
        ), 1000);
        $this->listeners[] = $events->attach(self::EVENT_ANNOTATION_METHOD_BEFORE, array(
            $this,
            'onBeforeMethod'
        ), 1000);
        $this->listeners[] = $events->attach(self::EVENT_ANNOTATION_METHOD_AFTER, array(
            $this,
            'onAfterMethod'
        ), 1000);
    }

    public function onBeforeClass()
    {}

    public function onAfterClass()
    {}

    public function onBeforeMethod()
    {}

    public function onAfterMethod()
    {}    
    
    /**
     * 
     * @param Event $event
     * @return \Reflection 
     */
    protected function getReflection( Event $event){
        return $event->getParam(ClassGenerator::PARAMETER_REFLECTION);
    }
}
