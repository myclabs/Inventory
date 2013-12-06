<?php
/**
 * @package Inventory
 */

require_once __DIR__ . '/User/populate.php';

/**
 * @package Inventory
 */
class Inventory_Populate extends Core_Script_Populate
{

    /**
     * Populate a specific environment.
     *
     * @param string $environment
     *
     * @void
     */
    public function populateEnvironment($environment)
    {
        $populateUnit = new User_Populate();
        $populateUnit->populateEnvironment($environment);
    }

}
