<?php

use Account\Domain\Account;
use Account\Domain\AccountRepository;
use Doctrine\ORM\EntityManager;
use Interop\Container\ContainerInterface;

return [

    AccountRepository::class => DI\factory(function (ContainerInterface $c) {
        return $c->get(EntityManager::class)->getRepository(Account::class);
    }),

    // Compte My C-Sense, référencé en dur
    'account.myc-sense' => DI\factory(function (ContainerInterface $c) {
        /** @var AccountRepository $accountRepository */
        $accountRepository = $c->get(AccountRepository::class);
        return $accountRepository->get(1);
    }),

];
