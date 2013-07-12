<?php
/**
 * @package Inventory
 */

require_once __DIR__ . '/../populate/Unit/populate.php';
require_once __DIR__ . '/Classif/populateTest.php';
require_once __DIR__ . '/Orga/populateTest.php';

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
        $entityManager->getEventManager()->addEventListener($events, Orga_Service_ACLManager::getInstance());

        // Ajout de treeTraverser.
        /** @var $usersResourceTreeTraverser User_Service_ACL_UsersResourceTreeTraverser */
        $cellResourceTreeTraverser = Orga_Service_ACLManager::getInstance();
        /** @var $aclService User_Service_ACL */
        $aclService = User_Service_ACL::getInstance();
        $aclService->setResourceTreeTraverser("Orga_Model_Cell", $cellResourceTreeTraverser);

        // Désactivation du filtre des ACL.
        /** @var $aclFilterService User_Service_ACLFilter */
        $aclFilterService = User_Service_ACLFilter::getInstance();
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
        // Résactivation du filtre des ACL.
        /** @var $aclFilterService User_Service_ACLFilter */
        $aclFilterService = User_Service_ACLFilter::getInstance();
        $aclFilterService->enabled = true;
        $aclFilterService->generate();

        // Fermeture Unit.
        $populateUnit = new Unit_Populate();
        $populateUnit->resetUnitEntityManager();
    }

}