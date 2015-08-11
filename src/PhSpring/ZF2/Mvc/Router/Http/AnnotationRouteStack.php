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
use Zend\Mvc\Router\Exception\RuntimeException;
use Zend\Cache\Storage\StorageInterface;
use Zend\Cache\Storage\Zend\Cache\Storage;
use PhSpring\ZF2\Mvc\Controller\Scanner;

/**
 * Description of AnnotationRouteStack
 *
 * @author lobiferi(info@phspring.nl)
 * @author tothd
 *         @
 */
class AnnotationRouteStack extends TreeRouteStack implements ServiceLocatorAwareInterface
{

    /**
     *
     * @var StorageInterface
     */
    private $cache;

    private $cacheKey = 'route-cache-phs';

    private $cacheServiceName;

    private $serviceLocator;

    private $parsed;

    private $fromCache;

    public static function factory($options = array())
    {
        $instance = parent::factory($options);
        $instance->setCacheService('phsCache');
        return $instance;
    }

    function getCache()
    {
        try {
            return $this->getServiceLocator()->get($this->cacheServiceName);
        } catch (\Exception $e) {
            return new StorageInterface();
        }
    }

    protected function setCacheService($cache)
    {
        $this->cacheServiceName = $cache;
    }

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
            $this->parseDynamicRoutes();
            $return = parent::match($request, $pathOffset, $options);
            if ($return == NULL) {
                $this->parseDynamicRoutes();
                return parent::match($request, $pathOffset, $options);
            }
        }
        return $return;
    }

    public function assemble(array $params = array(), array $options = array())
    {
        try {
            return parent::assemble($params, $options);
        } catch (RuntimeException $e) {
            try {
                $this->parseDynamicRoutes();
                return parent::assemble($params, $options);
            } catch (RuntimeException $e) {
                $this->parseDynamicRoutes();
                return parent::assemble($params, $options);
            }
        }
    }

    private function parseDynamicRoutes()
    {
        if ($this->parsed && $this->fromCache)
            return;
        if (null === $this->fromCache) {
            $this->fromCache = (array) $this->getCache()->getItem($this->cacheKey);
            foreach ($this->fromCache as $key => $route) {
                $this->addRoute($key, $route);
            }
            return;
        }
        $this->parsed;
        $counter = 0;
        $this->routePluginManager = $this->getServiceLocator()->get('RoutePluginManager');
        foreach ($this->getAvailableClasses() as $class) {
            $ref = new ReflectionClass($class);
            $classAnnot = $classRouteName = $classRoute = null;
            if ($ref->hasAnnotation(RequestMapping::class)) {
                $classAnnot = $ref->getAnnotation(RequestMapping::class);
                $classRouteName = $classAnnot->name;
                $classRoute = $classAnnot->value;
            }
            $classRoute = $classRoute ? $classRoute : '/'; 
            $routes = [];
            if (preg_match('/\@RequestMapping/', file_get_contents($ref->getFileName()))) {
                $methods = $ref->getMethods(ReflectionMethod::IS_PUBLIC);
                foreach ($methods as $method) {
                    $isIndex = $classAnnot && in_array($method->getName(), array(
                        'index',
                        'indexAction'
                    ));
                    if ($isIndex || $method->hasAnnotation(RequestMapping::class)) {
                        /* @var $annot RequestMapping */
                        $annot = $method->getAnnotation(RequestMapping::class);
                        $name = $annot ? ($annot->name?$annot->name:($isIndex?'index':'phs' . $counter ++)) : ($isIndex && $classRouteName ? $classRouteName : 'phs' . $counter ++);
                        $route = ($classRouteName ? '' : $classAnnot->value);
                        if ($annot) {
                            $route .= $annot->value;
                        } elseif (! $isIndex) {
                            $route .= '/' . $method->getName();
                        }
                        if($classAnnot->value == '/'){
                            $route = ltrim($route, '/');
                        }
                        
                        if ($name) {
                            $routes[$name] = [
                                'type' => 'literal',
                                'options' => [
                                    'route' => $route,
                                    'defaults' => [
                                        'controller' => $class,
                                        'action' => 'phs-action-method-' . $method->getName()
                                    ]
                                ]
                            ];
                        }
                    }
                }
                if ($classAnnot) {

                    if ($classRouteName && $classRoute) {
                        $routes = [
                            'type' => 'literal',
                            'options' => [
                                'route' => $classAnnot->value,
                                'defaults' => [
                                    'controller' => $class,
                                    'action' => 'index'
                                ]
                            ],
                            'may_terminate' => true,
                            // 'route_plugins' => $this->routePluginManager,
                            'child_routes' => $routes
                        ];
                        $routes = [
                            $classRouteName => $routes
                        ];
                    }
                }
                foreach ($routes as $key => $route) {
                    $this->fromCache[$key] = $route;
                }
            }
        }
//         echo '<pre>';
//         print_r($this->fromCache);
//         die();
        foreach ($this->fromCache as $key => $route) {
            $this->addRoute($key, $route);
        }
        $this->getCache()->setItem($this->cacheKey, $this->fromCache);
    }

    private function getAvailableClasses()
    {
        return $this->serviceLocator->get('ComponentScanner')->getControllers();
    }
}
