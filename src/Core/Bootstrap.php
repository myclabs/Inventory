<?php
/**
 * @author     matthieu.napoli
 * @package    Core
 * @subpackage Bootstrap
 */

use Core\Autoloader;
use Core\Log\ChromePHPFormatter;
use Core\Log\ExtendedLineFormatter;
use Core\Mail\NullTransport;
use DI\Container;
use DI\ContainerBuilder;
use DI\Definition\FileLoader\YamlDefinitionFileLoader;
use Doctrine\Common\Cache\ApcCache;
use Doctrine\Common\Cache\ArrayCache;
use Doctrine\Common\Proxy\AbstractProxyFactory;
use Doctrine\ORM\Tools\Setup;
use Monolog\Formatter\LineFormatter;
use Monolog\Handler\ChromePHPHandler;
use Monolog\Handler\FirePHPHandler;
use Monolog\Handler\StreamHandler;
use Monolog\Logger;
use Monolog\Processor\PsrLogMessageProcessor;

/**
 * Classe de bootstrap : initialisation de l'application.
 *
 * @package    Core
 * @subpackage Bootstrap
 */
abstract class Core_Bootstrap extends Zend_Application_Bootstrap_Bootstrap
{

    /**
     * @var Container
     */
    protected $container;

    /**
     * Lance en priorité nos méthodes "_init", puis ensuite celles des classes filles.
     * @param null|string|array $resource
     */
    protected function _bootstrap($resource = null)
    {
        if ($resource !== null) {
            parent::_bootstrap($resource);
        } else {
            // Lance les méthodes prioritaires
            $resources = array(
                'Autoloader',
                'UTF8',
                'Container',
                'Translations',
                'Log',
                'ErrorHandler',
                'FrontController',
                'Doctrine',
                'DefaultEntityManager',
                'WorkDispatcher',
                // Il faut initialiser le front controller pour que l'ajout de dossiers
                // de controleurs soit pris en compte
            );
            parent::_bootstrap($resources);
            // Lance toutes les autres méthodes (moins prioritaires)
            parent::_bootstrap();
        }
    }

    /**
     * Initialise du module par défaut.
     */
    protected function _initAutoloader()
    {
        /** @noinspection PhpIncludeInspection */
        require PACKAGE_PATH . '/vendor/autoload.php';
        Autoloader::getInstance()->register();
    }

    /**
     * UTF-8.
     */
    protected function _initUTF8()
    {
        if (APPLICATION_ENV != 'testsunitaires') {
            header('Content-Type: text/html; charset=utf-8');
        }
        // Définit l'encodage pour l'extension mb_string
        mb_internal_encoding('UTF-8');
    }

    /**
     * Initialize the dependency injection container
     */
    protected function _initContainer()
    {
        // Récupère la configuration
        $configuration = new Zend_Config($this->getOptions());

        $builder = new ContainerBuilder();
        $builder->addDefinitionsFromFile(new YamlDefinitionFileLoader(APPLICATION_PATH . '/configs/di.yml'));
        $diConfig = $configuration->get('di', null);
        if ($diConfig && (bool) $diConfig->get('cache', false)) {
            $cache = new ApcCache();
            $cache->setNamespace($configuration->get('applicationName', ''));
            $builder->setDefinitionCache($cache);
        }

        $this->container = $builder->build();

        if (isset($cache)) {
            $this->container->set('Doctrine\Common\Cache\Cache', $cache);
        }

        Zend_Registry::set('configuration', $configuration);
        Zend_Registry::set('applicationName', $configuration->get('applicationName', ''));
        Zend_Registry::set('container', $this->container);

        // Copie des éléments de configuration dans le container
        $this->container->set('application.name', $configuration->get('applicationName', ''));
        $this->container->set('email.contact.address', $configuration->emails->contact->adress);
        $this->container->set('email.noreply.name', $configuration->emails->noreply->name);
        $this->container->set('email.noreply.address', $configuration->emails->noreply->adress);

        // Configuration pour injecter dans les controleurs (intégration ZF1)
        $dispatcher = new \DI\ZendFramework1\Dispatcher();
        $dispatcher->setContainer($this->container);
        $frontController = Zend_Controller_Front::getInstance();
        $frontController->setDispatcher($dispatcher);
    }

