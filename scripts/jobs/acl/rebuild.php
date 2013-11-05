<?php

namespace Script\Jobs\ACL;

use Doctrine\ORM\EntityManager;
use User\Domain\ACL\ACLService;

define('RUN', false);
require_once __DIR__ . '/../../../application/init.php';

/**
 * Scripts re-générant le filtre des ACL
 */
class RebuildACL
{
    private $entityManager;
    private $aclService;

    public function __construct(EntityManager $entityManager, ACLService $aclService)
    {
        $this->entityManager = $entityManager;
        $this->aclService = $aclService;
    }

    public function run()
    {
        echo "Starting ACL filter generation..." . PHP_EOL;

        $this->aclService->rebuildAuthorizations();

        $this->entityManager->flush();

        echo "Finished!" . PHP_EOL;
    }
}

/** @var \DI\Container $container */
$container = \Zend_Registry::get('container');

/** @var RebuildACL $rebuildACL */
$rebuildACL = $container->get(RebuildACL::class);
$rebuildACL->run();
