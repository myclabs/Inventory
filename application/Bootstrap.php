<?php

use Doctrine\DBAL\Types\Type;

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
        /** @var Doctrine\ORM\Mapping\Driver\DriverChain $driver */
        $driver = $doctrineConfig->getMetadataDriverImpl();

        $autoloader->addModule('Inventory', APPLICATION_PATH);

        $modules = [
            'Unit',
            'User',
            'TEC',
            'Classif',
            'Keyword',
            'Techno',
            'Doc',
            'DW',
            'Algo',
            'AF',
            'Social',
            'Orga',
            'Simulation',
        ];

        foreach ($modules as $module) {
            $moduleRoot = APPLICATION_PATH . '/' . strtolower($module);

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
                $bootstrap->setRun($this->_run);
                $bootstrap->bootstrap();
                foreach ($bootstrap->getRun() as $run) {
                    $this->_markRun($run);
                }
            }

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
        $view->addHelperPath(PACKAGE_PATH . '/src/UI/View/Helper', 'UI_View_Helper');
    }

    /**
     * Initialise le mapping des types en BDD
     */
    protected function _initCalcTypeMapping()
    {
        Type::addType(Calc_TypeMapping_Value::TYPE_NAME, 'Calc_TypeMapping_Value');
        Type::addType(Calc_TypeMapping_UnitValue::TYPE_NAME, 'Calc_TypeMapping_UnitValue');
    }

    /**
     * Enregistrement du plugin pour les ACL
     */
    protected function _initPluginAcl()
    {
        $front = Zend_Controller_Front::getInstance();
        // Plugin des Acl
        if (Zend_Registry::isRegistered('activerAcl') && Zend_Registry::get('activerAcl')) {
            $front->registerPlugin(new Inventory_Plugin_Acl());
            Zend_Registry::set('pluginAcl', 'User_Plugin_Acl');
        }
    }

}
