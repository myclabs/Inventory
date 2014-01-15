<?php

use Doctrine\ORM\EntityManager;
use User\Domain\ACL\ACLService;

define('RUN', false);

require_once __DIR__ . '/../../../application/init.php';

$container = \Core\ContainerSingleton::getContainer();
/** @var EntityManager $em */
$em = $container->get(EntityManager::class);
/** @var ACLService $aclService */
$aclService = $container->get(ACLService::class);

require __DIR__ . '/migrateACL-begin.php';

// Run build update to update DB
echo "Executing build update" . PHP_EOL . PHP_EOL;
$output = [];
$return = 0;
$buildScript = __DIR__ . '/../../build/build.php';
exec("php $buildScript update", $output, $return);
if ($return !== 0) {
    die("Error executing build update" . PHP_EOL . implode(PHP_EOL, $output));
}

require __DIR__ . '/migrateOrga.php';

require __DIR__ . '/migrateACL-finish.php';
