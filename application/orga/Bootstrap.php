<?php

use User\Domain\ACL\ACLService;

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

            $eventDispatcher->addListener(Orga_Service_Report::class, DW_Model_Report::class);
        }
    }
}
