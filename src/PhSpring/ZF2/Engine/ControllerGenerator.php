<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */
namespace PhSpring\ZF2\Engine;

use Zend\Code\Generator\MethodGenerator;
use Zend\Code\Reflection\ClassReflection;
use PhSpring\Reflection\ReflectionClass;
use PhSpring\Annotations\Controller;
use PhSpring\ZF2\Annotations\CliController;
use Zend\Mvc\Controller\AbstractActionController;
use Zend\Mvc\Controller\AbstractConsoleController;
use Zend\Code\Generator\ParameterGenerator;
use Zend\EventManager\ListenerAggregateInterface;
use Zend\EventManager\EventManagerAwareInterface;
use Zend\EventManager\EventManagerInterface;

/**
 * Description of ControllerGenerator
 *
 * @author tothd
 * @author PhSpring
 */
class ControllerGenerator extends ClassGenerator implements EventManagerAwareInterface
{
    
    /**
     * @var EventManagerInterface
     */
    private $eventManager;

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

    /**
     * @param string $invokable
     */
    public function getContent($invokable)
    {
        $this->phsRef = new ReflectionClass($invokable);
        $this->generator = ClassGenerator::fromReflection(new ClassReflection($invokable));
        $this->eventManager->trigger(AbstractAnnotationListener::EVENT_ANNOTATION_CLASS_BEFORE, $this->generator, [self::PARAMETER_REFLECTION=>$this->phsRef]);
        
        //$this->buildMethods();
        
        return $this->generator->generate();
    }

    private function buildMethods()
    {
        foreach ($this->generator->getMethods() as $method) {
            if ($method->getName() == '__construct') {
                $this->buildConstructor();
                continue;
            }
            $params = array_map(function ($param) {
                return '$' . $param->getName();
            }, $method->getParameters());
            
            $body = sprintf(' return $this->%s->%s(%s);', self::PROPERTY_NAME_INSTANCE, $method->getName(), implode(', ', $params));
            
            $newMethod = new MethodGenerator();
            $newMethod->setName($method->getName());
            $newMethod->setBody($body);
            
            $this->generator->removeMethod($method->getName());
            $this->generator->addMethodFromGenerator($newMethod);
        }
    }

    private function buildConstructor()
    {
        $newMethod = new MethodGenerator();
        $newMethod->setName('__construct');
        $method = $this->generator->getMethod('__construct');
        $body = '';
        $params = [];
        if ($method) {
            foreach ($method->getParameters() as $param) {
                $paramName = 'phsParam' . $param->getName();
                $params[] = $paramName;
                $body .= sprintf('$%s = NULL;' . PHP_EOL, $paramName);
            }
            ;
            $this->generator->removeMethod($method->getName());
        }
        $body .= sprintf(' $this->%s = new \%s(%s);' . PHP_EOL, self::PROPERTY_NAME_INSTANCE, $this->phsRef->getName(), implode(', ', $params));
        $newMethod->setBody($body);
        $this->generator->addMethodFromGenerator($newMethod);
    }

    /**
     * @param EventManagerInterface $eventManager
     */
    public function setEventManager(EventManagerInterface $eventManager)
    {
        $this->eventManager = $eventManager;
    }

    /**
     * @return \Zend\EventManager\EventManagerInterface
     */
    public function getEventManager()
    {
        return $this->eventManager;
    }
}