<?php
namespace PhSpring\ZF2\Annotation;
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
    
    public function onAfterClass()
    {
            echo 'elkaptam:' . __METHOD__ . PHP_EOL;
    }
    
    public function onAfterMethod()
    {
            echo 'elkaptam:' . __METHOD__ . PHP_EOL;
    }
    
    public function onBeforeMethod()
    {
            echo 'elkaptam:' . __METHOD__ . PHP_EOL;
    }
    
    public function onBeforeClass(Event $event)
    {
            $this->refName = sprintf('$ref%s', spl_object_hash($this));
            $this->code = sprintf('%s = new \%s($this->%s);', $this->refName, ReflectionClass::class, ClassGenerator::PROPERTY_NAME_INSTANCE);
            $reflection = $this->getReflection($event);
            /* @var $target \PhSpring\ZF2\Engine\ClassGenerator */
            $target = $event->getTarget();
            /* @var $property ReflectionProperty */
            foreach ($reflection->getProperties() as $property) {
                    $this->handleProperty($property);
            }
            $target->getMethod('__construct')->setBody($target->getMethod('__construct')->getBody().PHP_EOL.$this->code);
    }
}
