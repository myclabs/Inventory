<?php
/**
 * @author valentin.claras
 * @package Orga
 */

error_reporting(E_ALL);

/**
 * @author valentin.claras
 * @package Orga
 */
class Orga_Bootstrap extends Core_Package_Bootstrap
{

    /**
     * Enregistre les Workers
     */
    protected function _initOrgaWorker()
    {
        /**@var Core_Work_Dispatcher $dispatcher */
        $dispatcher = Zend_Registry::get('workDispatcher');
        $dispatcher->registerWorker(new Orga_Work_WorkerGranularity());
        $dispatcher->registerWorker(new Orga_Work_WorkerMember());
        $dispatcher->registerWorker(new Orga_Work_Worker());
    }

    /**
     * Enregistre les Observers de Intégration.
     */
    protected function _initOrgaObservers()
    {
        if (APPLICATION_ENV != 'testsunitaires') {
            Core_EventDispatcher::getInstance()->addListener('Orga_Model_GranularityReport', 'DW_Model_Report');
        }
    }

    /**
     * Configuration pour les ressources "Cell" des ACL
     */
    protected function _initOrgaACLCellResourceTreeTraverser()
    {
        /** @var $usersResourceTreeTraverser User_Service_ACL_UsersResourceTreeTraverser */
        $cellResourceTreeTraverser = Orga_Service_ACLManager::getInstance();
        /** @var $aclService User_Service_ACL */
        $aclService = User_Service_ACL::getInstance();
        $aclService->setResourceTreeTraverser("Orga_Model_Cell", $cellResourceTreeTraverser);
    }

    /**
     * Listener de l'entity manager pour les authorisations sur cellules.
     */
    protected function _initOrgaACLManagerListener()
    {
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
    }

}
