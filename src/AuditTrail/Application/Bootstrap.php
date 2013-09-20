<?php
/**
 * @author matthieu.napoli
 */

namespace AuditTrail\Application;

use Core_Package_Bootstrap;
use DI\Container;

/**
 * Bootstrap
 */
class Bootstrap extends Core_Package_Bootstrap
{
    /**
     * Enregistrement des repository
     */
    protected function _initAuditrailRepositories()
    {
        $this->container->set(
            'AuditTrail\Domain\EntryRepository',
            function(Container $c) {
                return $c->get('Doctrine\ORM\EntityManager')->getRepository('AuditTrail\Domain\Entry');
            }
        );
    }
}
