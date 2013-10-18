<?php

namespace Core;

use Exception;

/**
 * Autoloader de classes
 *
 * @author matthieu.napoli
 */
class Autoloader
{

    /**
     * Stack of configurations for the autoloading
     * @var array()
     */
    protected $autoloadConfigurations = array();


    /**
     * Returns the instance of the Autoloader
     * @return Autoloader
     */
    public static function getInstance()
    {
        static $instance = null;
        if (!isset($instance)) {
            $instance = new self();
        }
        return $instance;
    }

    /**
     * Registers instance as autoloader
     */
    public function register()
    {
        spl_autoload_register(array(__CLASS__, 'autoload'));
    }

    /**
     * Autoload the class
     * @param string $class
     * @throws \Exception
     * @return bool
     */
    public function autoload($class)
    {
        foreach ($this->autoloadConfigurations as $configuration) {
            switch ($configuration['type']) {
                case 'module':
                    $return = $this->autoloadModule(
                        $class,
                        $configuration['namespace'],
                        $configuration['basePath']
                    );
                    break;
                case 'namespace':
                    $return = $this->autoloadNamespace(
                        $class,
                        $configuration['namespace'],
                        $configuration['basePath']
                    );
                    break;
                default:
                    throw new Exception("Autoloader error");
            }
            // If we found the class
            if ($return !== false) {
                return include $return;
            }
        }
        // If we didn't find the class, try to find it in the include path
        $return = $this->autoloadIncludePath($class);
        // If we found the class
        if ($return !== false) {
            return include $return;
        } else {
            return false;
        }
    }

    /**
     * Add a module
     * @param string $namespace
     * @param string $basePath
     */
    public function addModule($namespace, $basePath)
    {
        $this->autoloadConfigurations[] = array(
            'type'      => 'module',
            'namespace' => $namespace,
            'basePath'  => $basePath,
        );
    }

    /**
     * Add a namespace
     * @param string $namespace
     * @param string $basePath
     */
    public function addNamespace($namespace, $basePath)
    {
        $this->autoloadConfigurations[] = array(
            'type'      => 'namespace',
            'namespace' => $namespace,
            'basePath'  => $basePath,
        );
    }

    /**
     * Try to find the class in a module
     * @param  string $class
     * @param  string $namespace Module namespace
     * @param  string $basePath  Module base path
     * @return bool|string The path to the file
     */
    protected function autoloadModule($class, $namespace, $basePath)
    {
        // Check the namespace
        $segments = explode('_', $class);
        $classNamespace = array_shift($segments);
        if ($namespace != $classNamespace) {
            // the namespace doesn't match
            return false;
        }
        // Get the sub-namespace
        $subNamespace = array_shift($segments);
        switch ($subNamespace) {
            case 'Bootstrap':
                $classPath = $basePath . '/Bootstrap.php';
                // Does the file exists, and is it readable
                if ($this->isReadable($classPath)) {
                    return $classPath;
                } else {
                    return false;
                }
                break;
            case 'Model':
                $path = 'models';
                break;
            case 'Service':
                $path = 'services';
                break;
            case 'Plugin':
                $path = 'plugins';
                break;
            case 'Controller':
                $subNamespace2 = array_shift($segments);
                if ($subNamespace2 == 'Helper') {
                    $path = 'controllers/helpers';
                } else {
                    return false;
                }
                break;
            case 'View':
                $subNamespace2 = array_shift($segments);
                if ($subNamespace2 == 'Helper') {
                    $path = 'views/helpers';
                } else {
                    return false;
                }
                break;
            case 'Form':
                $path = 'forms';
                break;
            default:
                return false;
        }
        $classPath = $basePath . '/' . $path . '/' . implode('/', $segments) . '.php';
        // Does the file exists, and is it readable
        if ($this->isReadable($classPath)) {
            return $classPath;
        }
        return false;
    }

    /**
     * Try to find the class in a namespace
     * @param  string $class
     * @param  string $namespace Namespace
     * @param  string $basePath  Namespace base path
     * @return bool|string The path to the file
     */
    protected function autoloadNamespace($class, $namespace, $basePath)
    {
        // Check the namespace
        $segments = explode('_', $class);
        $classNamespace = array_shift($segments);
        if ($namespace != $classNamespace) {
            // the namespace doesn't match
            return false;
        }
        $classPath = $basePath . '/' . implode('/', $segments) . '.php';
        // Does the file exists, and is it readable
        if ($this->isReadable($classPath)) {
            return $classPath;
        }
        return false;
    }

    /**
     * Try to find the class in the include path
     * @param  string $class
     * @return bool|string The path to the file
     */
    protected function autoloadIncludePath($class)
    {
        $classPath = str_replace(array('_', '\\'), '/', $class) . '.php';
        // Does the file exists, and is it readable
        if ($this->isReadable($classPath)) {
            return $classPath;
        }
        return false;
    }

    /**
     * Returns TRUE if the $filename is readable, or FALSE otherwise.
     * This function uses the PHP include_path, where PHP's is_readable()
     * does not.
     *
     * This function comes from ZF 1.11
     *
     * @param string $filename
     * @return boolean
     */
    public static function isReadable($filename)
    {
        if (is_readable($filename)) {
            // Return early if the filename is readable without needing the
            // include_path
            return true;
        }

        if (strtoupper(substr(PHP_OS, 0, 3)) == 'WIN'
            && preg_match('/^[a-z]:/i', $filename)
        ) {
            // If on windows, and path provided is clearly an absolute path,
            // return false immediately
            return false;
        }

        foreach (self::explodeIncludePath() as $path) {
            if ($path == '.') {
                if (is_readable($filename)) {
                    return true;
                }
                continue;
            }
            $file = $path . '/' . $filename;
            if (is_readable($file)) {
                return true;
            }
        }
        return false;
    }

    /**
     * Explode an include path into an array
     *
     * If no path provided, uses current include_path. Works around issues that
     * occur when the path includes stream schemas.
     *
     * This function comes from ZF 1.11
     *
     * @param  string|null $path
     * @return array
     */
    public static function explodeIncludePath($path = null)
    {
        if (null === $path) {
            $path = get_include_path();
        }

        if (PATH_SEPARATOR == ':') {
            // On *nix systems, include_paths which include paths with a stream
            // schema cannot be safely explode'd, so we have to be a bit more
            // intelligent in the approach.
            $paths = preg_split('#:(?!//)#', $path);
        } else {
            $paths = explode(PATH_SEPARATOR, $path);
        }
        return $paths;
    }

    /**
     * Private constructor
     */
    final private function __construct()
    {
    }

    /**
     * Private clone method
     */
    final private function __clone()
    {
    }

}
