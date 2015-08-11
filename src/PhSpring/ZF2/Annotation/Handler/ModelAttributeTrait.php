<?php
namespace PhSpring\ZF2\Annotation\Handler;

trait ModelAttributeTrait
{
    function getModelAttribute($class){
        return new $class();
    }
}
