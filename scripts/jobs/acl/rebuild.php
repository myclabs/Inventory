<?php

use User\Domain\ACL\ACLService;

/**
 * Scripts re-générant le filtre des ACL
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

/** @var ACLService $aclService */
$aclService = $container->get(ACLService::class);

echo "Starting ACL filter generation..." . PHP_EOL;

$aclService->rebuildAuthorizations();

echo "Finished!" . PHP_EOL;
