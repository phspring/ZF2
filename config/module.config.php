<?php
return [
    'service_manager' => [
        'factories' => [
            'AnnotationEventManager'=> 'PhSpring\ZF2\Engine\AnnotationEventManagerFactory'
        ]
    ],
    'annotation_events' => [
        'PhSpring\ZF2\Annotation\ControllerAnnotationListener'
    ]
];