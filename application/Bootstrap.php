<?php

use Core\Autoloader;
use Core\Translation\TmxLoader;
use Doctrine\DBAL\Types\Type;
use Symfony\Component\EventDispatcher\EventDispatcher;
use Symfony\Component\Translation\Translator;
use User\Application\Plugin\ACLPlugin;
use User\Application\ViewHelper\IsAllowedHelper;

/**
 * Application bootstrap
 */
class Bootstrap extends Core_Bootstrap
{
    /**
     * Add and Configure all modules dependencies.
     */
    protected function _initModules()
    {
        $autoloader = Autoloader::getInstance();
        $frontController = Zend_Controller_Front::getInstance();

        $autoloader->addModule('Inventory', APPLICATION_PATH);

        $modules = [
            'Unit',
            'User',
            'TEC',
            'Classif',
            'Techno',
            'Doc',
            'DW',
            'Algo',
            'AF',
            'Social',
            'Orga',
            'Simulation',
            'AuditTrail',
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

        Zend_Registry::set(Core_Locale::registryKey, $locale);

        $translator = new Translator($locale->getId());
        $translator->addLoader('tmx', new TmxLoader());
        $translator->addResource('tmx', APPLICATION_PATH . '/languages', 'fr');
        $translator->addResource('tmx', APPLICATION_PATH . '/languages', 'en');
        $translator->setFallbackLocales(['fr']);
        $this->container->set(Translator::class, $translator);
    }

    /**
     * Enregistre les helpers de vue
     */
    protected function _initViewHelpers()
    {
        $this->bootstrap('View');
        $view = $this->getResource('view');
        $view->addHelperPath(PACKAGE_PATH . '/src/Core/View/Helper', 'Core_View_Helper');
        $view->addHelperPath(PACKAGE_PATH . '/src/UI/View/Helper', 'UI_View_Helper');
        $view->registerHelper($this->container->get(IsAllowedHelper::class, true), 'isAllowed');
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
     * Enregistrement du plugin pour les ACL
     */
    protected function _initPluginAcl()
    {
        $front = Zend_Controller_Front::getInstance();
        // Plugin des Acl
        if ($this->container->get('enable.acl')) {
            $front->registerPlugin($this->container->get(Inventory_Plugin_Acl::class));
        }
    }

    /**
     * Event listeners
     */
    protected function _initEventListeners()
    {
        /** @var EventDispatcher $eventDispatcher */
        $eventDispatcher = $this->container->get(EventDispatcher::class);

        // User events (plus prioritaire)
        $userEventListener = $this->container->get(\User\Domain\Event\EventListener::class, true);
        $eventDispatcher->addListener(Orga_Service_InputCreatedEvent::NAME, [$userEventListener, 'onUserEvent'], 10);
        $eventDispatcher->addListener(Orga_Service_InputEditedEvent::NAME, [$userEventListener, 'onUserEvent'], 10);

        // AuditTrail
        $auditTrailListener = $this->container->get(AuditTrail\Application\Service\EventListener::class, true);
        $eventDispatcher->addListener(Orga_Service_InputCreatedEvent::NAME, [$auditTrailListener, 'onInputCreated']);
        $eventDispatcher->addListener(Orga_Service_InputEditedEvent::NAME, [$auditTrailListener, 'onInputEdited']);
    }
}
