<?php
/**
 * @author     matthieu.napoli
 * @package    Core
 * @subpackage Script
 */

/**
 * Script action class
 *
 * @package    Core
 * @subpackage Script
 */
abstract class Core_Script_Action
{
    /**
     * Package which own the action.
     *  This attribute is used by the build script and needed to get the path to the db.sql file.
     *
     * @var Core_Package
     */
    public $dynamicPackage = null;

    /**
     * Environments in which the action is runned.
     *  This attribute is used by the build script.
     *
     * @var array
     */
    public $dynamicEnvironments = array();


    /**
     * Run the action.
     *
     * The script is run over all the environments defined in $this->environments
     */
    public final function run()
    {
        foreach ($this->dynamicEnvironments as $environment) {
            $this->runEnvironment($environment);
        }
    }

    /**
     * Run the action for a specific environments.
     *
     * @param string $environment
     *
     * @return void
     */
    protected abstract function runEnvironment($environment);

}
