#!/usr/bin/env php
<?php

use Doctrine\ORM\EntityManager;
use MetaConsole\Application;
use MetaModel\Bridge\Doctrine\EntityManagerBridge;
use MetaModel\Bridge\PHPDI\PHPDIBridge;
use MetaModel\MetaModel;

define('RUN', false);
require_once __DIR__ . '/../application/init.php';

$container = \Core\ContainerSingleton::getContainer();

/** @var EntityManager $entityManager */
$entityManager = $container->get(EntityManager::class);

$metaModel = new MetaModel();
$metaModel->addObjectManager(new EntityManagerBridge($entityManager));
$metaModel->addContainer(new PHPDIBridge($container));

$console = new Application('MetaConsole', null, $metaModel);
$console->run();
