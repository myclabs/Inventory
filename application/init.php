<?php
/**
 * Initialisation de l'application
 */

error_reporting(E_ALL);

// Répertoire vers l'application
defined('APPLICATION_PATH') || define('APPLICATION_PATH', dirname(__FILE__));

// Répertoire vers le package
defined('PACKAGE_PATH') || define('PACKAGE_PATH', realpath(APPLICATION_PATH . '/..'));

// Environnement
if (file_exists(APPLICATION_PATH . '/configs/env.php')) {
    require_once APPLICATION_PATH . '/configs/env.php';
}

// Vérifie que l'environnement d'exécution est définit
if (! defined('APPLICATION_ENV')) {
    die("No application environment defined. Use the application/configs/env.php file.");
}

require_once PACKAGE_PATH . '/vendor/autoload.php';

// Config
$config = new Zend_Config_Ini(APPLICATION_PATH . '/configs/application.ini', APPLICATION_ENV);
$configShared = new Zend_Config_Ini(APPLICATION_PATH . '/configs/shared.ini', APPLICATION_ENV, true);
$configShared->merge($config);
$configShared->setReadOnly();

// Crée l'application
$application = new Zend_Application(APPLICATION_ENV, $configShared);

// Lance le bootstrap, puis l'application
$application->bootstrap();
if (defined('RUN') && RUN) {
    $application->run();
}
