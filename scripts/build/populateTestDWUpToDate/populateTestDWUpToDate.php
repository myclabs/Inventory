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
        if ($environment === 'test') {
            return;
        }
        $this->init($environment);


        // Classif.
        $populateClassif = new Classif_PopulateTestDWUpToDate();
        $populateClassif->runEnvironment($environment);

        // Orga.
        $populateOrga = new Orga_PopulateTestDWUpToDate();
        $populateOrga->runEnvironment($environment);

        $this->close($environment);
    }

}