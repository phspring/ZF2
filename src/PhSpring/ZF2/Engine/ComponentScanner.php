<?php
namespace PhSpring\ZF2\Engine;

use Zend\ServiceManager\AbstractFactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;
use PhSpring\ZF2\Mvc\Controller\Scanner;
use FilesystemIterator;

class ComponentScanner implements AbstractFactoryInterface
{

    protected $configKey = 'component-scanner';

    protected $config;

    public function canCreateServiceWithName(ServiceLocatorInterface $serviceLocator, $name, $requestedName)
    {
        return in_array($requestedName, [
            'ControllerScanner'
        ]);
    }

    public function createServiceWithName(ServiceLocatorInterface $serviceLocator, $name, $requestedName)
    {
        $iterator = new \AppendIterator();
        $config = $this->getConfig($serviceLocator);
        $ret = [];
        if (array_key_exists('dir', $config)) {
            foreach ((array)$config['dir'] as $dir) {
                $iterator->append(new \RecursiveDirectoryIterator($dir, FilesystemIterator::KEY_AS_PATHNAME | FilesystemIterator::CURRENT_AS_FILEINFO | FilesystemIterator::SKIP_DOTS));
            }
        }
        if ($requestedName == 'ControllerScanner') {
            if (array_key_exists('controller', $config)) {
                foreach ((array) $config['controller'] as $dir)
                    $iterator->append(new \RecursiveDirectoryIterator($dir, FilesystemIterator::KEY_AS_PATHNAME | FilesystemIterator::CURRENT_AS_FILEINFO | FilesystemIterator::SKIP_DOTS));
            }
            return new Scanner($iterator);
        }
        return $iterator;
    }

    protected function getConfig(ServiceLocatorInterface $services)
    {
        if ($this->config !== null) {
            return $this->config;
        }
        
        if (! $services->has('Config')) {
            $this->config = array();
            return $this->config;
        }
        
        $config = $services->get('Config');
        if (! isset($config[$this->configKey]) || ! is_array($config[$this->configKey])) {
            $this->config = array();
            return $this->config;
        }
        
        $this->config = $config[$this->configKey];
        return $this->config;
    }
}
