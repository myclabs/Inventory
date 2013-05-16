<?php

/**
 * Application bootstrap
 */
class Bootstrap extends Core_Bootstrap
{

    protected function _initModules()
    {
        $autoloader = Core_Autoloader::getInstance();
        $autoloader->addModule('Default', APPLICATION_PATH);
        $autoloader->addModule('Unit', APPLICATION_PATH . '/unit');
    }

    protected function _initPackage()
    {
        Zend_Registry::set(Core_Translate::registryKey, new Core_Translate());
        Zend_Registry::set(Core_Locale::registryKey, Core_Locale::loadDefault());
    }

}
