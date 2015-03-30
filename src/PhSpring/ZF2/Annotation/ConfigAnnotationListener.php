<?php
namespace PhSpring\ZF2\Annotation;

use PhSpring\ZF2\Engine\AbstractAnnotationListener;
use Zend\EventManager\Event;
use PhSpring\ZF2\Engine\ClassGenerator;
use PhSpring\Annotations\Config;
use PhSpring\Annotation\Helper;
use PhSpring\Engine\Constants;

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
        $this->code = sprintf('%s = new \%s($this->%s);', $this->refName, \ReflectionClass::class, ClassGenerator::PROPERTY_NAME_INSTANCE);
        $reflection = $this->getReflection($event);
        /* @var $target \PhSpring\ZF2\Engine\ClassGenerator */
        $target = $event->getTarget();
        /* @var $property ReflectionProperty */
        foreach ($reflection->getProperties() as $property) {
            $this->handleProperty($property);
        }
        $target->getMethod('__construct')->setBody($target->getMethod('__construct')
            ->getBody() . PHP_EOL . $this->code);
    }

    public function handleProperty(\ReflectionProperty $property)
    {
        if ($property->hasAnnotation(Config::class)) {
            $annotation = $property->getAnnotation(Config::class);
            $type = Helper::getPropertyType($property);
            $isPrimitiveType = (in_array($type, Constants::$php_default_types) || in_array($type, Constants::$php_pseudo_types));
            $serviceName = "Config";
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
        }
    }
}
