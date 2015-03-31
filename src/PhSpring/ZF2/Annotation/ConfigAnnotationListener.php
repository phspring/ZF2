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
        $this->refName = sprintf('$ref%s', spl_object_hash($this));
        $this->code = ''; //sprintf('%s = new \%s($this->%s);', $this->refName, \ReflectionClass::class, ClassGenerator::PROPERTY_NAME_INSTANCE);
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
        $target->getMethod('__construct')->setBody($target->getMethod('__construct')->getBody() . PHP_EOL . $this->code);
    }

    public function handleProperty(ReflectionProperty $property)
    {
        if ($property->hasAnnotation(Config::class)) {
            $annotation = $property->getAnnotation(Config::class);
            if (isset($property->getAnnotation(Config::class)->value))
                return;
            $type = Helper::getPropertyType($property);
            $isPrimitiveType = (in_array($type, Constants::$php_default_types) || in_array($type, Constants::$php_pseudo_types));
            $serviceName = "Config";
            $this->code .= sprintf('$this->phsConfig("%s", \%s::class, "%s");' . PHP_EOL, $property->getName(), $serviceName, $type, $this->refName, ClassGenerator::PROPERTY_NAME_INSTANCE, $isPrimitiveType, $annotation->value);
            
            /*
            $this->code .= sprintf('
                $phsConfig%1$s = $serviceLocator->get("%2$s");
                $path = explode(".", "%7$s");
                while (!empty($path)) {
                    $phsConfig%1$s = $phsConfig%1$s[array_shift($path)];
                }
				%4$sref=%4$s->getProperty("%1$s");
				%4$sref->setAccessible(true);
                
                if ("%3$s" !== null && !!"%6$s") {
                    if (gettype($phsConfig%1$s) === "%3$s") {
                        %4$sref->setValue($this->%5$s, $phsConfig%1$s);
                    }elseif("%3$s"==="array" && method_exists($phsConfig%1$s, "toArray")){
                        %4$sref->setValue($this->%5$s, $phsConfig%1$s->toArray());
                    }
                }else{
                    %4$sref->setValue($this->%5$s, $phsConfig%1$s);
                }
            ' . PHP_EOL, $property->getName(), $serviceName, $type, $this->refName, ClassGenerator::PROPERTY_NAME_INSTANCE, $isPrimitiveType, $annotation->value);
             */
        }
    }
    
    private function phsConfig($service, $serviceName, $type, $refName, $nameInstance, $isPrimitiveType, $annotationValue)
    {
        $phsService = '$phsConfig' . $service;
        $refNameRef = $refName . 'ref';
        
        $phsService = $this->serviceLocator->get($serviceName);
        $path = explode(".", $annotationValue);
        while (!empty($path)) {
             $phsService =  $phsService[array_shift($path)];
        }
        $refNameRef = $refName->getProperty($service);
        $refNameRef->setAccessible(true);

        if ($type !== null && !!$isPrimitiveType) {
            if (gettype($phsService) === $type) {
                $refNameRef->setValue($this->$nameInstance, $phsService);
            } elseif ($type === "array" && method_exists($phsService, "toArray")){
                $refNameRef->setValue($this->$nameInstance, $phsService->toArray());
            }
        } else {
            $refNameRef->setValue($this->$nameInstance, $phsService);
        }
    }
}
