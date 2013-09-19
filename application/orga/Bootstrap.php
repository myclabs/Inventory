<?php

/**
 * @author valentin.claras
 */
class Orga_Bootstrap extends Core_Package_Bootstrap
{

    /**
     * Enregistre les Workers
     */
    protected function _initOrgaWorker()
    {
        /**@var \MyCLabs\Work\Worker\Worker $worker */
        $worker = $this->container->get('MyCLabs\Work\Worker\Worker');
        $worker->registerTaskExecutor(
            'Orga_Work_Task_AddGranularity',
            $this->container->get('Orga_Work_TaskExecutor_AddGranularityExecutor')
        );
        $worker->registerTaskExecutor(
            'Orga_Work_Task_AddMember',
            $this->container->get('Orga_Work_TaskExecutor_AddMemberExecutor')
        );
        $worker->registerTaskExecutor(
            'Orga_Work_Task_SetGranularityCellsGenerateDWCubes',
            $this->container->get('Orga_Work_TaskExecutor_SetGranularityCellsGenerateDWCubesExecutor')
        );
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
    protected function _initOrgaACLResourceTreeTraverser()
    {
        /** @var $usersResourceTreeTraverser User_Service_ACL_UsersResourceTreeTraverser */
        $resourceTreeTraverser = $this->container->get('Orga_Service_ACLManager');
        /** @var $aclService User_Service_ACL */
        $aclService = $this->container->get('User_Service_ACL');

        $aclService->setResourceTreeTraverser("Orga_Model_Cell", $resourceTreeTraverser);
        $aclService->setResourceTreeTraverser("DW_Model_Report", $resourceTreeTraverser);
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
