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
        $dispatcher = $this->container->get('Core_Work_Dispatcher');
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
            /** @var Core_EventDispatcher $eventDispatcher */
            $eventDispatcher = $this->container->get('Core_EventDispatcher');

            $eventDispatcher->addListener('Orga_Model_GranularityReport', 'DW_Model_Report');
        }
    }

    /**
     * Configuration pour les ressources "Cell" des ACL
     */
    protected function _initOrgaACLCellResourceTreeTraverser()
    {
        /** @var $usersResourceTreeTraverser User_Service_ACL_UsersResourceTreeTraverser */
        $cellResourceTreeTraverser = $this->container->get('Orga_Service_ACLManager');
        /** @var $aclService User_Service_ACL */
        $aclService = $this->container->get('User_Service_ACL');

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

        if (APPLICATION_ENV != 'testsunitaires') {
            $aclManager = $this->container->get('Orga_Service_ACLManager');

            $entityManager->getEventManager()->addEventListener($events, $aclManager);
        }
    }

}
