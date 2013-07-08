<?php
/**
 * @package Inventory
 */

require_once __DIR__ . '../populate/Unit/populate.php';
require_once __DIR__ . '../populate/User/populate.php';

/**
 * @package Inventory
 */
class Inventory_PopulateTest extends Core_Script_Populate
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
        // Default.
        $populateUnit = new User_Populate();
        $populateUnit->populateEnvironment($environment);
        $populateUnit = new Unit_Populate();
        $populateUnit->populateEnvironment($environment);

        // Classif.
        $populateClassif = new Classif_PopulateTest();
        $populateClassif->runEnvironment($environment);

        // Keyword.
        $populateKeyword = new Keyword_PopulateTest();
        $populateKeyword->runEnvironment($environment);

        // Techno.
        $populateTechno = new Techno_PopulateTest();
        $populateTechno->runEnvironment($environment);

        // Orga.
        $populateOrga = new Orga_PopulateTest();
        $populateOrga->runEnvironment($environment);
    }

}