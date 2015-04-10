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

    private $code;

    private $refName;

    /**
     *
     * @var MethodGenerator
     */
    private $targetMethod;

    public function onBeforeMethod(Event $event)
    {
        $this->code = '';
        $reflection = $this->getReflection($event);
        /* @var $target \PhSpring\ZF2\Engine\ClassGenerator */
        $target = $event->getTarget();
        
        foreach ($reflection->getMethods() as $method) {
            $this->targetMethod = $target->getMethod($method->getName());
            if ($method->hasAnnotation(RequestParam::class)) {
                if (! $target->hasMethod('phsGetRequestParam')) {
                    $target->addMethodFromGenerator(MethodGenerator::fromReflection(new MethodReflection(self::class . '::phsGetRequestParam')));
                }
                $this->handleMethod($method);
            }
        }
    }

    /**
     * Handle only one method
     *
     * @param ReflectionMethod $method            
     */
    private function handleMethod(ReflectionMethod $method)
    {
        $target = $this->targetMethod->getBody();
        $preBody = "";
        $params = [];
        foreach ($method->getParameters() as $param) {
            $params[$param->getName()] = $param;
        }
        
        foreach ($method->getAnnotations() as $annotation) {
            if ($annotation instanceof RequestParam) {
                if (array_key_exists($annotation->value, $params)) {
                    $preBody .= sprintf('$%1$s=$this->phsGetRequestParam("%1$s",%2$b, "%3$s");' . PHP_EOL, $annotation->value, $annotation->required, $annotation->defaultValue);
                }
            }
        }
        $preBody .= $target;
        $this->targetMethod->setBody($preBody);
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
