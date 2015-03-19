<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace PhSpring\ZF2\Engine;

use Zend\Code\Generator\ClassGenerator;
use Zend\Code\Generator\DocBlockGenerator;
use Zend\Code\Reflection\ClassReflection;

/**
 * Description of ControllerGenerator
 *
 * @author tothd
 */
class ControllerGenerator {
    protected function getContent($invokable)
    {
        $generated = ClassGenerator::fromReflection(new ClassReflection($invokable));
        $interfaces = $generated->getImplementedInterfaces();
        $interfaces[]=GeneratedController::class;
        $generated->setImplementedInterfaces($interfaces);
        $generated->setDocBlock(new DocBlockGenerator());
        //$config =$this->getServiceLocator()->get('servicemanager')->get('Config'); 
        //$this->serviceLocator->get('ServiceManager')->get('ServiceManager')->get('ServiceManager')->get('Config');
        //var_dump($config);
        $generated->setName('phs'.$generated->getName());
        $extClass = explode('\\',$invokable);
        $generated->setExtendedClass(end($extClass));
        
        return $generated->generate();
    }
}