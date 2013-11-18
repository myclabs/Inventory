<?php
/**
 * Script dépilant la work queue
 */

use MyCLabs\Work\Worker\SimpleWorker;
use MyCLabs\Work\Worker\Worker;
use Psr\Log\LoggerInterface;

define('RUN', false);

require_once __DIR__ . '/../../../application/init.php';

/** @var DI\Container $container */
$container = Zend_Registry::get('container');

/** @var Worker $worker */
$worker = $container->get(Worker::class);

if ($worker instanceof SimpleWorker) {
    /** @var Psr\Log\LoggerInterface $logger */
    $logger = $container->get(LoggerInterface::class);
    $logger->info('RabbitMQ not enabled, nothing to do');
}

// Traite une seule tache
$worker->work(1);
