<?php
/**
 * Scripts dépilant la work queue
 */

use Core\Log\ExtendedLineFormatter;
use Monolog\Handler\StreamHandler;
use Monolog\Logger;

define('RUN', false);

require_once __DIR__ . '/../../../application/init.php';

/** @var DI\Container $container */
$container = Zend_Registry::get('container');

// Modifie le logger par défaut
$logger = new Logger('log.worker');
$loggerHandler = new StreamHandler('php://stdout', Logger::DEBUG);
$loggerHandler->setFormatter(new ExtendedLineFormatter());
$logger->pushHandler($loggerHandler);
/** @noinspection PhpParamsInspection */
$logger->pushProcessor(new PsrLogMessageProcessor());
$container->set('Psr\Log\LoggerInterface', $logger);

/** @var Core_Work_Dispatcher $workDispatcher */
$workDispatcher = $container->get('Core_Work_Dispatcher');

$workDispatcher->work();
