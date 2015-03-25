<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace PhSpring\ZF2\Annotations;

use Doctrine\Common\Annotations\Annotation\Target;
use PhSpring\Engine\AnnotationAbstract;
use PhSpring\Annotations\Controller;
/**
 * Description of Controller
 *
 * @author lobiferi
 * @Annotation
 * @Target(value="CLASS")
 * @CliController
 */
class CliController
{
    function __construct() {
        
    }
}
