<?php

use Core\Autoloader;
use Core\Controller\FlushPlugin;
use Core\Log\ChromePHPFormatter;
use Core\Log\ErrorHandler;
use Core\Log\ExtendedLineFormatter;
use Core\Log\QueryLogger;
use Core\Log\UserInfoProcessor;
use Core\Mail\NullTransport;
use Core\Work\EventListener\RabbitMQEventListener;
use Core\Work\EventListener\SimpleEventListener;
use Core\Work\ServiceCall\ServiceCallTask;
use DI\Container;
use DI\ContainerBuilder;
use DI\Definition\FileLoader\YamlDefinitionFileLoader;
use Doctrine\Common\Annotations\AnnotationReader;
use Doctrine\Common\Annotations\CachedReader;
use Doctrine\Common\Cache\ArrayCache;
use Doctrine\Common\Cache\Cache;
use Doctrine\Common\Cache\MemcachedCache;
use Doctrine\Common\Proxy\AbstractProxyFactory;
use Doctrine\Common\Proxy\Autoloader as DoctrineProxyAutoloader;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Mapping\Driver\YamlDriver;
use Gedmo\Loggable\LoggableListener;
use Gedmo\Translatable\TranslatableListener;
use Monolog\Formatter\LineFormatter;
use Monolog\Handler\ChromePHPHandler;
use Monolog\Handler\FirePHPHandler;
use Monolog\Handler\StreamHandler;
use Monolog\Logger;
use Monolog\Processor\PsrLogMessageProcessor;
use MyCLabs\Work\Dispatcher\RabbitMQWorkDispatcher;
use MyCLabs\Work\Dispatcher\SimpleWorkDispatcher;
use MyCLabs\Work\Dispatcher\WorkDispatcher;
use MyCLabs\Work\TaskExecutor\ServiceCallExecutor;
use MyCLabs\Work\Worker\RabbitMQWorker;
use MyCLabs\Work\Worker\SimpleWorker;
use MyCLabs\Work\Worker\Worker;
use PhpAmqpLib\Channel\AMQPChannel;
use PhpAmqpLib\Connection\AMQPConnection;
use Psr\Log\LoggerInterface;

