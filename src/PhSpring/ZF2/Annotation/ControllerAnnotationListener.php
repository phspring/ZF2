<?php
namespace PhSpring\ZF2\Annotation;

use PhSpring\ZF2\Engine\AbstractAnnotationListener;

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
    public function onBeforeClass()
    {
        echo 'elkaptam:' . __METHOD__ . PHP_EOL;
        var_dump(func_get_args());
    }

    /*
     * (non-PHPdoc)
     * @see \PhSpring\ZF2\Engine\AbstractAnnotationListener::onBeforeMethod()
     */
    public function onBeforeMethod()
    {
        echo 'elkaptam:' . __METHOD__ . PHP_EOL;
    }
}
