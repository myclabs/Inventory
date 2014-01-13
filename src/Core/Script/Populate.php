<?php
use Doctrine\ORM\EntityManager;

/**
 * @author     matthieu.napoli
 * @package    Core
 * @subpackage Script
 */

/**
 * Class for Populate action.
 *
 * @package    Core
 * @subpackage Script
 */
abstract class Core_Script_Populate extends Core_Script_Action
{
    /**
     * Run the action for each environment.
     *
     * @param string $environment
     *
     * @return void
     */
    protected function runEnvironment($environment)
    {
        $this->populateEnvironment($environment);
    }

    /**
     * Populate a specific environment.
     *
     * @param string $environment
     *
     * @void
     */
    protected function populateEnvironment($environment)
    {
        echo "\tNothing done for $environment.".PHP_EOL;
    }
}
