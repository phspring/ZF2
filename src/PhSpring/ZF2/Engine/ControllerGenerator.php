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
use Zend\Code\Generator\DocBlockGenerator;

/**
 * Description of ControllerGenerator
 *
 * @author tothd
 * @author PhSpring
 */
class ControllerGenerator extends ClassGenerator implements EventManagerAwareInterface
{


    /**
     * @param string $invokable
     */
    public function getContent($invokable)
    {
        $this->build($invokable);
        return $this->generator->generate();
    }

    protected function build($invokable){
    	$this->phsRef = new ReflectionClass($invokable);
    	$this->generator = ClassGenerator::fromReflection(new ClassReflection($invokable));
    	$this->generator->setDocBlock(new DocBlockGenerator());
    	$this->eventManager->trigger(AbstractAnnotationListener::EVENT_ANNOTATION_CLASS_BEFORE, $this->generator, [self::PARAMETER_REFLECTION=>$this->phsRef]);
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

    /**
     *
     * @param unknown $invokable
     * @return \PhSpring\ZF2\Engine\ClassGenerator
     */
    public function __invoke($invokable){
    	$this->build($invokable);
    	return $this->generator;
    }
}