<?php
/**
 * Script dépilant la work queue
 */

use MyCLabs\Work\Worker\SimpleWorker;
use MyCLabs\Work\Worker\Worker;
use Psr\Log\LoggerInterface;

define('RUN', false);

require_once __DIR__ . '/../../../application/init.php';

/** @var Worker $worker */
$worker = \Core\ContainerSingleton::getContainer()->get(Worker::class);

if ($worker instanceof SimpleWorker) {
    /** @var Psr\Log\LoggerInterface $logger */
    $logger = $container->get(LoggerInterface::class);
    $logger->info('RabbitMQ not enabled, nothing to do');
}

// Traite une seule tache
$worker->work(1);

// Attend 1 seconde à cause d'un bug dans Supervisor qui fait que si le programme quitte trop vite
// supervisor croit qu'il est en échec : https://github.com/Supervisor/supervisor/issues/212
sleep(1);
