<?php

namespace AuditTrail\Application;

use AuditTrail\Domain\Entry;
use AuditTrail\Domain\EntryRepository;
use Core_Package_Bootstrap;
use DI\Container;
use Doctrine\ORM\EntityManager;

/**
 * Bootstrap
 *
 * @author matthieu.napoli
 */
class Bootstrap extends Core_Package_Bootstrap
{
    /**
     * Enregistrement des repository
     */
    protected function _initAuditTrailRepositories()
    {
        $this->container->set(
            EntryRepository::class,
            \DI\factory(function (Container $c) {
                return $c->get(EntityManager::class)->getRepository(Entry::class);
            })
        );
    }
}
