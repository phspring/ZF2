<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/zf2 for the canonical source repository
 * @copyright Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */
namespace PhSpring\ZF2\Mvc\Service;
use Zend\Mvc\Service\ServiceListenerFactory as ZendServiceListenerFactory;

class ServiceListenerFactory extends ZendServiceListenerFactory
{

    public function __construct()
    {
        $this->defaultServiceConfig['factories']['ControllerLoader'] = \PhSpring\ZF2\Mvc\Service\ControllerLoaderFactory::class;
    }
}
