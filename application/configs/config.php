<?php

use User\Domain\UserService;

return [
    // Nom de l'application installée
    // Ceci est un code, et ne doit pas être affiché à l'utilisateur
    'application.name' => 'Inventory',
    // Namespace pour les sauvegarde en session
    'session.storage.name' => DI\link('application.name'),

    // Répertoire d'upload les documents
    'documents.path' => PACKAGE_PATH . '/data/documents',

    // Emails
    'emails.noreply.name'   => 'My C-Tool',
    'emails.noreply.adress' => 'noreply@myc-sense.com',
    'emails.contact.adress' => 'contact@myc-sense.com',
    UserService::class      => DI\object()
            ->methodParameter('__construct', 'contactEmail', DI\link('emails.contact.adress'))
            ->methodParameter('__construct', 'noReplyEmail', DI\link('emails.noreply.adress'))
            ->methodParameter('__construct', 'noReplyName', DI\link('emails.noreply.name')),

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
];
