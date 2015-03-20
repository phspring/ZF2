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
use PhSpring\Reflection\ReflectionClass;
use PhSpring\Annotations\Controller;
use PhSpring\ZF2\Annotations\CliController;
use Zend\Mvc\Controller\AbstractActionController;
use Zend\Mvc\Controller\AbstractConsoleController;

/**
 * Description of ControllerGenerator
 *
 * @author tothd
 */
class ControllerGenerator
{

    /**
     *
     * @var ClassGenerator
     */
    private $generator;

    /**
     *
     * @var ReflectionClass
     */
    private $phsRef;

    public function getContent($invokable)
    {
        $this->phsRef = new ReflectionClass($invokable);
        $this->generator = ClassGenerator::fromReflection(new ClassReflection($invokable));
        
        $this->buildInterfaces();
        $this->buildMethods();
        
        $this->generator->setName('phs' . $this->generator->getName());
        $extClass = explode('\\', $invokable);
        $oringinClass = end($extClass);
        
        $annotation = $this->phsRef->getAnnotation(Controller::class);
        if ($annotation instanceof CliController) {
            $this->generator->setExtendedClass(AbstractConsoleController::class);
        } else {
            $this->generator->setExtendedClass(AbstractActionController::class);
        }
        
        return $this->generator->generate();
    }

    private function buildInterfaces()
    {
        $interfaces = $this->generator->getImplementedInterfaces();
        $interfaces[] = GeneratedControllerInterface::class;
        $this->generator->setImplementedInterfaces($interfaces);
    }
    
    private function buildMethods()
    {
        foreach ($this->generator->getMethods() as $method) {
            
            $params = array_map(function ($param) {
                return '$' . $param->getName();
            }, $method->getParameters());
            
            $body = sprintf('parent::%s(%s)', $method->getName(), implode(', ', $params));

            $newMethod = new MethodGenerator();
            $newMethod->setName($method->getName());
            $newMethod->setBody($body);
            
            $this->generator->removeMethod($method->getName());
            $this->generator->addMethodFromGenerator($newMethod);
        }
    }
}