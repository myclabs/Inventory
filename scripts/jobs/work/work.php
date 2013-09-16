<?php
/**
 * Scripts dépilant la work queue
 */

define('RUN', false);

require_once __DIR__ . '/../../../application/init.php';

/** @var DI\Container $container */
$container = Zend_Registry::get('container');

/** @var Core_Work_Dispatcher $workDispatcher */
$workDispatcher = $container->get('Core_Work_Dispatcher');

$workDispatcher->work();