    /**
     * Log des erreurs
     */
    protected function _initLog()
    {
        $configuration = Zend_Registry::get('configuration');
        $cli = (PHP_SAPI == 'cli');

        $logger = new Logger('log');

        // Log vers la console (si configuré ou PHP CLI)
        if ($configuration->log->stdout || $cli) {
            $loggerHandler = new StreamHandler('php://stdout', Logger::DEBUG);
            $loggerHandler->setFormatter(new ExtendedLineFormatter());
            $logger->pushHandler($loggerHandler);
        }
        // Log dans un fichier
        if ($configuration->log->file && !$cli) {
            $file = $this->container->get('log.file');
            $fileHandler = new StreamHandler(PACKAGE_PATH . '/' . $file, Logger::DEBUG);
            $fileHandler->setFormatter(new ExtendedLineFormatter());
            $logger->pushHandler($fileHandler);
        }
        // Log FirePHP
        if ($configuration->log->firephp && !$cli) {
            ini_set('html_errors', false);
            $logger->pushHandler(new FirePHPHandler());
            $chromePHPHandler = new ChromePHPHandler();
            $chromePHPHandler->setFormatter(new ChromePHPFormatter());
            $logger->pushHandler($chromePHPHandler);
        }

        /** @noinspection PhpParamsInspection */
        $logger->pushProcessor(new PsrLogMessageProcessor());

        $this->container->set('Psr\Log\LoggerInterface', $logger);

        // Log des requetes
        if ($configuration->log->queries) {
            $file = $this->container->get('log.query.file');
            $queryLoggerHandler = new StreamHandler(PACKAGE_PATH . '/' . $file, Logger::DEBUG);
            $queryLoggerHandler->setFormatter(new LineFormatter("%message%" . PHP_EOL));
            $queryLogger = new Logger('log.query', [$queryLoggerHandler]);
            $this->container->set('log.query', $queryLogger);
        }
    }

    /**
     * Gestion des erreurs.
     */
    protected function _initErrorHandler()
    {
        if (APPLICATION_ENV != 'testsunitaires') {
            $errorHandler = $this->container->get('Core\Log\ErrorHandler');
            // Fonctions de gestion des erreurs
            set_error_handler(array($errorHandler, 'myErrorHandler'));
            set_exception_handler(array($errorHandler, 'myExceptionHandler'));
            register_shutdown_function(array($errorHandler, 'myShutdownFunction'));
        }
    }

