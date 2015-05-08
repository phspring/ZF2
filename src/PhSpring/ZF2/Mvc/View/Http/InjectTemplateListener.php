<?php
namespace PhSpring\ZF2\Mvc\View\Http;

use Zend\Mvc\View\Http\InjectTemplateListener as ZITL;

class InjectTemplateListener extends ZITL
{

    /**
     * Inflect a name to a normalized value
     *
     * @param string $name            
     * @return string
     */
    protected function inflectName($name)
    {
        $name = parent::inflectName($name);
        return preg_replace('/^phs-action-method-/', '', $name);
    }
}
