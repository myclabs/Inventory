<?php

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
        $autoloader = Core_Autoloader::getInstance();
        $frontController = Zend_Controller_Front::getInstance();
        /* @var $doctrineConfig Doctrine\ORM\Configuration */
        $doctrineConfig = Zend_Registry::get('doctrineConfiguration');
        /** @var DriverChain $driver */
        $driver = $doctrineConfig->getMetadataDriverImpl();

        $autoloader->addModule('Default', APPLICATION_PATH);

        $modules = [
            'Unit',
            'User',
        ];

        foreach ($modules as $module) {
            $moduleRoot = APPLICATION_PATH . '/' . strtolower($module);

            // Autoloader
            $autoloader->addModule($module, $moduleRoot);

            // Controllers
            $frontController->addControllerDirectory($moduleRoot . '/controllers', $module);

            // Bootstrap
            require_once $moduleRoot . '/Bootstrap.php';
            $bootstrapName = $module . '_Bootstrap';
            /** @var $bootstrap Core_Package_Bootstrap */
            $bootstrap = new $bootstrapName($this->_application);
            $bootstrap->setRun($this->_run);
            $bootstrap->bootstrap();
            $this->_run = $bootstrap->getRun();

            // Doctrine Mappers
            $driver->getDefaultDriver()->getLocator()->addPaths([$moduleRoot . '/models/mappers']);
        }
    }

    protected function _initPackage()
    {
        Zend_Registry::set(Core_Translate::registryKey, new Core_Translate());
        Zend_Registry::set(Core_Locale::registryKey, Core_Locale::loadDefault());
    }

    /**
     * Enregistre les helpers de UI.
     */
    protected function _initViewHelperUI()
    {
        $this->bootstrap('View');
        $view = $this->getResource('view');
        $view->addHelperPath(PACKAGE_PATH . '/src/View/Helper', 'UI_View_Helper');
    }

}
