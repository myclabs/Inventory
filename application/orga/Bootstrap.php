<?php
use User\Domain\ACL\ACLService;
use User\Domain\ACL\UsersResourceTreeTraverser;

/**
 * @author valentin.claras
 */
class Orga_Bootstrap extends Core_Package_Bootstrap
{

    /**
     * Enregistre les Observers de Intégration.
     */
    protected function _initOrgaObservers()
    {
        if (APPLICATION_ENV != 'testsunitaires') {
            /** @var Core_EventDispatcher $eventDispatcher */
            $eventDispatcher = $this->container->get(Core_EventDispatcher::class);

            $eventDispatcher->addListener(Orga_Model_GranularityReport::class, DW_Model_Report::class);
        }
    }

    /**
     * Configuration pour les ressources "Cell" des ACL
     */
    protected function _initOrgaACLResourceTreeTraverser()
    {
        /** @var $usersResourceTreeTraverser UsersResourceTreeTraverser */
        $resourceTreeTraverser = $this->container->get(Orga_Service_ACLManager::class);
        /** @var $aclService ACLService */
        $aclService = $this->container->get(ACLService::class);

        $aclService->setResourceTreeTraverser(Orga_Model_Cell::class, $resourceTreeTraverser);
        $aclService->setResourceTreeTraverser(DW_Model_Report::class, $resourceTreeTraverser);
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
            $aclManager = $this->container->get(Orga_Service_ACLManager::class);

            $entityManager->getEventManager()->addEventListener($events, $aclManager);
        }
    }

}
