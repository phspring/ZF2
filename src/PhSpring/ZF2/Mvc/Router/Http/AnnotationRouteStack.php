<?php
namespace PhSpring\ZF2\Mvc\Router\Http;

use PhSpring\ZF2\Engine\Adapter\Request;
use PhSpring\ReflectionClass;
use ReflectionMethod;
use Zend\Mvc\Router\Http\Literal;
use PhSpring\Annotations\RequestMapping;
use Zend\Mvc\Router\Http\Part;

/**
 * Description of AnnotationRouteStack
 *
 * @author tothd
 */
class AnnotationRouteStack extends Zend\Mvc\Router\Http\TreeRouteStack implements Zend\ServiceManager\ServiceLocatorAwareInterface {
    
    private $serviceLocator;
    
    public function setServiceLocator(ServiceLocatorInterface $serviceLocator) {
         $this->serviceLocator = $serviceLocator;
    }
    
    public function getServiceLocator() {
        return $this->serviceLocator;
    }
    
    public function match(Request $request, $pathOffset = null, array $options = array()) {
        $return = parent::match();
        
        if($return == NULL) {

            $counter = 0;
            
            $this->routePluginManager = $this->getServiceLocator()->get('RoutePluginManager');
            $this = $this->getServiceLocator()->get('Router');
            foreach ($this->getServiceLocator()->get("Config")['controllers']['invokables'] as $class) {
                $ref = new ReflectionClass($class);
                    $routes = [];
                if (preg_match('/\@RequestMapping/', file_get_contents($ref->getFileName()))) {
                    $methods = $ref->getMethods(ReflectionMethod::IS_PUBLIC);
                    foreach ($methods as $method) {
                        if ($method->hasAnnotation(RequestMapping::class)) {
                            $annot = $method->getAnnotation(RequestMapping::class);
                            $routes['phs' . $counter ++] = 
                            Literal::factory([
                            'route' => $annot->value,
                            'defaults' => [
                            'controller' => $class,
                            'action' => $method->getName(),
                            ],
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
                        $routes = [Part::factory($routes)];
                    }
                    foreach ($routes as $key => $route){
                        $this->addRoute('phs-route-'.$key, $route);
                    }
                    
                    return parent::match();
                }
            }
        }
    }
}
