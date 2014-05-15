<?php

use Core\Autoloader;
use Core\ContainerSingleton;
use Core\Controller\FlushPlugin;
use Core\Log\ErrorHandler;
use Core\Mail\NullTransport;
use Core\Translation\TmxLoader;
use DI\Container;
use DI\ContainerBuilder;
use Doctrine\Common\Cache\ArrayCache;
use Doctrine\Common\Cache\Cache;
use Doctrine\Common\Cache\MemcachedCache;
use Doctrine\DBAL\Types\Type;
use Mnapoli\Translated\Helper\Zend1\TranslateZend1Helper;
use Mnapoli\Translated\Translator as DoctrineTranslator;
use MyCLabs\MUIH\Collapse;
use MyCLabs\MUIH\GenericTag;
use MyCLabs\MUIH\Icon;
use MyCLabs\MUIH\Tab;
use Symfony\Component\Translation\Translator;
use User\Application\ViewHelper\IsAllowedHelper;
use User\Application\ViewHelper\TutorialHelper;
use User\Application\Plugin\TutorialPlugin;

/**
 * Application bootstrap
 */
class Bootstrap extends Zend_Application_Bootstrap_Bootstrap
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
        Autoloader::getInstance()->addModule('Inventory', APPLICATION_PATH);
    }

    /**
     * UTF-8.
     */
    protected function _initUTF8()
    {
        header('Content-Type: text/html; charset=utf-8');
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

        // Modules
        $builder->addDefinitions(PACKAGE_PATH . '/src/User/Application/config.php');
        $builder->addDefinitions(APPLICATION_PATH . '/orga/config.php');
        $builder->addDefinitions(PACKAGE_PATH . '/src/Account/Application/config.php');

        switch (APPLICATION_ENV) {
            case 'testsunitaires':
                $builder->addDefinitions(APPLICATION_PATH . '/configs/config.env.tests.php');
                break;
            case 'developpement':
                $builder->addDefinitions(APPLICATION_PATH . '/configs/config.env.dev.php');
                break;
            case 'test':
            case 'production':
                $builder->addDefinitions(APPLICATION_PATH . '/configs/config.env.prod.php');
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

    /**
     * Add and Configure all modules dependencies.
     */
    protected function _initModules()
    {
        $autoloader = Autoloader::getInstance();
        $frontController = Zend_Controller_Front::getInstance();

        $modules = [
            'Unit',
            'User',
            'TEC',
            'Classification',
            'Parameter',
            'Doc',
            'DW',
            'Algo',
            'AF',
            'Social',
            'Orga',
            'AuditTrail',
            'Account',
        ];

        foreach ($modules as $module) {
            $moduleRoot = APPLICATION_PATH . '/' . strtolower($module);
            $moduleRoot2 = PACKAGE_PATH . '/src/' . $module;

            if (file_exists($moduleRoot)) {
                // Autoloader
                $autoloader->addModule($module, $moduleRoot);

                // Controllers
                $frontController->addControllerDirectory($moduleRoot . '/controllers', strtolower($module));

                // Bootstrap
                $bootstrapFile = $moduleRoot . '/Bootstrap.php';
                if (file_exists($bootstrapFile)) {
                    require_once $bootstrapFile;
                    $bootstrapName = $module . '_Bootstrap';
                    /** @var $bootstrap Core_Package_Bootstrap */
                    $bootstrap = new $bootstrapName($this->_application);
                    $bootstrap->container = $this->container;
                    $bootstrap->setRun($this->_run);
                    $bootstrap->bootstrap();
                    foreach ($bootstrap->getRun() as $run) {
                        $this->_markRun($run);
                    }
                }
            } elseif (file_exists($moduleRoot2)) {
                if (file_exists($moduleRoot2 . '/Application/Controller')) {
                    // Controllers
                    $frontController->addControllerDirectory(
                        $moduleRoot2 . '/Application/Controller',
                        strtolower($module)
                    );
                }

                // Bootstrap
                $bootstrapFile = $moduleRoot2 . '/Application/Bootstrap.php';
                if (file_exists($bootstrapFile)) {
                    require_once $bootstrapFile;
                    $bootstrapName = $module . '\Application\Bootstrap';
                    /** @var $bootstrap Core_Package_Bootstrap */
                    $bootstrap = new $bootstrapName($this->_application);
                    $bootstrap->container = $this->container;
                    $bootstrap->setRun($this->_run);
                    $bootstrap->bootstrap();
                    foreach ($bootstrap->getRun() as $run) {
                        $this->_markRun($run);
                    }
                }
            }
        }
    }

    /**
     * Locale et traductions
     */
    protected function _initI18n()
    {
        $locale = Core_Locale::loadDefault();
        Core_Locale::$minSignificantFigures = $this->container->get('locale.minSignificantFigures');

        // Fichiers de traduction
        $translator = new Translator($locale->getId());
        $translator->addLoader('tmx', new TmxLoader());
        $translator->addResource('tmx', APPLICATION_PATH . '/languages', 'fr');
        $translator->addResource('tmx', APPLICATION_PATH . '/languages', 'en');
        $translator->setFallbackLocales(['fr']);
        $this->container->set(Translator::class, $translator);

        // Traductions en BDD
        /** @var DoctrineTranslator $doctrineTranslator */
        $doctrineTranslator = $this->container->get(DoctrineTranslator::class);
        $doctrineTranslator->setCurrentLocale($locale->getLanguage());
    }

    /**
     * Enregistre les helpers de vue
     */
    protected function _initViewHelpers()
    {
        $this->bootstrap('View');
        /** @var Zend_View $view */
        $view = $this->getResource('view');
        $view->addHelperPath(PACKAGE_PATH . '/src/Core/View/Helper', 'Core_View_Helper');
        $view->addHelperPath(PACKAGE_PATH . '/src/UI/View/Helper', 'UI_View_Helper');
        $view->addHelperPath(PACKAGE_PATH . '/vendor/myclabs/muih/src/MyCLabs/MUIH/Bridge/ZendViewHelper/Zend1',
            'MyCLabs\MUIH\Bridge\ZendViewHelper\Zend1');
        $view->registerHelper($this->container->get(IsAllowedHelper::class), 'isAllowed');
        $view->registerHelper($this->container->get(TutorialHelper::class), 'tutorial');
        $view->registerHelper($this->container->get(TranslateZend1Helper::class), 'translate');
    }

    /**
     * Enregistre les helpers de vue
     */
    protected function _initMUIH()
    {
        Icon::$defaultIconPrefix = Icon::FONT_AWESOME;
        Tab::$defaultAjaxTabLoadingText = new GenericTag('p', __('UI', 'loading', 'loading'));
        Tab::$defaultAjaxTabLoadingText->prependContent(' ');
        Tab::$defaultAjaxTabLoadingText->prependContent(new Icon('spinner fa-spin'));
        Collapse::$defaultOpenedIndicator = new Icon('chevron-down');
        Collapse::$defaultClosedIndicator = new Icon('chevron-right');
    }

    /**
     * Initialise le mapping des types en BDD
     */
    protected function _initCalcTypeMapping()
    {
        Type::addType(Calc_TypeMapping_Value::TYPE_NAME, Calc_TypeMapping_Value::class);
        Type::addType(Calc_TypeMapping_UnitValue::TYPE_NAME, Calc_TypeMapping_UnitValue::class);
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
     * Enregistrement du plugin pour les ACL
     */
    protected function _initPluginAcl()
    {
        if ($this->container->get('enable.acl')) {
            $front = Zend_Controller_Front::getInstance();
            $front->registerPlugin($this->container->get(Inventory_Plugin_ACL::class));
        }
    }

    /**
     * Enregistrement du plugin pour le tutorial
     */
    protected function _initPluginTutorial()
    {
        $front = Zend_Controller_Front::getInstance();
        $front->registerPlugin($this->container->get(TutorialPlugin::class));
    }

    protected function _initCheckApplicationUrl()
    {
        if ($this->container->get('application.url') == '') {
            throw new RuntimeException("Il est nécessaire de définir 'application.url' dans parameters.php");
        }
    }

    /**
     * Plugin qui gère le menu
     */
    protected function _initMenuPlugin()
    {
        $front = Zend_Controller_Front::getInstance();
        $front->registerPlugin($this->container->get(Inventory_Plugin_MenuPlugin::class));
    }
}
