<?php
/**
 * Scripts dépilant la work queue
 */

use MyCLabs\Work\Worker\SimpleWorker;
use MyCLabs\Work\Worker\Worker;

define('RUN', false);

require_once __DIR__ . '/../../../application/init.php';

/** @var DI\Container $container */
$container = Zend_Registry::get('container');

/** @var Worker $worker */
$worker = $container->get('MyCLabs\Work\Worker\Worker');

if ($worker instanceof SimpleWorker) {
    /** @var Psr\Log\LoggerInterface $logger */
    $logger = $container->get('Psr\Log\LoggerInterface');
    $logger->info('RabbitMQ not enabled, nothing to do');
}

// Traite une seule tache
$worker->work(1);
