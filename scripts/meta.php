#!/usr/bin/env php
<?php
use MetaConsole\Application;
use MetaModel\Bridge\Doctrine\EntityManagerBridge;
use MetaModel\MetaModel;

define('APPLICATION_ENV', 'script');
define('RUN', false);
require_once __DIR__ . '/../application/init.php';

/** @var $entityManager Doctrine\ORM\EntityManager */
$entityManager = Zend_Registry::get('EntityManagers')['default'];

$metaModel = new MetaModel();
$metaModel->addObjectManager(new EntityManagerBridge($entityManager));

$console = new Application('MetaConsole', null, $metaModel);
$console->run();
