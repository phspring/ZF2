<?php
namespace PhSpring\ServiceManager;

use Zend\ServiceManager\FactoryInterface;

interface AutowireCapableFactoryInterface extends FactoryInterface
{
    /**
     * Return with the name of concret class or interface name
     * @return string name of class or interface witch is matching to the created object
     */
    public function getObjectType();
}