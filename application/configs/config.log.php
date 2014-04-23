<?php

use Core\Log\ChromePHPFormatter;
use Core\Log\ExtendedLineFormatter;
use Core\Log\QueryLogger;
use Core\Log\UserInfoProcessor;
use Interop\Container\ContainerInterface;
use Monolog\Formatter\LineFormatter;
use Monolog\Handler\ChromePHPHandler;
use Monolog\Handler\FirePHPHandler;
use Monolog\Handler\StreamHandler;
use Monolog\Logger;
use Monolog\Processor\PsrLogMessageProcessor;
use Psr\Log\LoggerInterface;

return [

    // Fichiers
    'log.file' => 'data/logs/error.log',
    'log.query.file' => 'data/logs/queries.log',

    // Logs actifs
    'log.stdout'  => false,
    'log.tofile'  => true,
    'log.firephp' => false,
    'log.queries' => false,

    // Logger
    LoggerInterface::class => DI\factory(function (ContainerInterface $c) {
        $cli = (PHP_SAPI == 'cli');

        $logger = new Logger('log');

        // Log vers la console (si configuré ou PHP CLI)
        if ($c->get('log.stdout') || $cli) {
            $loggerHandler = new StreamHandler('php://stdout', Logger::DEBUG);
            $loggerHandler->setFormatter(new ExtendedLineFormatter());
            $logger->pushHandler($loggerHandler);
        }
        // Log dans un fichier
        if ($c->get('log.tofile') && !$cli) {
            $fileHandler = new StreamHandler(PACKAGE_PATH . '/' . $c->get('log.file'), Logger::DEBUG);
            $fileHandler->setFormatter(new ExtendedLineFormatter());
            $logger->pushHandler($fileHandler);
        }
        // Log FirePHP
        if ($c->get('log.firephp') && !$cli) {
            ini_set('html_errors', false);
            $logger->pushHandler(new FirePHPHandler());
            $chromePHPHandler = new ChromePHPHandler();
            $chromePHPHandler->setFormatter(new ChromePHPFormatter());
            $logger->pushHandler($chromePHPHandler);
        }

        /** @noinspection PhpParamsInspection */
        $logger->pushProcessor(new PsrLogMessageProcessor());
        // Log l'email de l'utilisateur
        /** @noinspection PhpParamsInspection */
        $logger->pushProcessor(new UserInfoProcessor());

        return $logger;
    }),

    // Log des requetes
    'log.query.logger' => DI\factory(function (ContainerInterface $c) {
        if (! $c->get('log.queries')) {
            return null;
        }
        $queryLoggerHandler = new StreamHandler(PACKAGE_PATH . '/' . $c->get('log.query.file'), Logger::DEBUG);
        $queryLoggerHandler->setFormatter(new LineFormatter("%message%" . PHP_EOL));
        return new Logger('log.query', [$queryLoggerHandler]);
    }),
    QueryLogger::class => DI\object()
        ->constructor(DI\link('log.query.logger')),

];
