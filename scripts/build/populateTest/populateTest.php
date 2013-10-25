<?php
use User\Domain\ACL\ACLService;
use User\Domain\ACL\ACLFilterService;

/**
 * @package Inventory
 */

require_once __DIR__ . '/Classif/populateTest.php';
require_once __DIR__ . '/Keyword/populateTest.php';
require_once __DIR__ . '/Techno/populateTest.php';
require_once __DIR__ . '/Orga/populateTest.php';
require_once __DIR__ . '/AF/populateTest.php';

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
        if ($environment === 'test') {
            return;
        }
        // Initalisation Unit.
        $populateUnit = new Unit_Populate();
        $populateUnit->initUnitEntityManager($environment);


        // Classif.
        $populateClassif = new Classif_PopulateTest();
        $populateClassif->runEnvironment($environment);

        // Keyword.
        $populateKeyword = new Keyword_PopulateTest();
        $populateKeyword->runEnvironment($environment);

        // Techno.
        $populateTechno = new Techno_PopulateTest();
        $populateTechno->runEnvironment($environment);

        // AF.
        $populateAF = new AF_PopulateTest();
        $populateAF->runEnvironment($environment);

        // Orga.
        $populateOrga = new Orga_PopulateTest();
        $populateOrga->runEnvironment($environment);

        echo "… done!".PHP_EOL;

        // Fermeture Unit.
        $populateUnit = new Unit_Populate();
        $populateUnit->resetUnitEntityManager();
    }
}