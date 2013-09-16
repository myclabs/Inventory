<?php
/**
 * Scripts dépilant la work queue
 */

use Core\Work\Dispatcher\WorkDispatcher;

define('RUN', false);

require_once __DIR__ . '/../../../application/init.php';

/** @var DI\Container $container */
$container = Zend_Registry::get('container');

/** @var WorkDispatcher $workDispatcher */
$workDispatcher = $container->get('Core\Work\Dispatcher\WorkDispatcher');

$workDispatcher->work();
