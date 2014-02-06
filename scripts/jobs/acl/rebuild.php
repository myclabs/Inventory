<?php

namespace Script\Jobs\ACL;

use Symfony\Component\Console\Application;
use User\Application\Command\RebuildACLCommand;

define('RUN', false);
require_once __DIR__ . '/../../../application/init.php';

/** @var \DI\Container $container */
$container = \Zend_Registry::get('container');

/** @var RebuildACLCommand $rebuildACL */
$rebuildACL = $container->get(RebuildACLCommand::class);

$application = new Application('ACL');
$application->add($rebuildACL);
$application->run();
