<?php
namespace PhSpring\ZF2\Mvc\Controller;

use Zend\Mvc\Controller\AbstractActionController as ZendAbstractActionController;

class AbstractActionController extends ZendAbstractActionController
{

    /**
     * Transform an "action" token into a method name
     *
     * @param string $action            
     * @return string
     */
    public static function getMethodFromAction($action)
    {
        if(preg_match('/^phs-action-method-/', $action)){
            return preg_replace('/^phs-action-method-/', '', $action);
        }
        return parent::getMethodFromAction($action);
    }
}
