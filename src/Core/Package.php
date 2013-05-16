<?php
/**
 * @author     alexandre.delorme
 * @author     matthieu.napoli
 * @package    Core
 * @subpackage Package
 */

/**
 * Classe stockant les informations relatives à un package
 *
 * @package    Core
 * @subpackage Package
 */
class Core_Package
{

    protected $name;
    protected $version;
    protected $path;
    protected $url;
    protected $isModule;

    /**
     * Constructor
     * @param string $name
     * @param string $path
     * @param string $version
     * @param string $url
     * @param bool   $isModule
     */
    public function __construct($name, $path, $version = null, $url = null, $isModule = false)
    {
        $this->name = $name;
        $this->version = $version;
        $this->path = $path;
        $this->url = $url;
        $this->isModule = $isModule;
    }

    /**
     * @return string Name of the package
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @return string Version
     */
    public function getVersion()
    {
        return $this->version;
    }

    /**
     * @return string Path to the package
     */
    public function getPath()
    {
        return $this->path;
    }

    /**
     * @return string Url to the package
     */
    public function getUrl()
    {
        return $this->url;
    }

    /**
     * @return bool   Bool which defines if the package is a module or not.
     */
    public function isModule()
    {
        return $this->isModule;
    }

}
