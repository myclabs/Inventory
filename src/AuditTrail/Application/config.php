<?php

use AuditTrail\Domain\EntryRepository;
use Doctrine\ORM\EntityManager;
use Interop\Container\ContainerInterface;
use AuditTrail\Domain\Entry;

return [

    EntryRepository::class => \DI\factory(function (ContainerInterface $c) {
        return $c->get(EntityManager::class)->getRepository(Entry::class);
    })

];
