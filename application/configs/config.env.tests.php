<?php

use Doctrine\Common\Proxy\AbstractProxyFactory;

return [
    // Logs
    'log.stdout'  => true,
    'log.tofile'  => false,
    'log.firephp' => false,
    'log.queries' => false,

    // Doctrine
    'db.name' => 'inventory_tests',
    'doctrine.proxies.mode' => AbstractProxyFactory::AUTOGENERATE_EVAL,

    // RabbitMQ
    'rabbitmq.enabled'  => false,
];
