<?php
/**
 * Scripts dépilant la work queue
 */

use Monolog\Handler\StreamHandler;
use Monolog\Logger;

define('RUN', false);

require_once __DIR__ . '/../../../application/init.php';

/** @var DI\Container $container */
$container = Zend_Registry::get('container');

// Modifie le logger par défaut
$logger = new Logger('log.worker', [new StreamHandler('php://stdout', Logger::DEBUG)]);
$container->set('Psr\Log\LoggerInterface', $logger);

/** @var Core_Work_Dispatcher $workDispatcher */
$workDispatcher = $container->get('Core_Work_Dispatcher');

$workDispatcher->work();
