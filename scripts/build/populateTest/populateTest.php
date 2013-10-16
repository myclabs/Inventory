<?php
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
        $this->init($environment);


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


        $this->close($environment);
    }

    /**
     * @param string $environment
     */
    protected function init($environment)
    {
        /** @var DI\Container $container */
        $container = Zend_Registry::get('container');

        // Ajout du listener des ACL.
        if (! Zend_Registry::isRegistered('EntityManagers')) {
            return;
        }
        $entityManagers = Zend_Registry::get('EntityManagers');
        /** @var $entityManager Doctrine\ORM\EntityManager */
        $entityManager = $entityManagers['default'];
        $events = [
            Doctrine\ORM\Events::onFlush,
            Doctrine\ORM\Events::postFlush,
        ];
        $entityManager->getEventManager()->addEventListener($events, $container->get(Orga_Service_ACLManager::class));

        // Ajout de treeTraverser.
        /** @var $aclService User_Service_ACL */
        $aclService = $container->get(User_Service_ACL::class);
        $aclService->setResourceTreeTraverser(Orga_Model_Cell::class, $container->get(Orga_Service_ACLManager::class));

        // Désactivation du filtre des ACL.
        /** @var $aclFilterService User_Service_ACLFilter */
        $aclFilterService = $container->get(User_Service_ACLFilter::class);
        $aclFilterService->enabled = false;

        // Filtre des ACL
        $aclFilterService->enabled = true;
        $aclFilterService->generate();

        // Initalisation Unit.
        $populateUnit = new Unit_Populate();
        $populateUnit->initUnitEntityManager($environment);
    }

    /**
     * @param string $environment
     */
    protected function close($environment)
    {
        /** @var DI\Container $container */
        $container = Zend_Registry::get('container');

        // Résactivation du filtre des ACL.
        /** @var $aclFilterService User_Service_ACLFilter */
        $aclFilterService = $container->get(User_Service_ACLFilter::class);
        $aclFilterService->enabled = true;
        echo "\tRégénération des ACL…";
        $aclFilterService->generate();
        echo "… done!".PHP_EOL;

        // Fermeture Unit.
        $populateUnit = new Unit_Populate();
        $populateUnit->resetUnitEntityManager();
    }

}