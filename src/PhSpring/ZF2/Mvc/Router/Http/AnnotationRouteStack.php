<?php
namespace PhSpring\ZF2\Mvc\Router\Http;

use ReflectionMethod;
use Zend\Mvc\Router\Http\Literal;
use PhSpring\Annotations\RequestMapping;
use Zend\Mvc\Router\Http\Part;
use Zend\Mvc\Router\Http\TreeRouteStack;
use Zend\ServiceManager\ServiceLocatorAwareInterface;
use Zend\Stdlib\RequestInterface as Request;
use PhSpring\Reflection\ReflectionClass;

/**
 * Description of AnnotationRouteStack
 *
 * @author tothd
 */
class AnnotationRouteStack extends TreeRouteStack implements ServiceLocatorAwareInterface
{

    private $serviceLocator;

    public function getServiceLocator()
    {
        return $this->serviceLocator;
    }

    public function setServiceLocator(\Zend\ServiceManager\ServiceLocatorInterface $serviceLocator)
    {
        $this->serviceLocator = $serviceLocator;
    }

    public function match(Request $request, $pathOffset = null, array $options = array())
    {
        $return = parent::match($request, $pathOffset, $options);
        
        if ($return == NULL) {
            
            $counter = 0;
            
            $this->routePluginManager = $this->getServiceLocator()->get('RoutePluginManager');
            foreach ($this->getServiceLocator()->get("Config")['controllers']['invokables'] as $class) {
                $ref = new ReflectionClass($class);
                $routes = [];
                if (preg_match('/\@RequestMapping/', file_get_contents($ref->getFileName()))) {
                    $methods = $ref->getMethods(ReflectionMethod::IS_PUBLIC);
                    foreach ($methods as $method) {
                        if ($method->hasAnnotation(RequestMapping::class)) {
                            $annot = $method->getAnnotation(RequestMapping::class);
                            $routes['phs' . $counter ++] = Literal::factory([
                                'route' => $annot->value,
                                'defaults' => [
                                    'controller' => $class,
                                    'action' => $method->getName()
                                ]
                            ]);
                        }
                    }
                    
                    if ($ref->hasAnnotation(RequestMapping::class)) {
                        $annot = $ref->getAnnotation(RequestMapping::class);
                        $routes = [
                            'route' => [
                                'type' => 'literal',
                                'options' => [
                                    'route' => $annot->value,
                                    'defaults' => [
                                        'controller' => $class,
                                        'action' => 'index'
                                    ]
                                ]
                            ],
                            'may_terminate' => true,
                            'route_plugins' => $this->routePluginManager,
                            'child_routes' => $routes
                        ];
                        $routes = [
                            Part::factory($routes)
                        ];
                    }
                    foreach ($routes as $key => $route) {
                        $this->addRoute('phs-route-' . $key, $route);
                    }
                }
            }
            return parent::match($request, $pathOffset, $options);
        }
        return $return;
    }
}
