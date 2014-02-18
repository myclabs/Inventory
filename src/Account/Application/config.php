<?php

use Account\Domain\Account;
use Account\Domain\AccountRepository;
use DI\Container;
use Doctrine\ORM\EntityManager;

return [

    AccountRepository::class => DI\factory(function (Container $c) {
        return $c->get(EntityManager::class)->getRepository(Account::class);
    }),

];