    /**
     * Initialise Doctrine pour utiliser l'autoloader de Zend.
     */
    protected function _initDoctrine()
    {
        $configuration = Zend_Registry::get('configuration');
        // Création de la configuration de Doctrine.
        $doctrineConfig = new Doctrine\ORM\Configuration();

        // Définition du cache en fonction de l'environement.
        switch (APPLICATION_ENV) {
            case 'test':
            case 'production':
                $cache = $this->container->get('Doctrine\Common\Cache\Cache');
                $doctrineAutoGenerateProxy = AbstractProxyFactory::AUTOGENERATE_NEVER;
                break;
            default:
                $cache = new ArrayCache();
                $doctrineAutoGenerateProxy = AbstractProxyFactory::AUTOGENERATE_EVAL;
                break;
        }

        // Choix du driver utilisé par le schema.
        //  Utilisation d'un driver YAML.
        //  Les fichiers de mapping porteront l'extension '.yml'.
        $doctrineYAMLDriver = new Doctrine\ORM\Mapping\Driver\YamlDriver(
            array(
                 APPLICATION_PATH . '/models/mappers'
            ),
            '.yml'
        );

        // Annotations pour les extensions Doctrine
        $driverChain = new \Doctrine\ORM\Mapping\Driver\DriverChain();
        $driverChain->setDefaultDriver($doctrineYAMLDriver);
        // Juste pour enregistrer les annotations doctrine dans le registry
        $doctrineConfig->newDefaultAnnotationDriver();
        $cachedAnnotationReader = new Doctrine\Common\Annotations\CachedReader(
            new Doctrine\Common\Annotations\AnnotationReader(),
            $cache
        );
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
        $doctrineConfig->setAutoGenerateProxyClasses($doctrineAutoGenerateProxy);
        // Ligne inutile mais bug, cf. http://www.doctrine-project.org/jira/browse/DCOM-210#comment-21061
        $doctrineConfig->setProxyDir(PACKAGE_PATH . '/data/proxies');

        // Log des requêtes
        if ($configuration->log->queries) {
            $doctrineConfig->setSQLLogger($this->container->get('Core\Log\QueryLogger'));
        }

        // Enregistrement de la configuration Doctrine dans le Registry.
        //  Utile pour créer d'autres EntityManager.
        Zend_Registry::set('doctrineConfiguration', $doctrineConfig);
    }

    /**
     * Initialize Doctrine
     */
    protected function _initDefaultEntityManager()
    {
        $entityManager = $this->createDefaultEntityManager();

        // Enregistrement de l'entityManager par défault dans le Registry.
        // Les prochains devront être ajouté au tableau.
        $entityManagers = array('default' => $entityManager);
        Zend_Registry::set('EntityManagers', $entityManagers);
        $this->container->set('Doctrine\ORM\EntityManager', $entityManager);
    }

    /**
     * Plugin qui configure l'extension Doctrine Loggable
     */
    protected function _initLoggableExtension()
    {
        $front = Zend_Controller_Front::getInstance();
        $front->registerPlugin($this->container->get('Inventory_Plugin_LoggableExtensionConfigurator'));
    }

    /**
     * Crée l'entity manager utilisé par défaut. Méthode utilise pour recréer un entity manager
     * si celui-ci se ferme à cause d'une exception
     * @param null $connectionSettings
     * @return Core_ORM_EntityManager
     */
    public function createDefaultEntityManager($connectionSettings = null)
    {
        if ($connectionSettings == null) {
            // Récupération de la configuration de la connexion dans l'application.ini
            /** @var mixed $connectionSettings */
            $connectionSettings = Zend_Registry::get('configuration')->doctrine->default->connection;
        }

        $connectionArray = array(
            'driver'        => $connectionSettings->driver,
            'user'          => $connectionSettings->user,
            'password'      => $connectionSettings->password,
            'dbname'        => $connectionSettings->dbname,
            'host'          => $connectionSettings->host,
            'port'          => $connectionSettings->port,
            'driverOptions' => array(
                1002 => 'SET NAMES utf8'
            ),
        );

        /* @var $doctrineConfig Doctrine\ORM\Configuration */
        $doctrineConfig = Zend_Registry::get('doctrineConfiguration');

        // Création de l'EntityManager depuis la configuration de doctrine.
        $em = Core_ORM_EntityManager::create($connectionArray, $doctrineConfig);

        // Extension de traduction de champs
        $translatableListener = new Gedmo\Translatable\TranslatableListener();
        $translatableListener->setTranslatableLocale(Core_Locale::loadDefault()->getLanguage());
        $translatableListener->setDefaultLocale(Zend_Registry::get('configuration')->translation->defaultLocale);
        $translatableListener->setPersistDefaultLocaleTranslation(true);
        $translatableListener->setTranslationFallback(true);
        $em->getEventManager()->addEventSubscriber($translatableListener);
        Zend_Registry::set('doctrineTranslate', $translatableListener);
        $this->container->set('Gedmo\Translatable\TranslatableListener', $translatableListener);

        // Extension de versionnement de champs
        $loggableListener = new Gedmo\Loggable\LoggableListener();
        $em->getEventManager()->addEventSubscriber($loggableListener);
        $this->container->set('Gedmo\Loggable\LoggableListener', $loggableListener);

        return $em;
    }

