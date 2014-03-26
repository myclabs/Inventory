<?php

namespace Script\Jobs\Orga;

use Doctrine\ORM\EntityManager;
use Orga_Model_Organization;

define('RUN', false);
require_once __DIR__ . '/../../../application/init.php';

/**
 * Scripts re-générant les parties céllulaires des exports.
 */
class RebuildConfiguration
{
    /**
     * @var \Doctrine\ORM\EntityManager
     */
    private $entityManager;

    public function __construct(EntityManager $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    public function run()
    {
        $this->checkLibrary();
    }

    protected function checkLibrary()
    {
        /** @var Orga_Model_Organization $organization */
        foreach (Orga_Model_Organization::loadList() as $organization) {
            foreach ($organization->getGranularities() as $granularities) {
                foreach ($granularities->getCells() as $cell) {
                    $cell->enableDocLibraryForAFInputSetPrimary();
                }
            }
            $organization->save();
            $this->entityManager->flush();
        }
    }

}

/** @var \DI\Container $container */
$container = \Core\ContainerSingleton::getContainer();

/** @var RebuildExports $rebuildExports */
$rebuildExports = $container->get(RebuildConfiguration::class);
$rebuildExports->run();
