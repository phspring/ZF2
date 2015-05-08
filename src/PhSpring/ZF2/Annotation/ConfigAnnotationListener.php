<?php
namespace PhSpring\ZF2\Annotation;

use PhSpring\Annotation\Helper;
use PhSpring\Annotations\Config;
use PhSpring\Engine\Constants;
use PhSpring\ZF2\Engine\AbstractAnnotationListener;
use PhSpring\ZF2\Engine\ClassGenerator;
use ReflectionProperty;
use Zend\Code\Generator\MethodGenerator;
use Zend\Code\Reflection\MethodReflection;
use Zend\EventManager\Event;

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of ConfigAnnotationListener
 *
 * @author tothd
 */
class ConfigAnnotationListener extends AbstractAnnotationListener
{

    private $code;

    private $refName;

    public function onBeforeClass(Event $event)
    {
        $this->code = '';
        $reflection = $this->getReflection($event);
        /* @var $target ClassGenerator */
        $target = $event->getTarget();
        if (! $target->hasMethod('phsConfig')) {
            $target->addMethodFromGenerator(MethodGenerator::fromReflection(new MethodReflection(self::class . '::phsConfig')));
        }
        /* @var $property ReflectionProperty */
        foreach ($reflection->getProperties() as $property) {
            $this->handleProperty($property);
        }
        $target->getMethod('__construct')->setBody($target->getMethod('__construct')
            ->getBody() . PHP_EOL . $this->code);
    }

    public function handleProperty(ReflectionProperty $property)
    {
        if ($property->hasAnnotation(Config::class)) {
            $annotation = $property->getAnnotation(Config::class);
            $type = Helper::getPropertyType($property);
            $isPrimitiveType = (in_array($type, Constants::$php_default_types) || in_array($type, Constants::$php_pseudo_types));
            $serviceName = "Config";
            $this->code .= sprintf('$this->phsConfig("%s", \%s::class, "%s", %d, "%s");' . PHP_EOL, $property->getName(), $serviceName, $type, $isPrimitiveType, $annotation->value);
        }
    }

    private function phsConfig($propertyName, $serviceName, $type, $isPrimitiveType, $annotationValue)
    {
        $phsService = $this->serviceLocator->get($serviceName);
        $path = explode(".", $annotationValue);
        while (! empty($path)) {
            $key = array_shift($path);
            if (array_key_exists($key, $phsService)) {
                $phsService = $phsService[$key];
            } else {
                throw new \InvalidArgumentException("Configuration key ('$annotationValue') is not available");
            }
        }
        $property = $this->phsRef->getProperty($propertyName);
        $property->setAccessible(true);
        
        if ($type !== null && ! ! $isPrimitiveType) {
            if (gettype($phsService) === $type) {
                $property->setValue($this->phsInstance, $phsService);
            } elseif ($type === "array" && is_object($phsService) && method_exists($phsService, "toArray")) {
                $property->setValue($this->phsInstance, $phsService->toArray());
            }
        } else {
            $property->setValue($this->phsInstance, $phsService);
        }
    }
}
