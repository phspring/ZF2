<?php
namespace PhSpring\ZF2\Engine;

use Zend\Code\Generator\ClassGenerator as ZfClassGenerator;

class ClassGenerator extends ZfClassGenerator
{
    /**
     * @var EventManagerInterface
     */
    protected $eventManager;

    /**
     *
     * @var ClassGenerator
     */
    protected $generator;

    /**
     *
     * @var ReflectionClass
     */
    protected $phsRef;

	const PROPERTY_NAME_INSTANCE = 'phsInstance';

    const PARAMETER_REFLECTION = 'phsReflection';

    const DEFAULT_PREFIX = 'phs';

    const PARAMETER_PREFIX = 'phsParam';

    public function getInvokableClassName(){
    	return $this->generator===null ? $this->getNamespaceName().'\\'.$this->getName(): $this->generator->getNamespaceName().'\\'.$this->generator->getName();
    }
}
