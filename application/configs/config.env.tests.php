<?php

use Doctrine\Common\Proxy\AbstractProxyFactory;
use Monolog\Handler\TestHandler;
use Monolog\Logger;
use MyCLabs\UnitAPI\UnitOperationService;
use MyCLabs\UnitAPI\UnitService;
use Psr\Log\LoggerInterface;
use Unit\Mock\FakeUnitOperationService;
use Unit\Mock\FakeUnitService;

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

    // Units API
    UnitService::class => DI\object(FakeUnitService::class),
    UnitOperationService::class => DI\object(FakeUnitOperationService::class)
        ->constructor(DI\link(UnitService::class)),

];
