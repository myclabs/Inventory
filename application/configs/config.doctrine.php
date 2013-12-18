<?php

use Core\Log\QueryLogger;
use DI\Container;
use Doctrine\Common\Annotations\AnnotationReader;
use Doctrine\Common\Annotations\CachedReader;
use Doctrine\Common\Cache\Cache;
use Doctrine\Common\Proxy\AbstractProxyFactory;
use Doctrine\Common\Proxy\Autoloader as DoctrineProxyAutoloader;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Mapping\Driver\DriverChain;
use Doctrine\ORM\Mapping\Driver\YamlDriver;
use Gedmo\Loggable\LoggableListener;
use Gedmo\Translatable\TranslatableListener;

return [

    // Configuration de la connexion à la BDD
    'db.host'     => 'localhost',
    'db.driver'   => 'pdo_mysql',
    'db.name'     => 'inventory',
    'db.user'     => 'myc-sense',
    'db.password' => '',

    // Configuration Doctrine
    'doctrine.proxies.mode' => AbstractProxyFactory::AUTOGENERATE_FILE_NOT_EXISTS,
    'doctrine.configuration' => DI\factory(function (Container $c) {
        $doctrineConfig = new Doctrine\ORM\Configuration();

        $cache = $c->get(Cache::class);

        // Choix du driver utilisé par le schema.
        //  Utilisation d'un driver YAML.
        //  Les fichiers de mapping porteront l'extension '.yml'.
        $doctrineYAMLDriver = new YamlDriver([APPLICATION_PATH . '/models/mappers'], '.yml');

        // Annotations pour les extensions Doctrine
        $driverChain = new DriverChain();
        $driverChain->setDefaultDriver($doctrineYAMLDriver);
        // Juste pour enregistrer les annotations doctrine dans le registry
        $doctrineConfig->newDefaultAnnotationDriver();
        $cachedAnnotationReader = new CachedReader(new AnnotationReader(), $cache);
        Gedmo\DoctrineExtensions::registerMappingIntoDriverChainORM(
            $driverChain, // our metadata driver chain, to hook into
            $cachedAnnotationReader // our cached annotation reader
        );
        Zend_Registry::set('annotationReader', $cachedAnnotationReader);

        $doctrineConfig->setMetadataDriverImpl($driverChain);

        // Configuration de Doctrine pour utiliser le cache
        //  pour la création des requêtes, des résults, et du parsing des Métadata.
        $doctrineConfig->setQueryCacheImpl($cache);
        $doctrineConfig->setResultCacheImpl($cache);
        $doctrineConfig->setMetadataCacheImpl($cache);
        // Configuration des Proxies.
        $doctrineConfig->setProxyNamespace('Doctrine_Proxies');
        $doctrineConfig->setAutoGenerateProxyClasses($c->get('doctrine.proxies.mode'));
        $doctrineConfig->setProxyDir(PACKAGE_PATH . '/data/proxies');
        if ($c->get('doctrine.proxies.mode') !== AbstractProxyFactory::AUTOGENERATE_EVAL) {
            DoctrineProxyAutoloader::register($doctrineConfig->getProxyDir(), $doctrineConfig->getProxyNamespace());
        }

        // Log des requêtes
        if ($c->get('log.queries')) {
            $doctrineConfig->setSQLLogger($c->get(QueryLogger::class));
        }

        return $doctrineConfig;
    }),

    // Entity manager
    EntityManager::class => DI\factory(function (Container $c) {
        $connectionArray = [
            'driver'        => $c->get('db.driver'),
            'user'          => $c->get('db.user'),
            'password'      => $c->get('db.password'),
            'dbname'        => $c->get('db.name'),
            'host'          => $c->get('db.host'),
            'port'          => $c->get('db.port'),
            'driverOptions' => [ 1002 => 'SET NAMES utf8' ],
        ];

        /* @var $doctrineConfig Doctrine\ORM\Configuration */
        $doctrineConfig = $c->get('doctrine.configuration');

        // Création de l'EntityManager depuis la configuration de doctrine.
        $em = Core_ORM_EntityManager::create($connectionArray, $doctrineConfig);

        // Extension de traduction de champs
        $em->getEventManager()->addEventSubscriber($c->get(TranslatableListener::class));
        // Extension de versionnement de champs
        $em->getEventManager()->addEventSubscriber($c->get(LoggableListener::class));

        return $em;
    }),

    // Extensions Doctrine
    TranslatableListener::class => DI\factory(function () {
        $listener = new TranslatableListener();
        $listener->setTranslatableLocale(Core_Locale::loadDefault()->getLanguage());
        $listener->setDefaultLocale(Zend_Registry::get('configuration')->translation->defaultLocale);
        $listener->setPersistDefaultLocaleTranslation(true);
        $listener->setTranslationFallback(true);
        Zend_Registry::set('doctrineTranslate', $listener);
        return $listener;
    }),

];
