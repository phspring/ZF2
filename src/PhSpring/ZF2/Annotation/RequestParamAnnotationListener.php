<?php
namespace PhSpring\ZF2\Annotation;

use PhSpring\ZF2\Engine\AbstractAnnotationListener;
use PhSpring\Annotations\RequestParam;
use PhSpring\Reflection\ReflectionMethod;
use Zend\Code\Reflection\MethodReflection;
use Zend\EventManager\Event;
use Zend\Code\Generator\MethodGenerator;
use Zend\Mvc\Controller\Plugin\Params;

class RequestParamAnnotationListener extends AbstractAnnotationListener
{

    public function onBeforeMethod(Event $event)
    {
        if ($this->getReflectionMethod()->hasAnnotation(RequestParam::class)) {
            if (! $this->getTarget()->hasMethod('phsGetRequestParam')) {
                $this->getTarget()->addMethodFromGenerator(MethodGenerator::fromReflection(new MethodReflection(self::class . '::phsGetRequestParam')));
            }
            $this->handleMethod();
        }
    }

    /**
     * Handle only one method
     *
     * @param ReflectionMethod $this->getReflectionMethod()            
     */
    private function handleMethod()
    {
        $target = $this->getTargetMethod()->getBody();
        $preBody = "";
        $params = [];
        foreach ($this->getReflectionMethod()->getParameters() as $param) {
            $params[$param->getName()] = $param;
        }
        
        foreach ($this->getReflectionMethod()->getAnnotations() as $annotation) {
            if ($annotation instanceof RequestParam) {
                if (array_key_exists($annotation->value, $params)) {
                    $preBody .= sprintf('$%1$s=$this->phsGetRequestParam("%1$s",%2$b, "%3$s");' . PHP_EOL, $annotation->value, $annotation->required, $annotation->defaultValue);
                }
            }
        }
        $preBody .= $target;
        $this->getTargetMethod()->setBody($preBody);
    }

    private function phsGetRequestParam($name, $required, $default)
    {
        /* @var $params Params */
        $params = $param = $this->params();
        $param = $params->fromRoute($name);
        if (! $param) {
            $param = $this->params()->fromQuery($name);
        }
        if (! $param) {
            $param = $this->params()->fromPost($name);
        }
        if (! $param) {
            $param = $this->params()->fromHeader($name);
        }
        if (! $param) {
            if ($required) {
                throw new \InvalidArgumentException("Not defined parameter");
            } elseif ($default !== \PhSpring\Annotations\RequestParam::DEFAULT_VALUE) {
                $param = $default;
            } else {
                $param = null;
            }
        }
        return $param;
    }
}
