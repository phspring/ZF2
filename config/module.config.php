<?php
return [
	'service_manager' => [
		'factories' => [
			'AnnotationEventManager' => 'PhSpring\ZF2\Engine\AnnotationEventManagerFactory',
			'phsCache' => function () {
				return \Zend\Cache\StorageFactory::factory(
					array(
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
	'router' => [
	    'router_class'=>'PhSpring\ZF2\Mvc\Router\Http\AnnotationRouteStack'
	],
	'annotation_events' => [
		'PhSpring\ZF2\Annotation\ControllerAnnotationListener',
		'PhSpring\ZF2\Annotation\AutowiredAnnotationListener',
		'PhSpring\ZF2\Annotation\ConfigAnnotationListener',
	    'PhSpring\ZF2\Annotation\RequestParamAnnotationListener',
	     
	]
]
;