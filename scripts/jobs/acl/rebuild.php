<?php

use User\Domain\ACL\ACLFilterService;

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

/** @var ACLFilterService $aclFilterService */
$aclFilterService = $container->get(ACLFilterService::class);

echo "Starting ACL filter generation..." . PHP_EOL;

$aclFilterService->generate();

echo "Finished!" . PHP_EOL;
