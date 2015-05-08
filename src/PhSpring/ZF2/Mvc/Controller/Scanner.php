<?php
namespace PhSpring\ZF2\Mvc\Controller;

use Zend\Code\Reflection\ClassReflection;
use PhSpring\Annotations\Controller;
use PhSpring\Reflection\ReflectionClass;

class Scanner
{

    /**
     *
     * @var \RecursiveDirectoryIterator
     */
    private $directories;

    function __construct(\Iterator $directories)
    {
        $this->directories = $directories;
    }

    public function getControllers()
    {
        $controllers = [];
        foreach ($this->directories as $file) {
            $t = token_get_all(file_get_contents($file->getPathName()));
            $namespace = $class = '';
            while (next($t)) {
                $v = current($t);
                switch ($v[0]) {
                    case T_NAMESPACE:
                        
                        $namespace = $this->getNameSpace($t);
                        break;
                    case T_CLASS:
                        $class = $this->getClass($t);
                        break;
                }
                if ($class) {
                    $class = $namespace . '\\' . $class;
                    break;
                }
            }
            if ($class) {
                $ref = new ReflectionClass($class);
                if ($class instanceof \Zend\Mvc\Controller\AbstractActionController || $ref->hasAnnotation(Controller::class)) {
                    array_push($controllers, $class);
                }
            }
        }
        return $controllers;
    }

    private function getNameSpace(&$t)
    {
        ! defined('T_NS_SEPARATOR') && define('T_NS_SEPARATOR', '\\');
        $ns = '';
        $c = 0;
        while (current($t) != ';') {
            $v = current($t);
            if (is_array($v) && in_array($v[0], [
                T_VARIABLE,
                T_NS_SEPARATOR,
                T_STRING
            ])) {
                $ns .= $v[1];
            }
            next($t);
        }
        return $ns;
    }

    private function getClass(&$t)
    {
        $class = '';
        $c = 0;
        while ($class === '') {
            $v = current($t);
            if ($c ++ === 40) {
                break;
            }
            if (is_array($v) && in_array($v[0], [
                T_VARIABLE,
                T_STRING
            ])) {
                return $v[1];
            }
            next($t);
        }
    }
}
