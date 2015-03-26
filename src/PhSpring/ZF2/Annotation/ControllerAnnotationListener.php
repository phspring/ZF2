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
use Zend\Mvc\Controller\AbstractActionController;

class ControllerAnnotationListener extends AbstractAnnotationListener
{

    /*
     * (non-PHPdoc)
     * @see \PhSpring\ZF2\Engine\AbstractAnnotationListener::onAfterClass()
     */
    public function onAfterClass()
    {
        echo 'elkaptam:' . __METHOD__ . PHP_EOL;
    }

    /*
     * (non-PHPdoc)
     * @see \PhSpring\ZF2\Engine\AbstractAnnotationListener::onAfterMethod()
     */
    public function onAfterMethod()
    {
        echo 'elkaptam:' . __METHOD__ . PHP_EOL;
    }

    /*
     * (non-PHPdoc)
     * @see \PhSpring\ZF2\Engine\AbstractAnnotationListener::onBeforeClass()
     */
    public function onBeforeClass(Event $event)
    {
        $reflection = $this->getReflection($event);
        $target=$event->getTarget();
        if(!$reflection->hasAnnotation(Controller::class)){
            return;
        }
        $interfaces = $target->getImplementedInterfaces();
        $interfaces[] = GeneratedControllerInterface::class;
        $target->setImplementedInterfaces($interfaces);
        $this->buildConstructor($target, $reflection);
        $target->setName('phs' . $target->getName());
        $extClass = explode('\\', $reflection->getName());
        $oringinClass = end($extClass);
        
        $annotation = $reflection->getAnnotation(Controller::class);
        if ($annotation instanceof CliController) {
            $target->setExtendedClass(AbstractConsoleController::class);
        } else {
            $target->setExtendedClass(AbstractActionController::class);
        }
    }

    /*
     * (non-PHPdoc)
     * @see \PhSpring\ZF2\Engine\AbstractAnnotationListener::onBeforeMethod()
     */
    public function onBeforeMethod()
    {
        echo 'elkaptam:' . __METHOD__ . PHP_EOL;
    }
    
    private function buildConstructor(ClassGenerator $generator, ReflectionClass $reflection)
    {
        $newMethod = new MethodGenerator();
        $newMethod->setName('__construct');
        $method = $generator->getMethod('__construct');
        $body = '';
        $params = [];
        if ($method) {
            foreach ($method->getParameters() as $param) {
                $paramName = 'phsParam' . $param->getName();
                $params[] = $paramName;
                $body .= sprintf('$%s = NULL;' . PHP_EOL, $paramName);
            }
            ;
            $generator->removeMethod($method->getName());
        }
        $body .= sprintf(' $this->%s = new \%s(%s);' . PHP_EOL, ClassGenerator::PROPERTY_NAME_INSTANCE, $reflection->getName(), implode(', ', $params));
        $newMethod->setBody($body);
        $generator->addMethodFromGenerator($newMethod);
    }
    
}
