<?php

use Account\Domain\ACL\AccountAdminRole;
use Doctrine\Common\Cache\Cache;
use Doctrine\ORM\EntityManager;
use Interop\Container\ContainerInterface;
use Mnapoli\Translated\Translator;
use MyCLabs\ACL\Doctrine\ACLSetup;
use Orga\Domain\Cell;
use Orga\Domain\Workspace;
use Orga\Domain\Service\Cell\Input\CellInputService;
use Orga\Domain\Service\Cell\Input\CellInputCreatedEvent;
use Orga\Domain\Service\Cell\Input\CellInputEditedEvent;
use Unit\Service\CachedUnitOperationService;
use Unit\Service\CachedUnitService;
use User\Domain\ACL\Actions;
use Inventory\Command\CreateDBCommand;
use Inventory\Command\UpdateDBCommand;
use MyCLabs\ACL\ACL;
use MyCLabs\ACL\CascadeStrategy\SimpleCascadeStrategy;
use Orga\Domain\ACL\CellAdminRole;
use Orga\Domain\ACL\CellContributorRole;
use Orga\Domain\ACL\CellManagerRole;
use Orga\Domain\ACL\CellObserverRole;
use Orga\Domain\ACL\CellResourceGraphTraverser;
use Orga\Domain\ACL\WorkspaceAdminRole;
use Orga\Domain\ACL\WorkspaceResourceGraphTraverser;
use Symfony\Component\EventDispatcher\EventDispatcher;
use MyCLabs\UnitAPI\UnitOperationService;
use MyCLabs\UnitAPI\UnitService;
use MyCLabs\UnitAPI\WebService\UnitOperationWebService;
use MyCLabs\UnitAPI\WebService\UnitWebService;
use User\Domain\ACL\AdminRole;
use User\Domain\User;

return [
    // Nom de l'application installée
    // Ceci est un code, et ne doit pas être affiché à l'utilisateur
    'application.name' => 'Inventory',
    'application.url' => '',
    // Namespace pour les sauvegarde en session
    'session.storage.name' => DI\link('application.name'),

    'debug.login' => false,

    // Répertoire d'upload les documents
    'documents.path' => PACKAGE_PATH . '/data/documents',

    // Répertoire d'enregistrement des exports
    'exports.inputs.path' => PACKAGE_PATH . '/data/exports/inputs',

    // Emails
    'emails.noreply.name'   => 'My C-Tool',
    'emails.noreply.adress' => 'noreply@myc-sense.com',
    'emails.contact.adress' => 'contact@myc-sense.com',

    // Chemin vers les fichier de fonts (nécéssaire pour le Captcha)
    'police.path' => 'data/fonts/',

    // Chemin vers l'exécutable mysql (nécessaire pour les scripts)
    'mysqlBin.path' => '/usr/bin/mysql',

    // Langues supportées par l'application
    'translation.defaultLocale' => 'fr',
    'translation.languages'     => ['fr', 'en'],
    'translation.fallbacks'     => [
        'fr' => ['en'],
        'en' => ['fr'],
    ],
    Translator::class => DI\object()
            ->constructor(DI\link('translation.defaultLocale'), DI\link('translation.fallbacks')),

    // ACL
    'enable.acl' => true,

    // Feature flags
    'feature.register' => false,

    // Surcharge du nombre de chiffres significatifs
    // Fonctionnalité spéciale pour art225 et art255
    'locale.minSignificantFigures' => null,

    // Event manager
    EventDispatcher::class => DI\factory(function (ContainerInterface $c) {
        $dispatcher = new EventDispatcher();

        // User events (plus prioritaire)
        $userEventListener = $c->get(\User\Domain\Event\EventListener::class);
        $dispatcher->addListener(CellInputCreatedEvent::NAME, [$userEventListener, 'onUserEvent'], 10);
        $dispatcher->addListener(CellInputEditedEvent::NAME, [$userEventListener, 'onUserEvent'], 10);

        // AuditTrail
        $auditTrailListener = $c->get(AuditTrail\Application\Service\EventListener::class);
        $dispatcher->addListener(CellInputCreatedEvent::NAME, [$auditTrailListener, 'onInputCreated']);
        $dispatcher->addListener(CellInputEditedEvent::NAME, [$auditTrailListener, 'onInputEdited']);

        return $dispatcher;
    }),

    CreateDBCommand::class => DI\object()
            ->constructor(
                DI\link(UpdateDBCommand::class),
                DI\link('db.host'),
                DI\link('db.port'),
                DI\link('db.user'),
                DI\link('db.password'),
                DI\link('db.name')
            ),
    UpdateDBCommand::class => DI\object()
            ->constructor(DI\link(EntityManager::class), DI\link('db.name')),


    // ACL
    ACL::class => DI\factory(function (ContainerInterface $c) {
        $em = $c->get(EntityManager::class);

        $cascadeStrategy = new SimpleCascadeStrategy($em);
        $cascadeStrategy->setResourceGraphTraverser(
            Workspace::class,
            $c->get(WorkspaceResourceGraphTraverser::class)
        );
        $cascadeStrategy->setResourceGraphTraverser(
            Cell::class,
            $c->get(CellResourceGraphTraverser::class)
        );

        return new ACL($em, $cascadeStrategy);
    }),
    ACLSetup::class => DI\factory(function () {
        $setup = new ACLSetup();
        $setup->setSecurityIdentityClass(User::class);
        $setup->setActionsClass(Actions::class);
        $setup->registerRoleClass(AdminRole::class, 'superadmin');
        $setup->registerRoleClass(AccountAdminRole::class, 'accountAdmin');
        $setup->registerRoleClass(WorkspaceAdminRole::class, 'organizationAdmin');
        $setup->registerRoleClass(CellAdminRole::class, 'cellAdmin');
        $setup->registerRoleClass(CellManagerRole::class, 'cellManager');
        $setup->registerRoleClass(CellContributorRole::class, 'cellContributor');
        $setup->registerRoleClass(CellObserverRole::class, 'cellObserver');
        return $setup;
    }),


    // Units API
    'units.webservice.url' => 'http://units.myc-sense.com/api/',
    'units.webservice.httpClient' => DI\factory(function (ContainerInterface $c) {
        return new \GuzzleHttp\Client([
            'base_url' => $c->get('units.webservice.url'),
        ]);
    }),
    UnitWebService::class => DI\object()
            ->constructor(DI\link('units.webservice.httpClient')),
    UnitService::class => DI\object(CachedUnitService::class)
            ->constructor(DI\link(UnitWebService::class), DI\link(Cache::class)),
    UnitOperationWebService::class => DI\object()
            ->constructor(DI\link('units.webservice.httpClient')),
    UnitOperationService::class => DI\object(CachedUnitOperationService::class)
            ->constructor(DI\link(UnitOperationWebService::class), DI\link(Cache::class)),
];
