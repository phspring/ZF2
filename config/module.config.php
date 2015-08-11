<?php
return [
    'service_manager' => [
        'abstract_factories' => [
            'Zend\Cache\Service\StorageCacheAbstractServiceFactory',
            'Zend\Log\LoggerAbstractServiceFactory',
            'PhSpring\ZF2\Engine\Scanner\ComponentScanner'
        ],
        'factories' => [
            'AnnotationEventManager' => 'PhSpring\ZF2\Engine\AnnotationEventManagerFactory',
            'phsCache' => function () {
                return \Zend\Cache\StorageFactory::factory(array(
                    'adapter' => array(
                        'name' => 'filesystem',
                        'options' => array(
                            'dirLevel' => 2,
                            'cacheDir' => 'data/cache/phs',
                            'dirPermission' => 0755,
                            'filePermission' => 0666,
                            'namespaceSeparator' => '-db-'
                        )
                    ),
                    'plugins' => array(
                        'serializer'
                    )
                ));
            }
        ]
    ],
    'controllers' => [
        'invokables' => [
            'PhSpring\CliController' => 'PhSpring\ZF2\Engine\CliController'
        ],
        'abstract_factories' => [
            'PhSpring\ZF2\Engine\Scanner\ControllerScanner'
        ]
    ]
    ,
    'router' => [
        'router_class' => 'PhSpring\ZF2\Mvc\Router\Http\AnnotationRouteStack'
    ],
    
    'phspring' => array(
        'generated_dir' => 'generated',
        'component-scan' => [
            'base-package' => 'Application\Controller'
        ]
    ),
    'console' => array(
        'router' => array(
            'routes' => array(
                'cli' => array(
                    'options' => array(
                        'route' => 'scan',
                        'defaults' => array(
                            'controller' => 'PhSpring\CliController',
                            'action' => 'scan'
                        )
                    )
                )
            )
        )
    ),
    'annotation_events' => [
        'PhSpring\ZF2\Annotation\ControllerAnnotationListener',
        'PhSpring\ZF2\Annotation\AutowiredAnnotationListener',
        'PhSpring\ZF2\Annotation\ConfigAnnotationListener',
        'PhSpring\ZF2\Annotation\RequestParamAnnotationListener',
        'PhSpring\ZF2\Annotation\ModelAttributeListener'
    ]
]
;