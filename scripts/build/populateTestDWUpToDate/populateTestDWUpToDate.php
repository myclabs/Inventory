<?php
/**
 * @package Inventory
 */

require_once __DIR__ . '/Classif/populateTestDWUpToDate.php';
require_once __DIR__ . '/Orga/populateTestDWUpToDate.php';

/**
 * @package Inventory
 */
class Inventory_PopulateTestDWUpToDate extends Inventory_PopulateTest
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
        // Initalisation Unit.
        $populateUnit = new Unit_Populate();
        $populateUnit->initUnitEntityManager($environment);

        // Classif.
        $populateClassif = new Classif_PopulateTestDWUpToDate();
        $populateClassif->runEnvironment($environment);

        // Orga.
        $populateOrga = new Orga_PopulateTestDWUpToDate();
        $populateOrga->runEnvironment($environment);

        // Fermeture Unit.
        $populateUnit = new Unit_Populate();
        $populateUnit->resetUnitEntityManager();
    }

}