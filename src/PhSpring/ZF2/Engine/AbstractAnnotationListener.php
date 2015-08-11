<?php
namespace PhSpring\ZF2\Engine;

use Zend\EventManager\ListenerAggregateInterface;
use Zend\EventManager\EventManagerInterface;
use Zend\Mvc\MvcEvent;
use Zend\EventManager\Event;
use Zend\Code\Generator\MethodGenerator;
use Zend\Code\Reflection\MethodReflection;
use PhSpring\Reflection\ReflectionClass;
use PhSpring\Reflection\ReflectionMethod;

abstract class AbstractAnnotationListener implements ListenerAggregateInterface
{

    protected $code;

    private $refName;

    /**
     *
     * @var ReflectionClass
     */
    private $reflection;

    /**
     *
     * @var MethodGenerator
     */
    private $targetMethod;

    /**
     *
     * @var ReflectionMethod
     */
    private $reflectionMethod;

    /**
     *
     * @var \PhSpring\ZF2\Engine\ClassGenerator
     */
    private $target;

    const EVENT_ANNOTATION_CLASS_BEFORE = 'PhSpring.Annotation.onBeforeClass';

    const EVENT_ANNOTATION_CLASS_AFTER = 'PhSpring.Annotation.onAfterClass';

    const EVENT_ANNOTATION_METHOD_BEFORE = 'PhSpring.Annotation.onBeforeMethod';

    const EVENT_ANNOTATION_METHOD_AFTER = 'PhSpring.Annotation.onAfterMethod';

    /**
     *
     * @var \Zend\Stdlib\CallbackHandler[]
     */
    private $listeners = array();

    private function parseDefaultDataFromEvent(Event $event)
    {
        $this->code = '';
        $this->reflection = $event->getParam(ClassGenerator::PARAMETER_REFLECTION);;
        $this->target = $event->getTarget();
    }

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
        if (method_exists($this, 'onBeforeClass')) {
            $this->listeners[] = $events->attach(self::EVENT_ANNOTATION_CLASS_BEFORE, array(
                $this,
                '_onBeforeClass'
            ), 1000);
        }
        if (method_exists($this, 'onAfterClass')) {
            $this->listeners[] = $events->attach(self::EVENT_ANNOTATION_CLASS_AFTER, array(
                $this,
                '_onAfterClass'
            ), 1000);
        }
        if (method_exists($this, 'onBeforeMethod')) {
            $this->listeners[] = $events->attach(self::EVENT_ANNOTATION_METHOD_BEFORE, array(
                $this,
                '_onBeforeMethod'
            ), 1000);
        }
        if (method_exists($this, 'onAfterMethod')) {
            $this->listeners[] = $events->attach(self::EVENT_ANNOTATION_METHOD_AFTER, array(
                $this,
                '_onAfterMethod'
            ), 1000);
        }
    }

    public final function _onBeforeClass(Event $event)
    {
        $this->parseDefaultDataFromEvent($event);
        $this->onBeforeClass($event);
    }

    public final function _onAfterClass(Event $event)
    {
        $this->parseDefaultDataFromEvent($event);
        $this->onAfterClass($event);
    }

    public final function _onBeforeMethod(Event $event)
    {
        $this->parseDefaultDataFromEvent($event);
        foreach ($this->reflection->getMethods() as $method) {
            $handler = new $this();
            $handler->setReflection($this->reflection);
            $handler->setTarget($this->target);
            $handler->setTargetMethod($this->target->getMethod($method->getName()));
            $handler->setReflectionMethod($method);
            $handler->onBeforeMethod($event);
        }
    }

    public final function _onAfterMethod(Event $event)
    {
        $this->parseDefaultDataFromEvent($event);
        foreach ($this->reflection->getMethods() as $method) {
            $this->targetMethod = $this->target->getMethod($method->getName());
            $this->onAfterMethod($event);
        }
    }

    protected function copyTemplateMethod(ClassGenerator $target, $method)
    {
        $target->addMethodFromGenerator(MethodGenerator::fromReflection(new MethodReflection(get_class($this) . '::' . $method)));
    }

    /**
     *
     * @return the $code
     */
    protected function getCode()
    {
        return $this->code;
    }

    /**
     *
     * @return the $refName
     */
    protected function getRefName()
    {
        return $this->refName;
    }

    /**
     *
     * @return ReflectionClass the $reflection
     */
    protected function getReflection()
    {
        return $this->reflection;
    }

    /**
     *
     * @return ClassGenerator the $target
     */
    protected function getTarget()
    {
        return $this->target;
    }

    /**
     *
     * @param string $code            
     */
    protected function setCode($code)
    {
        $this->code = $code;
    }

    /**
     *
     * @param field_type $refName            
     */
    protected function setRefName($refName)
    {
        $this->refName = $refName;
    }

    /**
     *
     * @param ReflectionClass $reflection            
     */
    protected function setReflection(ReflectionClass $reflection)
    {
        $this->reflection = $reflection;
    }

    /**
     *
     * @param \PhSpring\ZF2\Engine\ClassGenerator $target            
     */
    protected function setTarget(ClassGenerator $target)
    {
        $this->target = $target;
    }

    /**
     *
     * @return the $targetMethod
     */
    protected function getTargetMethod()
    {
        return $this->targetMethod;
    }

    /**
     *
     * @return the $reflectionMethod
     */
    protected function getReflectionMethod()
    {
        return $this->reflectionMethod;
    }

    /**
     *
     * @param \Zend\Code\Generator\MethodGenerator $targetMethod            
     */
    protected function setTargetMethod($targetMethod)
    {
        $this->targetMethod = $targetMethod;
    }

    /**
     *
     * @param \PhSpring\Reflection\ReflectionMethod $reflectionMethod            
     */
    protected function setReflectionMethod($reflectionMethod)
    {
        $this->reflectionMethod = $reflectionMethod;
    }
}
