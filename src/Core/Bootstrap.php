<?php

use Core\Autoloader;
use Core\ContainerSingleton;
use Core\Controller\FlushPlugin;
use Core\Log\ErrorHandler;
use Core\Mail\NullTransport;
use DI\Container;
use DI\ContainerBuilder;
use Doctrine\Common\Cache\ArrayCache;
use Doctrine\Common\Cache\Cache;
use Doctrine\Common\Cache\MemcachedCache;
use Gedmo\Loggable\LoggableListener;
use Gedmo\Translatable\TranslatableListener;

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
                'ErrorHandler',
                // Il faut initialiser le front controller pour que l'ajout de dossiers
                // de controleurs soit pris en compte
                'FrontController',
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
        $builder->addDefinitions(APPLICATION_PATH . '/configs/config.php');
        $builder->addDefinitions(APPLICATION_PATH . '/configs/config.log.php');
        $builder->addDefinitions(APPLICATION_PATH . '/configs/config.doctrine.php');
        $builder->addDefinitions(APPLICATION_PATH . '/configs/config.work.php');

        switch (APPLICATION_ENV) {
            case 'testsunitaires':
                $builder->addDefinitions(APPLICATION_PATH . '/configs/config.tests.php');
                break;
            case 'developpement':
                $builder->addDefinitions(APPLICATION_PATH . '/configs/config.dev.php');
                break;
            case 'test':
            case 'production':
                $builder->addDefinitions(APPLICATION_PATH . '/configs/config.prod.php');
                break;
        }

        $builder->addDefinitions(APPLICATION_PATH . '/configs/parameters.php');

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

        // Temporary static access to the container
        ContainerSingleton::setContainer($this->container);

        $this->container->set(Cache::class, $cache);

        Zend_Registry::set('configuration', $configuration);

        // Configuration pour injecter dans les controleurs (intégration ZF1)
        $dispatcher = new \DI\Bridge\ZendFramework1\Dispatcher();
        $dispatcher->setContainer($this->container);
        $frontController = Zend_Controller_Front::getInstance();
        $frontController->setDispatcher($dispatcher);
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
            $connectionArray = [
                'driver'        => $this->container->get('db.driver'),
                'user'          => $this->container->get('db.user'),
                'password'      => $this->container->get('db.password'),
                'dbname'        => $this->container->get('db.name'),
                'host'          => $this->container->get('db.host'),
                'port'          => $this->container->get('db.port'),
                'driverOptions' => [
                    1002 => 'SET NAMES utf8'
                ],
            ];
        } else {
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
        }

        /* @var $doctrineConfig Doctrine\ORM\Configuration */
        $doctrineConfig = Zend_Registry::get('doctrineConfiguration');

        // Création de l'EntityManager depuis la configuration de doctrine.
        $em = Core_ORM_EntityManager::create($connectionArray, $doctrineConfig);

        // Extension de traduction de champs
        $translatableListener = new TranslatableListener();
        $translatableListener->setTranslatableLocale(Core_Locale::loadDefault()->getLanguage());
        $translatableListener->setDefaultLocale($this->container->get('translation.defaultLocale'));
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
     * Session namespace
     */
    protected function _initSessionNamespace()
    {
        $auth = Zend_Auth::getInstance();
        $auth->setStorage(new Zend_Auth_Storage_Session($this->container->get('application.name')));
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
