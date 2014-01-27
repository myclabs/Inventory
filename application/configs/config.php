<?php

use MyCLabs\UnitAPI\UnitOperationService;
use MyCLabs\UnitAPI\UnitService;
use MyCLabs\UnitAPI\WebService\UnitOperationWebService;
use MyCLabs\UnitAPI\WebService\UnitWebService;
use User\Domain\UserService;

return [
    // Nom de l'application installée
    // Ceci est un code, et ne doit pas être affiché à l'utilisateur
    'application.name' => 'Inventory',
    'application.url' => '',
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
            ->methodParameter('__construct', 'noReplyName', DI\link('emails.noreply.name'))
            ->methodParameter('__construct', 'applicationUrl', DI\link('application.url')),

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

    // Fonctionnalité spéciale pour art225 et art255
    'locale.minSignificantFigures' => null,

    // Units API
    'units.webservice.url' => 'http://localhost:8000/api/',
    'units.webservice.httpClient' => DI\object(\Guzzle\Http\Client::class)
        ->constructor([ 'baseUrl' => DI\link('units.webservice.url') ]),
    UnitService::class => DI\object(UnitWebService::class)
        ->constructor(DI\link('units.webservice.httpClient')),
    UnitOperationService::class => DI\object(UnitOperationWebService::class)
        ->constructor(DI\link('units.webservice.httpClient')),
];