/**
 * Classe de bootstrap : initialisation de l'application.
 *
 * @author matthieu.napoli
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
            $resources = [
                'Autoloader',
                'UTF8',
                'Container',
                'Translations',
                'Log',
                'ErrorHandler',
                // Il faut initialiser le front controller pour que l'ajout de dossiers
                // de controleurs soit pris en compte
                'FrontController',
                'Doctrine',
                'DefaultEntityManager',
                'Work',
            ];
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

        // Cache de prod
        if ($diConfig && (bool) $diConfig->get('cache', false)) {
            $cache = new MemcachedCache();
            $memcached = new Memcached();
            $memcached->addServer('localhost', 11211);
            $cache->setMemcached($memcached);
        } else {
            // Cache de dev très simple
            $cache = new ArrayCache();
        }
        $cache->setNamespace($configuration->get('applicationName', ''));
        $builder->setDefinitionCache($cache);

        $this->container = $builder->build();

        $this->container->set(Cache::class, $cache);

        Zend_Registry::set('configuration', $configuration);
        Zend_Registry::set('applicationName', $configuration->get('applicationName', ''));
        Zend_Registry::set('container', $this->container);

        // Copie des éléments de configuration dans le container
        $this->container->set('application.name', $configuration->get('applicationName', ''));
        $this->container->set('email.contact.address', $configuration->emails->contact->adress);
        $this->container->set('email.noreply.name', $configuration->emails->noreply->name);
        $this->container->set('email.noreply.address', $configuration->emails->noreply->adress);
        $this->container->set('feature.register', $configuration->feature->register);

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
        // Log l'email de l'utilisateur
        $logger->pushProcessor(new UserInfoProcessor());

        $this->container->set(LoggerInterface::class, $logger);

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
            $errorHandler = $this->container->get(ErrorHandler::class);
            // Fonctions de gestion des erreurs
            set_error_handler([$errorHandler, 'myErrorHandler']);
            set_exception_handler([$errorHandler, 'myExceptionHandler']);
            register_shutdown_function([$errorHandler, 'myShutdownFunction']);
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

        $cache = $this->container->get(Cache::class);

        /** @see AbstractProxyFactory */
        $doctrineAutoGenerateProxy = (int) $configuration->doctrine->proxies->mode;

        // Choix du driver utilisé par le schema.
        //  Utilisation d'un driver YAML.
        //  Les fichiers de mapping porteront l'extension '.yml'.
        $doctrineYAMLDriver = new YamlDriver([APPLICATION_PATH . '/models/mappers'], '.yml');

        // Annotations pour les extensions Doctrine
        $driverChain = new \Doctrine\ORM\Mapping\Driver\DriverChain();
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
        $doctrineConfig->setAutoGenerateProxyClasses($doctrineAutoGenerateProxy);
        $doctrineConfig->setProxyDir(PACKAGE_PATH . '/data/proxies');
        if ($doctrineAutoGenerateProxy !== AbstractProxyFactory::AUTOGENERATE_EVAL) {
            DoctrineProxyAutoloader::register($doctrineConfig->getProxyDir(), $doctrineConfig->getProxyNamespace());
        }

        // Log des requêtes
        if ($configuration->log->queries) {
            $doctrineConfig->setSQLLogger($this->container->get(QueryLogger::class));
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
        Zend_Registry::set('EntityManagers', ['default' => $entityManager]);
        $this->container->set(EntityManager::class, $entityManager);
    }

    /**
     * Plugin qui configure l'extension Doctrine Loggable
     */
    protected function _initLoggableExtension()
    {
        $front = Zend_Controller_Front::getInstance();
        $front->registerPlugin($this->container->get(Inventory_Plugin_LoggableExtensionConfigurator::class));
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

        $connectionArray = [
            'driver'        => $connectionSettings->driver,
            'user'          => $connectionSettings->user,
            'password'      => $connectionSettings->password,
            'dbname'        => $connectionSettings->dbname,
            'host'          => $connectionSettings->host,
            'port'          => $connectionSettings->port,
            'driverOptions' => [
                1002 => 'SET NAMES utf8'
            ],
        ];

        /* @var $doctrineConfig Doctrine\ORM\Configuration */
        $doctrineConfig = Zend_Registry::get('doctrineConfiguration');

        // Création de l'EntityManager depuis la configuration de doctrine.
        $em = Core_ORM_EntityManager::create($connectionArray, $doctrineConfig);

        // Extension de traduction de champs
        $translatableListener = new TranslatableListener();
        $translatableListener->setTranslatableLocale(Core_Locale::loadDefault()->getLanguage());
        $translatableListener->setDefaultLocale(Zend_Registry::get('configuration')->translation->defaultLocale);
        $translatableListener->setPersistDefaultLocaleTranslation(true);
        $translatableListener->setTranslationFallback(true);
        $em->getEventManager()->addEventSubscriber($translatableListener);
        Zend_Registry::set('doctrineTranslate', $translatableListener);
        $this->container->set(TranslatableListener::class, $translatableListener);

        // Extension de versionnement de champs
        $loggableListener = new LoggableListener();
        $em->getEventManager()->addEventSubscriber($loggableListener);
        $this->container->set(LoggableListener::class, $loggableListener);

        return $em;
    }

    /**
     * Work dispatcher
     */
    protected function _initWork()
    {
        // Détermine si on utilise RabbitMQ
        $useRabbitMQ = false;
        $configuration = Zend_Registry::get('configuration');
        if (isset($configuration->rabbitmq) && isset($configuration->rabbitmq->enabled)) {
            $useRabbitMQ = (bool) $configuration->rabbitmq->enabled;
            if ($useRabbitMQ) {
                $this->container->set('rabbitmq.host', $configuration->rabbitmq->host);
                $this->container->set('rabbitmq.port', $configuration->rabbitmq->port);
                $this->container->set('rabbitmq.user', $configuration->rabbitmq->user);
                $this->container->set('rabbitmq.password', $configuration->rabbitmq->password);
            }
        }

        // Connexion RabbitMQ
        $this->container->set('rabbitmq.queue', $this->container->get('application.name') . '-work');
        $this->container->set(AMQPChannel::class, function (Container $c) {
            $queue = $c->get('rabbitmq.queue');
            /** @var AMQPConnection $connection */
            $connection = $c->get(AMQPConnection::class);
            $channel = $connection->channel();
            // Queue durable (= sauvegardée sur disque)
            $channel->queue_declare($queue, false, true, false, false);
            return $channel;
        });

        $this->container->set(WorkDispatcher::class, function (Container $c) use ($useRabbitMQ) {
            if ($useRabbitMQ) {
                $channel = $c->get(AMQPChannel::class);
                $workDispatcher = new RabbitMQWorkDispatcher($channel, $c->get('rabbitmq.queue'));
                $workDispatcher->addEventListener($c->get(RabbitMQEventListener::class));
            } else {
                $workDispatcher = new SimpleWorkDispatcher($c->get(Worker::class));
                $workDispatcher->addEventListener($c->get(SimpleEventListener::class));
            }
            return $workDispatcher;
        });

        $this->container->set(Worker::class, function (Container $c) use ($useRabbitMQ) {
            if ($useRabbitMQ) {
                $channel = $c->get(AMQPChannel::class);
                $worker = new RabbitMQWorker($channel, $c->get('rabbitmq.queue'));
                $worker->addEventListener($c->get(RabbitMQEventListener::class));
            } else {
                $worker = $c->get(SimpleWorker::class);
                $worker->addEventListener($c->get(SimpleEventListener::class));
            }

            $worker->registerTaskExecutor(ServiceCallTask::class, new ServiceCallExecutor($c));

            return $worker;
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
        $front->registerPlugin(new FlushPlugin());
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
}
