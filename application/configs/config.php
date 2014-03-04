<?php

use Account\Domain\ACL\AccountAdminRole;
use DI\Container;
use Doctrine\ORM\EntityManager;
use MyCLabs\ACL\Doctrine\ACLSetup;
use User\Domain\ACL\Actions;
use Inventory\Command\CreateDBCommand;
use Inventory\Command\UpdateDBCommand;
use JMS\Serializer\Serializer;
use JMS\Serializer\SerializerBuilder;
use MyCLabs\ACL\ACL;
use MyCLabs\ACL\CascadeStrategy\SimpleCascadeStrategy;
use Orga\Model\ACL\CellAdminRole;
use Orga\Model\ACL\CellContributorRole;
use Orga\Model\ACL\CellManagerRole;
use Orga\Model\ACL\CellObserverRole;
use Orga\Model\ACL\CellResourceGraphTraverser;
use Orga\Model\ACL\OrganizationAdminRole;
use Orga\Model\ACL\OrganizationResourceGraphTraverser;
use Symfony\Component\EventDispatcher\EventDispatcher;
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

    // ACL
    'enable.acl' => true,

    // Feature flags
    'feature.register' => false,

    // Surcharge du nombre de chiffres significatifs
    // Fonctionnalité spéciale pour art225 et art255
    'locale.minSignificantFigures' => null,

    // Event manager
    EventDispatcher::class => DI\factory(function (Container $c) {
        $dispatcher = new EventDispatcher();

        // User events (plus prioritaire)
        $userEventListener = $c->get(\User\Domain\Event\EventListener::class);
        $dispatcher->addListener(Orga_Service_InputCreatedEvent::NAME, [$userEventListener, 'onUserEvent'], 10);
        $dispatcher->addListener(Orga_Service_InputEditedEvent::NAME, [$userEventListener, 'onUserEvent'], 10);

        // AuditTrail
        $auditTrailListener = $c->get(AuditTrail\Application\Service\EventListener::class);
        $dispatcher->addListener(Orga_Service_InputCreatedEvent::NAME, [$auditTrailListener, 'onInputCreated']);
        $dispatcher->addListener(Orga_Service_InputEditedEvent::NAME, [$auditTrailListener, 'onInputEdited']);

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

    Orga_Service_ETLStructure::class => DI\object()
            ->constructorParameter('defaultLocale', DI\link('translation.defaultLocale'))
            ->constructorParameter('locales', DI\link('translation.languages')),

    // ACL
    ACL::class => DI\factory(function (Container $c) {
        $em = $c->get(EntityManager::class);

        $cascadeStrategy = new SimpleCascadeStrategy($em);
        $cascadeStrategy->setResourceGraphTraverser(
            Orga_Model_Organization::class,
            $c->get(OrganizationResourceGraphTraverser::class)
        );
        $cascadeStrategy->setResourceGraphTraverser(
            Orga_Model_Cell::class,
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
        $setup->registerRoleClass(OrganizationAdminRole::class, 'organizationAdmin');
        $setup->registerRoleClass(CellAdminRole::class, 'cellAdmin');
        $setup->registerRoleClass(CellManagerRole::class, 'cellManager');
        $setup->registerRoleClass(CellContributorRole::class, 'cellContributor');
        $setup->registerRoleClass(CellObserverRole::class, 'cellObserver');
        return $setup;
    }),

    Serializer::class => DI\factory(function () {
        return SerializerBuilder::create()
            ->addMetadataDir(PACKAGE_PATH . '/src/Inventory/Serializer')
            ->addMetadataDir(PACKAGE_PATH . '/src/Techno/Architecture/Serializer', 'Techno\Domain')
            ->build();
    }),

];
