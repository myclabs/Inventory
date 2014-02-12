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
    /**
     * @var \Doctrine\ORM\EntityManager
     */
    private $entityManager;
    /**
     * @var Orga_Service_Export
     */
    private $exportService;
    /**
     * @var string
     */
    private $directoryInputsExports;
    /**
     * @var string
     */
    private $gitIgnoreFile;

    public function __construct(EntityManager $entityManager, Orga_Service_Export $exportService)
    {
        $this->entityManager = $entityManager;
        $this->exportService = $exportService;
        $this->directoryInputsExports = APPLICATION_PATH . '/../data/exports/inputs/';
        $this->gitIgnoreFile = $this->directoryInputsExports . '.gitignore';
    }

    public function run()
    {
        $options = getopt('cp', ['no-clear', 'no-permissions']);

        if (!isset($options['c']) && !isset($options['no-clear'])) {
            $this->clearExistingFiles();
        }
        $this->generate();
        if (!isset($options['p']) && !isset($options['no-permissions'])) {
            $this->changePermissions();
        }
    }

    protected function clearExistingFiles()
    {
        echo "Starting removal of old CellInputs exports..." . PHP_EOL;
        foreach(glob($this->directoryInputsExports . '*') as $file) {
            if(is_file($file) && ($file !== $this->gitIgnoreFile)) {
                unlink($file);
            }
        }
        echo " …removal finished!" . PHP_EOL;
    }

    protected function generate()
    {
        echo "Starting generation of new CellInputs exports..." . PHP_EOL;
        /** @var Orga_Model_Organization $organization */
        foreach (Orga_Model_Organization::loadList() as $organization) {
            echo "\t|--".$organization->getLabel().PHP_EOL;
            foreach ($organization->getInputGranularities() as $inputGranularity) {
                $this->entityManager->clear();
                $inputGranularity = Orga_Model_Granularity::load($inputGranularity->getId());
                echo "\t\t->".$inputGranularity->getLabel().PHP_EOL;
                foreach ($inputGranularity->getOrderedCells() as $inputCell) {
                    if (!(count(glob($this->directoryInputsExports . $inputCell->getId() . '.*')) >0)) {
                        $inputCell = Orga_Model_Cell::load($inputCell->getId());
                        $this->exportService->saveCellInput($inputCell);
                        if (count(glob($this->directoryInputsExports . $inputCell->getId() . '.*')) >0) {
                            echo "\t\t\t…".$inputCell->getExtendedLabel().PHP_EOL;
                        }
                    }
                }
                echo "\t\t<-".PHP_EOL;
                unset($inputGranularity);
            }
            echo "\t--|".PHP_EOL;
        }
        echo " …change finished!" . PHP_EOL;
    }

    protected function changePermissions()
    {
        echo "Starting change to permissions on file to allow edition by the application..." . PHP_EOL;
        foreach(glob($this->directoryInputsExports . '*') as $file) {
            if(is_file($file) && ($file !== $this->gitIgnoreFile)) {
                chmod($file, 0777);
            }
        }
        echo " …permissions changed!" . PHP_EOL;
    }
}

/** @var \DI\Container $container */
$container = \Zend_Registry::get('container');

/** @var RebuildExports $rebuildExports */
$rebuildExports = $container->get(RebuildExports::class);
$rebuildExports->run();
