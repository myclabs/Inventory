<?php

use Doctrine\Common\Proxy\AbstractProxyFactory;

return [
    // Logs
    'log.stdout'  => false,
    'log.tofile'  => true,
    'log.firephp' => true,
    'log.queries' => true,

    // Doctrine
    'doctrine.proxies.mode' => AbstractProxyFactory::AUTOGENERATE_EVAL,

    // RabbitMQ
    'rabbitmq.enabled'  => false,
];
