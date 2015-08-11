<?php
namespace PhSpring\ZF2\Annotation;

use PhSpring\ZF2\Engine\AbstractAnnotationListener;
use Zend\EventManager\Event;
use PhSpring\ZF2\Engine\ClassGenerator;
use PhSpring\Annotations\Controller;
use Zend\Code\Generator\MethodGenerator;
use PhSpring\Reflection\ReflectionClass;
use PhSpring\ZF2\Annotations\CliController;
use Zend\Mvc\Controller\AbstractConsoleController;
use PhSpring\ZF2\Engine\GeneratedControllerInterface;
use Zend\Code\Generator\ParameterGenerator;
use PhSpring\ZF2\Mvc\Controller\AbstractActionController;

class ControllerAnnotationListener extends AbstractAnnotationListener
{

    /*
     * (non-PHPdoc)
     * @see \PhSpring\ZF2\Engine\AbstractAnnotationListener::onBeforeClass()
     */
    public function onBeforeClass(Event $event)
    {
        if (! $this->getReflection()->hasAnnotation(Controller::class)) {
            return;
        }
        $interfaces = $this->getTarget()->getImplementedInterfaces();
        $interfaces[] = '\\' . GeneratedControllerInterface::class;
        $this->getTarget()->setImplementedInterfaces($interfaces);
        $this->buildConstructor();
        $this->cloneMethods($this->getTarget(), $this->getReflection());
        $this->getTarget()->setName(ClassGenerator::DEFAULT_PREFIX . $this->getTarget()->getName());
        $extClass = explode('\\', $this->getReflection()->getName());
        $oringinClass = end($extClass);
        
        $annotation = $this->getReflection()->getAnnotation(Controller::class);
        if ($annotation instanceof CliController) {
            $this->getTarget()->setExtendedClass('\\' . AbstractConsoleController::class);
        } else {
            $this->getTarget()->setExtendedClass('\\' . AbstractActionController::class);
        }
    }

    private function buildConstructor()
    {
        $newMethod = new MethodGenerator();
        $newMethod->setName('__construct');
        $method = $this->getTarget()->getMethod('__construct');
        $body = '';
        $params = [];
        if ($method) {
            foreach ($method->getParameters() as $param) {
                $paramName = ClassGenerator::PARAMETER_PREFIX . $param->getName();
                $body .= sprintf('$%s = NULL;' . PHP_EOL, $paramName);
                $params[] = '$' . $paramName;
            }
            ;
            $this->getTarget()->removeMethod($method->getName());
        }
        $body .= sprintf('
        $this->serviceLocator = $params[0];
        $this->%1$s = new \%2$s(%3$s);
        $this->phsRef = new \PhSpring\Reflection\ReflectionClass($this->%1$s);' . PHP_EOL, ClassGenerator::PROPERTY_NAME_INSTANCE, $this->getReflection()->getName(), implode(', ', $params));
        $newMethod->setParameter((new ParameterGenerator())->setName('params'));
        $newMethod->setBody($body);
        $this->getTarget()->addMethodFromGenerator($newMethod);
    }

    private function cloneMethods()
    {
        foreach ($this->getTarget()->getMethods() as $method) {
            if ($method->getName() == '__construct') {
                continue;
            }
            $newMethod = $this->cloneMethod($method);
            $this->getTarget()->removeMethod($method->getName());
            $this->getTarget()->addMethodFromGenerator($newMethod);
        }
    }

    /**
     *
     * @param MethodGenerator $method                      
     * @return string|\Zend\Code\Generator\MethodGenerator
     */
    private function cloneMethod(MethodGenerator $method)
    {
        $params = array_map(function ($param) {
            return '$' . $param->getName();
        }, $method->getParameters());
        
        $body = sprintf(' return $this->%s->%s(%s);', ClassGenerator::PROPERTY_NAME_INSTANCE, $method->getName(), implode(', ', $params));
        
        $newMethod = new MethodGenerator();
        $newMethod->setName($method->getName());
        $newMethod->setBody($body);
        return $newMethod;
    }
}
