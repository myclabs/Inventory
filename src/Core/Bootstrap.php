<?php
/**
 * @author     matthieu.napoli
 * @package    Core
 * @subpackage Bootstrap
 */

use DI\Container;
use DI\Definition\FileLoader\YamlDefinitionFileLoader;
use Doctrine\ORM\Tools\Setup;

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
                'ErrorLog',
                'ErrorHandler',
                'FrontController',
                'Acl',
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
        require PACKAGE_PATH . '/vendor/autoload.php';
        Core_Autoloader::getInstance()->register();
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
        $this->container = new Container();

        // Auto-register, waiting for https://github.com/mnapoli/PHP-DI/issues/71
        $this->container->set('DI\Container', $this->container);

        // Récupère la configuration
        $configuration = new Zend_Config($this->getOptions());
        Zend_Registry::set('configuration', $configuration);
        Zend_Registry::set('applicationName', $configuration->get('applicationName', ''));
        Zend_Registry::set('container', $this->container);

        $this->container->set('applicationName', $configuration->get('applicationName', ''));

        $this->container->addDefinitionsFromFile(new YamlDefinitionFileLoader(APPLICATION_PATH . '/configs/di.yml'));
    }

    /**
     * Log des erreurs
     */
    protected function _initErrorLog()
    {
        $errorLog = Core_Error_Log::getInstance();
        // Si on est en tests unitaires
        if ((APPLICATION_ENV == 'testsunitaires') || (APPLICATION_ENV == 'script')) {
            // Prend en compte toutes les erreurs
            error_reporting(E_ALL);
            // Log vers la console
            $errorLog->addDestinationLogs(Core_Error_Log::DESTINATION_CONSOLE);
        }
        // Si on est en développement ou test
        if (APPLICATION_ENV == 'developpement') {
            // Prend en compte toutes les erreurs
            error_reporting(E_ALL);
            // Log vers Firebug
            $errorLog->addDestinationLogs(Core_Error_Log::DESTINATION_FIREBUG);
        }
        // Log dans un fichier
        $errorLog->addDestinationLogs(Core_Error_Log::DESTINATION_FILE);
    }

    /**
     * Gestion des erreurs.
     */
    protected function _initErrorHandler()
    {
        if ((APPLICATION_ENV == 'developpement')
         || (APPLICATION_ENV == 'script')
         || (APPLICATION_ENV == 'test')
         || (APPLICATION_ENV == 'production')
        ) {
            // Fonctions de gestion des erreurs
            set_error_handler(array('Core_Error_Handler','myErrorHandler'));
            set_exception_handler(array('Core_Error_Handler','myExceptionHandler'));
            register_shutdown_function(array('Core_Error_Handler','myShutdownFunction'));
        }
    }

    /**
     * Initialise Doctrine pour utiliser l'autoloader de Zend.
     */
    protected function _initDoctrine()
    {
        // Création de la configuration de Doctrine.
        $doctrineConfig = new Doctrine\ORM\Configuration();

        // Définition du cache en fonction de l'environement.
        switch (APPLICATION_ENV) {
            case 'production':
                //@todo Voir avec Benjamin quel cache on utilise.
//                 $doctrineCache = new Doctrine\Common\Cache\ApcCache();
//                 $doctrineCache = new Doctrine\Common\Cache\MemcachedCache();
                $doctrineCache = new Doctrine\Common\Cache\ArrayCache();
//                $doctrineAutoGenerateProxy = false;
                // Temporaire refs #5641
                $doctrineAutoGenerateProxy = true;
                break;
            default:
                $doctrineCache = new Doctrine\Common\Cache\ArrayCache();
                $doctrineAutoGenerateProxy = true;
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
            $doctrineCache
        );
        Gedmo\DoctrineExtensions::registerMappingIntoDriverChainORM(
            $driverChain, // our metadata driver chain, to hook into
            $cachedAnnotationReader // our cached annotation reader
        );
        Zend_Registry::set('annotationReader', $cachedAnnotationReader);

        $doctrineConfig->setMetadataDriverImpl($driverChain);

        // Configuration de Doctrine pour utiliser le cache
        //  pour la création des requêtes, des résults, et du parsing des Métadata.
        $doctrineConfig->setQueryCacheImpl($doctrineCache);
        $doctrineConfig->setResultCacheImpl($doctrineCache);
        $doctrineConfig->setMetadataCacheImpl($doctrineCache);
        // Configuration des Proxies.
        $doctrineConfig->setProxyDir(PACKAGE_PATH . '/data/proxies');
        $doctrineConfig->setProxyNamespace('Doctrine_Proxies');
        $doctrineConfig->setAutoGenerateProxyClasses($doctrineAutoGenerateProxy);

        // Configuration de l'autoloader spécial pour les Proxy
        // @see http://www.doctrine-project.org/jira/browse/DDC-1698
        Doctrine\ORM\Proxy\Autoloader::register($doctrineConfig->getProxyDir(), $doctrineConfig->getProxyNamespace());

        // Définition du sql profiler en fonction de l'environement.
        switch (APPLICATION_ENV) {
            case 'test':
            case 'developpement':
                // Requêtes transmises à Firebug.
                $profiler = new ZendX\Doctrine2\FirebugProfiler();
                break;
            case 'testsunitaires':
                // Requêtes placées dans un fichier.
                $profiler = new Core_Profiler_File();
                break;
            default:
                $profiler = null;
            break;
        }
        $doctrineConfig->setSQLLogger($profiler);

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
     * Crée l'entity manager utilisé par défaut. Méthode utilise pour recréer un entity manager
     * si celui-ci se ferme à cause d'une exception
     * @param null $connectionSettings
     * @return Core_ORM_EntityManager
     */
    public function createDefaultEntityManager($connectionSettings = null)
    {
        if ($connectionSettings == null) {
            // Récupération de la configuration de la connexion dans l'application.ini
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

        // Configuration des extensions doctrine
        $translatableListener = new Gedmo\Translatable\TranslatableListener();
        $translatableListener->setTranslatableLocale(Core_Locale::loadDefault()->getLanguage());
        $translatableListener->setDefaultLocale('fr');
        $translatableListener->setTranslationFallback(true);
        Zend_Registry::set('doctrineTranslate', $translatableListener);

        $em->getEventManager()->addEventSubscriber($translatableListener);

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

        if ($useGearman) {
            $workDispatcher = new Core_Work_GearmanDispatcher();
        } else {
            $workDispatcher = new Core_Work_SimpleDispatcher();
        }
        Zend_Registry::set('workDispatcher', $workDispatcher);

        // Register workers
        $workDispatcher->registerWorker($this->container->get('Core_Work_ServiceCall_Worker'));
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
        $name = Zend_Registry::get('applicationName');
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
     * Définition du doctype du document.
     */
    protected function _initDocType()
    {
        $this->bootstrap('View');
        $view = $this->getResource('View');
        $view->doctype('HTML5');
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
     * Enregistre les helpers de Core.
     */
    protected function _initViewHelperCore()
    {
        $this->bootstrap('View');
        // Exceptionellement, les helpers de vue de Core sont dans la librairie.
        //  Car Core n'est pas un module.
        $view = $this->getResource('view');
        $view->addHelperPath(PACKAGE_PATH.'/src/View/Helper', 'Core_View_Helper');
    }

    /**
     * Envoi de mail.
     */
    protected function _initMail()
    {
        if ((APPLICATION_ENV == 'testsunitaires') || (APPLICATION_ENV == 'script')) {
            Zend_Mail::setDefaultTransport(new Core_Mail_Transport_Debug());
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
