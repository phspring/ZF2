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
use PhSpring\ZF2\Engine\GeneratedControllerInterface;
use PhSpring\Reflection\ReflectionProperty;
use PhSpring\Annotations\Autowired;
use PhSpring\Annotation\Helper;
use PhSpring\Engine\Constants;
use PhSpring\Annotations\Qualifier;
use Zend\Code\Reflection\MethodReflection;

class AutowiredAnnotationListener extends AbstractAnnotationListener
{

    private $code;

    private $refName;

    /*
     * (non-PHPdoc)
     * @see \PhSpring\ZF2\Engine\AbstractAnnotationListener::onBeforeClass()
     */
    public function onBeforeClass(Event $event)
    {
        $this->refName = sprintf('$ref%s', spl_object_hash($this));
        $this->code = sprintf('%s = new \%s($this->%s);', $this->refName, ReflectionClass::class, ClassGenerator::PROPERTY_NAME_INSTANCE);
        $reflection = $this->getReflection($event);
        /* @var $target \PhSpring\ZF2\Engine\ClassGenerator */
        $target = $event->getTarget();
        if (! $target->hasMethod('phsAutowired')) {
            $target->addMethodFromGenerator(MethodGenerator::fromReflection(new MethodReflection(self::class . '::phsAutowired')));
        }
        
        /* @var $property ReflectionProperty */
        foreach ($reflection->getProperties() as $property) {
            $this->handleProperty($property);
        }
        $target->getMethod('__construct')->setBody($target->getMethod('__construct')
            ->getBody() . PHP_EOL . $this->code);
    }

    public function handleProperty(ReflectionProperty $property)
    {
        if ($property->hasAnnotation(Autowired::class)) {
            if (isset($property->getAnnotation(Autowired::class)->value))
                return;
            $type = Helper::getPropertyType($property);
            $isPrimitiveType = (in_array($type, Constants::$php_default_types) || in_array($type, Constants::$php_pseudo_types));
            $serviceName = ($qualifier = $property->getAnnotation(Qualifier::class)) ? $qualifier->value : null;
            
            if (($type === null || $isPrimitiveType) && $serviceName === null) {
                throw new \RuntimeException("Must set the {$property->getDeclaringClass()->getName()}::\${$property->getName()} property type by @var annotation or you must use @Qualifier annotation to define the service");
            }
            if (empty($serviceName)) {
                $serviceName = $type;
            }
            $this->code .= sprintf('$this->phsAutowired("%s", \%s::class, "%s");' . PHP_EOL, $serviceName, $type, $property->getName());
        }
    }

    private function phsAutowired($serviceName, $expectedType, $propertyName)
    {
        $service = $this->serviceLocator->get($serviceName);
        if (! $service instanceof $expectedType) {
            throw new \Exception("The type is missmatch ($propertyName - $serviceName - $expectedType)");
        }
        $property = $this->phsRef->getProperty($propertyName);
        $property->setAccessible(true);
        $property->setValue($this->phsInstance, $service);
    }
}