    /**
     * Work dispatcher
     */
    protected function _initWorkDispatcher()
    {
        // Détermine si on utilise gearman
        $configuration = Zend_Registry::get('configuration');
        if (isset($configuration->gearman) && isset($configuration->gearman->enabled)) {
            $useGearman = (bool) $configuration->gearman->enabled;
        } else {
            $useGearman = true;
        }
        $useGearman = $useGearman && extension_loaded('gearman');

        $this->container->set('Core_Work_Dispatcher', function(Container $c) use ($useGearman) {
                if ($useGearman) {
                    $implementation = 'Core_Work_GearmanDispatcher';
                } else {
                    $implementation = 'Core_Work_SimpleDispatcher';
                }
                /** @var Core_Work_Dispatcher $workDispatcher */
                $workDispatcher = $c->get($implementation);
                // Register workers
                $workDispatcher->registerWorker($this->container->get('Core_Work_ServiceCall_Worker'));

                return $workDispatcher;
            });
    }

    /**
     * Traductions
     */
    protected function _initTranslations()
    {
        // Langues
        $configuration = Zend_Registry::get('configuration');
        if (isset($configuration->translation)) {
            $languages = $configuration->translation->languages->toArray();
        } else {
            $languages = [];
        }

        Zend_Registry::set('languages', $languages);
    }

    /**
     * Session namespace
     */
    protected function _initSessionNamespace()
    {
        $auth = Zend_Auth::getInstance();
        $name = $this->container->get('application.name');
        if ($name == '') {
            $configuration = Zend_Registry::get('configuration');
            $name = $configuration->sessionStorage->name;
        }
        $auth->setStorage(new Zend_Auth_Storage_Session($name));
    }

    /**
     * Place le bootstrap dans le registry pour être accessible dans les TestCase
     */
    protected function _initBootstrapInRegistry()
    {
        Zend_Registry::set('bootstrap', $this);
    }

    /**
     * Enregistre les plugins de Core.
     */
    protected function _initPluginCore()
    {
        $front = Zend_Controller_Front::getInstance();
        $front->registerPlugin(new Core_Plugin_Flush());
    }

    /**
     * Envoi de mail.
     */
    protected function _initMail()
    {
        if (APPLICATION_ENV == 'testsunitaires') {
            Zend_Mail::setDefaultTransport(new NullTransport());
        }
    }

    /**
     * Configuration de FirePHP.
     *
     * @see http://www.firephp.org/HQ/Use.htm
     */
    protected function _initFirePHP()
    {
        if ((APPLICATION_ENV == 'developpement') || (APPLICATION_ENV == 'test')) {
            // Configuration de FirePHP
            $firePHP = Zend_Wildfire_Plugin_FirePhp::getInstance();
            $firePHP->setOption('maxObjectDepth', 1);
            $firePHP->setOption('maxArrayDepth', 1);
            // On filtre les classes Zend pour les ignorer
            $firePHP->setObjectFilter(
                'Bootstrap',
                array(
                    '_application',
                    '_classResources',
                    '_container',
                    '_optionKeys',
                    '_options',
                    '_pluginLoader',
                    '_pluginResources',
                    '_run',
                    'frontController',
                )
            );
            $firePHP->setObjectFilter(
                'Zend_Controller_Front',
                array(
                    '_dispatcher',
                    '_plugins',
                    '_request',
                    '_response',
                    '_router',
                )
            );
            $firePHP->setObjectFilter(
                'Zend_View',
                array(
                    '_path',
                    '_helper',
                    '_loaders',
                    '_file',
                )
            );
            $firePHP->setObjectFilter(
                'Zend_View_Helper_Partial',
                array(
                    'view',
                )
            );
        }
    }

}
