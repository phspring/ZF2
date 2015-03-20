<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace PhSpring\ZF2\Engine;

use Zend\Code\Generator\ClassGenerator;
use Zend\Code\Generator\MethodGenerator;
use Zend\Code\Reflection\ClassReflection;

/**
 * Description of ControllerGenerator
 *
 * @author tothd
 */
class ControllerGenerator {
    
    /**
     *
     * @var ClassGenerator
     */
    private $generator;
    
    public function getContent($invokable)
    {
        $this->generator = ClassGenerator::fromReflection(new ClassReflection($invokable));
        
        $this->buildInterfaces();
        $this->buildMethods();
        //$config =$this->getServiceLocator()->get('servicemanager')->get('Config'); 
        //$this->serviceLocator->get('ServiceManager')->get('ServiceManager')->get('ServiceManager')->get('Config');
        //var_dump($config);
        $this->generator->setName('phs'.$this->generator->getName());
        $extClass = explode('\\',$invokable);
        $this->generator->setExtendedClass(end($extClass));
        
        return $this->generator->generate();
    }
    
    private function buildInterfaces()
    {
        $interfaces = $this->generator->getImplementedInterfaces();
        $interfaces[]=GeneratedControllerInterface::class;
        $this->generator->setImplementedInterfaces($interfaces);
    }
    
    private function buildMethods()
    {
        foreach ($this->generator->getMethods() as $method)
        {
            $body = 'parent::' . $method->getName() . '(';
            $params = array_map(function ($param) {
                return '$' . $param->getName();
            }, $method->getParameters());
            $body .= implode(', ', $params) . ');';
            $newMethod = new MethodGenerator();
            $newMethod->setName($method->getName());
            $newMethod->setBody($body);
            
            $this->generator->removeMethod($method->getName());
            $this->generator->addMethodFromGenerator($newMethod);
        }
    }
}