<?php
namespace PhSpring\ZF2\Mvc\Controller;

use PhSpring\Annotations\Controller;
use PhSpring\Reflection\ReflectionClass;
use PhSpring\ZF2\Engine\GeneratedControllerInterface;
use stdClass;
use Zend\Mvc\Controller\AbstractActionController;
use Zend\Mvc\Controller\ControllerManager as ZCM;
use Zend\ServiceManager\Exception\ServiceNotCreatedException;
use Zend\ServiceManager\Exception\ServiceNotFoundException;
use PhSpring\ZF2\Engine\ClassGenerator;

class ControllerManager extends ZCM
{

	/**
	 * Attempt to create an instance via an invokable class
	 *
	 * Overrides parent implementation by passing $creationOptions to the
	 * constructor, if non-null.
	 *
	 * @param string $canonicalName
	 * @param string $requestedName
	 * @return null|stdClass
	 * @throws ServiceNotCreatedException If resolved class does not exist
	 */
	protected function createFromInvokable($canonicalName, $requestedName)
	{
        $invokable = $this->invokableClasses[$canonicalName];

		if (! class_exists($invokable)) {
			throw new ServiceNotFoundException(
				sprintf('%s: failed retrieving "%s%s" via invokable class "%s"; class does not exist',
					get_class($this) . '::' . __FUNCTION__, $canonicalName,
					($requestedName ? '(alias: ' . $requestedName . ')' : ''), $invokable));
		}

		$ref = new ReflectionClass($invokable);
		/* @var $cache \Zend\Cache\Storage\Adapter\Filesystem */
		$cache = $this->getServiceLocator()->get('phsCache');

		if (true || ! $cache->hasItem($canonicalName)) {
			$cache->addItem($canonicalName, $invokable);
			if (! (in_array(GeneratedControllerInterface::class, $ref->getInterfaceNames()) ||
				 $invokable instanceof AbstractActionController)) {
				if ($ref->hasAnnotation(Controller::class)) {
					$generator = $this->getServiceLocator()->get('ControllerGenerator');
					$class = $generator($invokable);
					$cache->setItem($canonicalName,
						[
							'name' => $class->getInvokableClassName(),
							'content' => $class->generate()
						]);
				}
			}
		}

		$data = $cache->getItem($canonicalName);
		if (is_array($data)) {
			$invokable = $data['name'];
			$content = $data['content'];
			eval($content);
		}

		if (null === $this->creationOptions || (is_array($this->creationOptions) && empty($this->creationOptions))) {
			$instance = new $invokable();
		} else {
			$instance = new $invokable($this->creationOptions);
		}

		return $instance;
	}
}
