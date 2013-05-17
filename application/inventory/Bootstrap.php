<?php
/**
 * @author valentin.claras
 * @package Inventory
 */

/**
 * @author valentin.claras
 * @package Inventory
 */
class Inventory_Bootstrap extends Core_Package_Bootstrap
{

    /**
     * Enregistre les Workers de Intégration.
     */
    protected function _initInventoryWorker()
    {
        /**@var Core_Work_Dispatcher $dispatcher */
        $dispatcher = Zend_Registry::get('workDispatcher');
        $dispatcher->registerWorker(new Inventory_Work_Worker());
    }

    /**
     * Enregistre les Observers de Intégration.
     */
    protected function _initInventoryObservers()
    {
        Core_EventDispatcher::getInstance()->addListener('Inventory_Model_GranularityDataProvider', 'Orga_Model_Granularity');
        Core_EventDispatcher::getInstance()->addListener('Inventory_Model_CellDataProvider', 'Orga_Model_Cell');
        Core_EventDispatcher::getInstance()->addListener('Inventory_Model_GranularityReport', 'DW_Model_Report');
    }

    /**
     * Configuration pour les ressources "CellDataProvider" des ACL
     */
    protected function _initInventoryACLCellDataProviderResourceTreeTraverser()
    {
        /** @var $usersResourceTreeTraverser User_Service_ACL_UsersResourceTreeTraverser */
        $cellDataProviderResourceTreeTraverser = Inventory_Service_ACLManager::getInstance();
        /** @var $aclService User_Service_ACL */
        $aclService = User_Service_ACL::getInstance();
        $aclService->setResourceTreeTraverser("Inventory_Model_CellDataProvider", $cellDataProviderResourceTreeTraverser);
    }

    /**
     * Listener de l'entity manager pour les authorisations sur cellules.
     */
    protected function _initInventoryACLManagerListener()
    {
        if (! Zend_Registry::isRegistered('EntityManagers')) {
            return;
        }
        $entityManagers = Zend_Registry::get('EntityManagers');
        /** @var $entityManager Doctrine\ORM\EntityManager */
        $entityManager = $entityManagers['default'];
        $events = [
            Doctrine\ORM\Events::postFlush,
        ];
        $entityManager->getEventManager()->addEventListener($events, Inventory_Service_ACLManager::getInstance());
    }

}
