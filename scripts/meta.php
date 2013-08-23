#!/usr/bin/env php
<?php
use MetaConsole\Application;
use MetaModel\Bridge\Doctrine\EntityManagerBridge;
use MetaModel\Bridge\PHPDI\PHPDIBridge;
use MetaModel\MetaModel;

define('RUN', false);
require_once __DIR__ . '/../application/init.php';

/** @var Doctrine\ORM\EntityManager $entityManager */
$entityManager = Zend_Registry::get('EntityManagers')['default'];
/** @var \DI\Container $container */
$container = Zend_Registry::get('container');

$metaModel = new MetaModel();
$metaModel->addObjectManager(new EntityManagerBridge($entityManager));
$metaModel->addContainer(new PHPDIBridge($container));

$console = new Application('MetaConsole', null, $metaModel);
$console->run();
