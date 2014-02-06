<?php

namespace Script\Jobs\ACL;

use Symfony\Component\Console\Application;
use User\Application\Command\RebuildACLCommand;

define('RUN', false);
require_once __DIR__ . '/../../../application/init.php';

/** @var RebuildACLCommand $rebuildACL */
$rebuildACL = \Core\ContainerSingleton::getContainer()->get(RebuildACLCommand::class);

$application = new Application('ACL');
$application->add($rebuildACL);
$application->run();
