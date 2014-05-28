<?php

use Core\Log\QueryLogger;
use Doctrine\Common\Annotations\AnnotationReader;
use Doctrine\Common\Annotations\CachedReader;
use Doctrine\Common\Cache\Cache;
use Doctrine\Common\Proxy\AbstractProxyFactory;
use Doctrine\Common\Proxy\Autoloader as DoctrineProxyAutoloader;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Mapping\Driver\AnnotationDriver;
use Doctrine\ORM\Mapping\Driver\DriverChain;
use Doctrine\ORM\Mapping\Driver\SimplifiedYamlDriver;
use Doctrine\ORM\Mapping\Driver\YamlDriver;
use Gedmo\Loggable\LoggableListener;
use Interop\Container\ContainerInterface;
use MyCLabs\ACL\ACL;
use MyCLabs\ACL\Doctrine\ACLSetup;

return [

    // Configuration de la connexion à la BDD
    'db.host'     => 'localhost',
    'db.port'     => 3306,
    'db.driver'   => 'pdo_mysql',
    'db.name'     => 'inventory',
    'db.user'     => 'myc-sense',
    'db.password' => '',

    // Configuration Doctrine
    'doctrine.proxies.mode' => AbstractProxyFactory::AUTOGENERATE_FILE_NOT_EXISTS,
    'doctrine.configuration' => DI\factory(function (ContainerInterface $c) {
        $doctrineConfig = new Doctrine\ORM\Configuration();

        $cache = $c->get(Cache::class);

        $paths = [
            APPLICATION_PATH . '/models/mappers',
            APPLICATION_PATH . '/dw/models/mappers',
            APPLICATION_PATH . '/orga/models/mappers',
        ];
        $doctrineYAMLDriver = new YamlDriver($paths, '.yml');

        // Annotations pour les extensions Doctrine
        $driverChain = new DriverChain();
        $driverChain->setDefaultDriver($doctrineYAMLDriver);
        // Juste pour enregistrer les annotations doctrine dans le registry
        $doctrineConfig->newDefaultAnnotationDriver();
        $annotationReader = new AnnotationReader();
        Gedmo\DoctrineExtensions::registerMappingIntoDriverChainORM(
            $driverChain, // our metadata driver chain, to hook into
            $annotationReader // our annotation reader
        );

        // Annotations pour MyCLabs\ACL
        $annotationDriver = new AnnotationDriver(
            new CachedReader(new AnnotationReader(), $cache),
            [ PACKAGE_PATH . '/vendor/myclabs/acl/src/Model' ]
        );
        $driverChain->addDriver($annotationDriver, 'MyCLabs\ACL\Model');

        // Nouveaux packages utilisent le simplified driver
        $modules = [
            'User',
            'Classification',
            'Parameter',
            'Doc',
            'AuditTrail',
            'AF',
            'Account',
        ];
        foreach ($modules as $module) {
            $yamlDriver = new SimplifiedYamlDriver(
                [PACKAGE_PATH . '/src/' . $module . '/Architecture/DBMapper' => $module . '\Domain'],
                '.yml'
            );
            $driverChain->addDriver($yamlDriver, $module . '\Domain');
        }

        // Translation
        $yamlDriver = new SimplifiedYamlDriver(
            [PACKAGE_PATH . '/src/Core/Translation' => 'Core\Translation'],
            '.yml'
        );
        $driverChain->addDriver($yamlDriver, 'Core\Translation');

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
    EntityManager::class => DI\factory(function (ContainerInterface $c) {
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

        $evm = $em->getEventManager();

        // Extension de versionnement de champs
        $evm->addEventSubscriber($c->get(LoggableListener::class));

        // Configuration pour MyCLabs\ACL
        /** @var ACLSetup $aclSetup */
        $aclSetup = $c->get(ACLSetup::class);
        $aclSetup->setUpEntityManager($em, function () use ($c) {
            return $c->get(ACL::class);
        });

        return $em;
    }),

];
