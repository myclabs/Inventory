<?php
/**
 * Scripts dépilant la work queue
 */

set_time_limit(0);

/**
 * Détermine si l'application est lancée après le Bootstrap
 * @var bool
 */
define('RUN', false);

require_once __DIR__ . '/../../../application/init.php';

/** @var \DI\Container $container */
$container = Zend_Registry::get('container');

/** @var $workDispatcher Core_Work_Dispatcher */
$workDispatcher = $container->get('Core_Work_Dispatcher');

$workDispatcher->work();
