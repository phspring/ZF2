<?php
namespace PhSpring\ServiceManager;

use Zend\ServiceManager\AbstractFactoryInterface;
use Zend\ServiceManager\FactoryInterface;

interface AutowireCapableAbstractFactoryInterface extends AbstractFactoryInterface
{

    /**
     * Constant that indicates no externally defined autowiring.
     * Note that
     * BeanFactoryAware etc and annotation-driven injection will still be applied.
     *
     * @see #createBean
     * @see #autowire
     * @see #autowireBeanProperties
     */
    const AUTOWIRE_NO = 0;

    /**
     * Constant that indicates autowiring bean properties by name
     * (applying to all bean property setters).
     *
     * @see #createBean
     * @see #autowire
     * @see #autowireBeanProperties
     */
    const AUTOWIRE_BY_NAME = 1;

    /**
     * Constant that indicates autowiring bean properties by type
     * (applying to all bean property setters).
     *
     * @see #createBean
     * @see #autowire
     * @see #autowireBeanProperties
     */
    const AUTOWIRE_BY_TYPE = 2;

    /**
     * Constant that indicates autowiring the greediest constructor that
     * can be satisfied (involves resolving the appropriate constructor).
     *
     * @see #createBean
     * @see #autowire
     */
    const AUTOWIRE_CONSTRUCTOR = 3;

    /**
     * Return with the name of interface name
     * 
     * @return string name of interface witch is matching to the created object
     */
    public function getObjectType();
}