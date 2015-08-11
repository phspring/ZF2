<?php
namespace PhSpring\ZF2\Annotation;

use PhSpring\ZF2\Engine\AbstractAnnotationListener;
use PhSpring\Annotations\ModelAttribute;
use PhSpring\Reflection\ReflectionMethod;
use Zend\EventManager\Event;
use ReflectionParameter;
use PhSpring\ZF2\Annotation\Handler\ModelAttributeTrait;

class ModelAttributeListener extends AbstractAnnotationListener
{

    public function onBeforeMethod(Event $event)
    {
        if (! $this->getReflectionMethod()->hasAnnotation(ModelAttribute::class)) {
            return;
        }
        $this->getTarget();
        $annotations = $this->getReflectionMethod()->getAnnotation(ModelAttribute::class);
        $parameters = $this->getReflectionMethod()->getParameters();
        $name = $annotations->value;
        $param = array_filter($parameters, function (\ReflectionParameter $item) use($name) {
            return $item->getName() == $name;
        });
        $param = $param[0];
        $class = \stdClass::class;
        /* @var $param ReflectionParameter */
        if ($param->canBePassedByValue()) {
            $class = $param->getClass()->getName();
        }
        
        if (! $this->getTarget()->hasTrait('\\'.ModelAttributeTrait::class)) {
            $this->getTarget()->addTrait('\\'.ModelAttributeTrait::class);
        }
        
        $body = "\$$name = \$this->getModelAttribute(\\$class::class);";
        $body .= $this->getTargetMethod()->getBody();
        $this->getTargetMethod()->setBody($body);
        var_dump($param);
        var_dump($annotations);
    }
}