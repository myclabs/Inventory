<?php

use Doctrine\Common\Proxy\AbstractProxyFactory;
use Monolog\Handler\TestHandler;
use Monolog\Logger;
use Psr\Log\LoggerInterface;

return [
    // Logs
    'log.stdout'  => false,
    'log.tofile'  => false,
    'log.firephp' => false,
    'log.queries' => false,
    LoggerInterface::class => DI\factory(function () {
        $logger = new Logger('log');
        $logger->pushHandler(new TestHandler());
        return $logger;
    }),

    // Doctrine
    'db.name' => 'inventory_tests',
    'doctrine.proxies.mode' => AbstractProxyFactory::AUTOGENERATE_EVAL,

    // RabbitMQ
    'rabbitmq.enabled'  => false,
];
