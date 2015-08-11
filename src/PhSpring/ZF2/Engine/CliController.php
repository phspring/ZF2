<?php
namespace PhSpring\ZF2\Engine;

use Zend\Mvc\Controller\AbstractConsoleController;
use Zend\ServiceManager\AbstractFactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;
use PhSpring\Annotations\Controller;
use PhSpring\Reflection\ReflectionClass;
use PhSpring\ZF2\Engine\GeneratedControllerInterface;
use stdClass;
use Zend\Mvc\Controller\AbstractActionController;
use Zend\Mvc\Controller\ControllerManager as ZCM;
use Zend\ServiceManager\Exception\ServiceNotCreatedException;
use Zend\ServiceManager\Exception\ServiceNotFoundException;
use PhSpring\ZF2\Engine\ClassGenerator;
use PhSpring\ZF2\Mvc\View\Http\InjectTemplateListener;
use Zend\Mvc\MvcEvent;

class CliController extends AbstractConsoleController
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

    function findAllDirs($start)
    {
        $dirStack = [
            $start
        ];
        while ($dir = array_shift($dirStack)) {
            $ar = glob($dir . '/*', GLOB_ONLYDIR | GLOB_NOSORT);
            if (! $ar)
                continue;
            
            $dirStack = array_merge($dirStack, $ar);
            foreach ($ar as $DIR)
                yield $DIR;
        }
    }
 
    public function scanAction()
    {
        $serviceLocator = $this->getServiceLocator();
        /* @var $cache \Zend\Cache\Storage\Adapter\Filesystem */
        $cache = $this->getServiceLocator()->get('phsCache');
        
        // Open an inotify instance
        $fd = inotify_init();
        // - Using stream_set_blocking() on $fd
        stream_set_blocking($fd, 0);
        
        $componentDirs = [];
        $componentNamespaces = $this->getServiceLocator()->get('Config')['phspring']['component-scan'];
        $pattern = '/(';
        foreach ($componentNamespaces as $name => $ns) {
            $componentNamespaces[$name] = str_replace('\\', '\\' . DIRECTORY_SEPARATOR, $ns);
        }
        $pattern .= implode('|', $componentNamespaces);
        $pattern .= ')/';
        $fname = '*.php';
        $watch_descriptor = [];
        foreach ($this->findAllDirs(getcwd()) as $dir) {
            if (preg_match($pattern, $dir)) {
                // Watch __FILE__ for metadata changes (e.g. mtime)
                $id = inotify_add_watch($fd, $dir, IN_MODIFY | IN_CREATE | IN_DELETE | IN_DELETE_SELF);
                $watch_descriptor[$id] = $dir;
            }
        }
        // - Using stream_select() on $fd:
        do {
            usleep(500);
            $events = inotify_read($fd);
            print_r($events);
            if ($events) {
                foreach ($events as $event) {
                    $dir = $watch_descriptor[$event['wd']];
                    $filepath = realpath($dir . DIRECTORY_SEPARATOR . $event['name']);
                    foreach ($this->file_get_php_classes($filepath) as $requestedName) {
                        $canonicalName = strtolower(strtr($requestedName, $this->canonicalNamesReplacements));
                        $ref = new ReflectionClass($requestedName);
                        
                        $cache->addItem($canonicalName, $requestedName);
                        if (! $requestedName instanceof AbstractActionController) {
                            if ($ref->hasAnnotation(Controller::class)) {
                                $parts = str_split(md5($ref->getFileName()), 2);
                                array_unshift($parts, $cache->getOptions()->getCacheDir());
                                $path = implode(DIRECTORY_SEPARATOR, $parts);
                                if (! file_exists($path)) {
                                    mkdir($path, 0755, true);
                                }
                                $generator = $serviceLocator->get('ControllerGenerator');
                                $class = $generator($requestedName);
                                $classPath = $path . DIRECTORY_SEPARATOR . pathinfo($ref->getFileName(), PATHINFO_BASENAME);
                                file_put_contents($classPath, '<?php' . PHP_EOL . $class->generate());
                                
                                $cache->setItem($canonicalName, [
                                    'name' => $class->getInvokableClassName(),
                                    'class_path' => $classPath,
                                    'template' => preg_replace('/\\\\/', '/', preg_replace('/(\\\\)?Controller/', '', $requestedName))
                                ]);
                            }
                        }
                    }
                }
            }
            $read = array(
                $fd
            );
            $write = null;
            $except = null;
            stream_select($read, $write, $except, 0);
            inotify_read($fd); // Does no block, and return false if no events are pending
        } while (true);
        
        foreach ($watch_descriptor as $id => $wd) {
            inotify_rm_watch($fd, $id);
        }
        
        // Close the inotify instance
        // This may have closed all watches if this was not already done
        fclose($fd);
    }

    function file_get_php_classes($filepath)
    {
        $php_code = file_get_contents($filepath);
        $classes = $this->get_php_classes($php_code);
        return $classes;
    }

    function get_php_classes($php_code)
    {
        $classes = array();
        $tokens = token_get_all($php_code);
        $count = count($tokens);
        $ns = '';
        for ($i = 1; $i < $count; $i ++) {
            if ($tokens[$i][0] == T_NAMESPACE) {
                $i += 2;
                $ns = '';
                while ($tokens[$i] != ';') {
                    $ns .= $tokens[$i ++][1];
                }
            }
            if ($tokens[$i - 2][0] == T_CLASS && $tokens[$i - 1][0] == T_WHITESPACE && $tokens[$i][0] == T_STRING) {
                
                $class_name = $tokens[$i][1];
                $classes[] = $ns . '\\' . $class_name;
            }
        }
        return $classes;
    }
}