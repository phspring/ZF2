<?php
namespace PhSpring\ZF2\Engine\Scanner;

use Zend\ServiceManager\AbstractFactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;
use FilesystemIterator;

class ComponentScanner implements AbstractFactoryInterface
{

    /**
     *
     * @var array map of characters to be replaced through strtr
     */
    protected $canonicalNamesReplacements = array(
        '-' => '',
        '_' => '',
        ' ' => '',
        '\\' => '',
        '/' => ''
    );

    protected $configKey = 'component-scan';

    protected $config;

    public function canCreateServiceWithName(ServiceLocatorInterface $serviceLocator, $name, $requestedName)
    {
        return in_array($requestedName, [
            'ControllerScanner',
            'ComponentScanner'
        ]);
    }

    public function createServiceWithName(ServiceLocatorInterface $serviceLocator, $name, $requestedName)
    {
        $iterator = new \AppendIterator();
        $componentDirs = [];
        $componentNamespaces = $this->getConfig($serviceLocator);
        if (! empty($componentNamespaces)) {
            $pattern = '/(';
            foreach ($componentNamespaces as $name => $ns) {
                $componentNamespaces[$name] = str_replace('\\', '\\' . DIRECTORY_SEPARATOR, $ns);
            }
            $pattern .= implode('|', $componentNamespaces);
            $pattern .= ')/';
            $fname = '*.php';
            foreach ($this->findAllDirs(getcwd(), $pattern) as $dir) {
                $iterator->append(new \RecursiveDirectoryIterator($dir, FilesystemIterator::KEY_AS_PATHNAME | FilesystemIterator::CURRENT_AS_FILEINFO | FilesystemIterator::SKIP_DOTS));
            }
        }
        return new Scanner($iterator, $serviceLocator);
    }

    protected function findAllDirs($start, $pattern)
    {
        $dirStack = [
            $start
        ];
        while ($dir = array_shift($dirStack)) {
            $ar = glob($dir . '/*', GLOB_ONLYDIR | GLOB_NOSORT);
            if (! $ar)
                continue;
            
            $dirStack = array_merge($dirStack, $ar);
            foreach ($ar as $DIR) {
                if (preg_match($pattern, $DIR)) {
                    yield $DIR;
                }
            }
        }
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
        if (! isset($config['phspring'][$this->configKey]) || ! is_array($config['phspring'][$this->configKey])) {
            $this->config = array();
            return $this->config;
        }
        
        $this->config = $config['phspring'][$this->configKey];
        return $this->config;
    }
}
