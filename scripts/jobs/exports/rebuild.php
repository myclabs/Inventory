<?php

namespace Script\Jobs\Exports;

use Doctrine\ORM\EntityManager;
use Orga_Service_Export;
use Orga_Model_Organization;
use Orga_Model_Granularity;
use Orga_Model_Cell;

define('RUN', false);
require_once __DIR__ . '/../../../application/init.php';

/**
 * Scripts re-générant les parties céllulaires des exports.
 */
class RebuildExports
{
    private $entityManager;
    /**
     * @var Orga_Service_Export
     */
    private $exportService;

    public function __construct(EntityManager $entityManager, Orga_Service_Export $exportService)
    {
        $this->entityManager = $entityManager;
        $this->exportService = $exportService;
    }

    public function run()
    {
        $directoryInputsExports = APPLICATION_PATH . '/../data/exports/inputs/*';
        $gitIgnoreFile = substr($directoryInputsExports, 0, -1) . '.gitignore';

        echo "Starting removal of old CellInputs exports..." . PHP_EOL;
        foreach(glob($directoryInputsExports) as $file) {
            if(is_file($file) && ($file !== $gitIgnoreFile)) {
                unlink($file);
            }
        }
        echo "\t…removal finished!" . PHP_EOL;

        echo "Starting generation of new CellInputs exports..." . PHP_EOL;
        /** @var Orga_Model_Organization $organization */
        foreach (Orga_Model_Organization::loadList() as $organization) {
            foreach ($organization->getInputGranularities() as $inputGranularity) {
                $this->entityManager->clear();
                $inputGranularity = Orga_Model_Granularity::load($inputGranularity->getId());
                foreach ($inputGranularity->getCells() as $inputCell) {
                    $inputCell = Orga_Model_Cell::load($inputCell->getId());
                    $this->exportService->saveCellInput($inputCell);
                }
                unset($inputGranularity);
            }
        }
        echo "\t…permissions changed!" . PHP_EOL;

        echo "Starting change to permissions on file to allow edition by the application..." . PHP_EOL;
        foreach(glob($directoryInputsExports) as $file) {
            if(is_file($file) && ($file !== $gitIgnoreFile)) {
                chmod($file, 0777);
            }
        }
        echo "\t…change finished!" . PHP_EOL;
    }
}

/** @var \DI\Container $container */
$container = \Zend_Registry::get('container');

/** @var RebuildExports $rebuildExports */
$rebuildExports = $container->get(RebuildExports::class);
$rebuildExports->run();
