<?php

namespace Orga\Application;

use Core_Package_Bootstrap;
use Core_EventDispatcher;
use Orga\Domain\Service\ETL\OrgaReportFactory;
use DW\Domain\Report;

/**
 * @author valentin.claras
 */
class Bootstrap extends Core_Package_Bootstrap
{
    /**
     * Enregistre les Observers de Intégration.
     */
    protected function _initOrgaObservers()
    {
        if (APPLICATION_ENV != 'testsunitaires') {
            /** @var Core_EventDispatcher $eventDispatcher */
            $eventDispatcher = $this->container->get(Core_EventDispatcher::class);

            $eventDispatcher->addListener(OrgaReportFactory::class, Report::class);
        }
    }
}
