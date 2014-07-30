<?php

namespace Script\Jobs\Orga;

use Doctrine\ORM\EntityManager;
use Interop\Container\ContainerInterface;
use Orga\Domain\Workspace;

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
        /** @var Workspace $organization */
        foreach (Workspace::loadList() as $organization) {
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

/** @var ContainerInterface $container */
$container = \Core\ContainerSingleton::getContainer();

$rebuildExports = $container->get(RebuildConfiguration::class);
$rebuildExports->run();
