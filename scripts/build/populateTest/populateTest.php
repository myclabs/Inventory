<?php

use Doctrine\ORM\EntityManager;

require_once __DIR__ . '/Classif/populateTest.php';
require_once __DIR__ . '/Techno/populateTest.php';
require_once __DIR__ . '/Orga/populateTest.php';
require_once __DIR__ . '/AF/populateTest.php';

class Inventory_PopulateTest extends Core_Script_Populate
{
    public function populateEnvironment($environment)
    {
        $container = \Core\ContainerSingleton::getContainer();
        $entityManager = $container->get(EntityManager::class);
        $entityManager->clear();

        // Classif.
        $populateClassif = new Classif_PopulateTest();
        $populateClassif->runEnvironment($environment);

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
    }
}
