<?php
namespace PhSpring\ZF2\Engine;

use Zend\Code\Generator\ClassGenerator as ZfClassGenerator;

class ClassGenerator extends ZfClassGenerator
{
    const PROPERTY_NAME_INSTANCE = 'phsInstance';
    const PARAMETER_REFLECTION = 'phsReflection';
    const DEFAULT_PREFIX = 'phs';
    const PARAMETER_PREFIX = 'phsParam';
    
    }
