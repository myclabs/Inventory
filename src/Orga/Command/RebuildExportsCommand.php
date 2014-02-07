<?php

namespace Orga\Command;

use Doctrine\ORM\EntityManager;
use Orga_Service_Export;
use Orga_Model_Organization;
use Orga_Model_Granularity;
use Orga_Model_Cell;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Commande re-génerant les parties cellulaires des exports.
 *
 * @author valentin.claras
 * @author matthieu.napoli
 */
class RebuildExportsCommand extends Command
{
    /**
     * @var EntityManager
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

        // TODO devrait être dans la configuration...
        $this->directoryInputsExports = APPLICATION_PATH . '/../data/exports/inputs/';
        // TODO devrait ignorer tous les dotfiles plutot que ce cas particulier
        $this->gitIgnoreFile = $this->directoryInputsExports . '.gitignore';

        parent::__construct();
    }

    protected function configure()
    {
        $this->setName('export:rebuild')
            ->setDescription('Regénère les parties cellulaires des exports');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->clearExistingFiles($output);
        $this->generate($output);
        $this->changePermissions($output);
    }

    private function clearExistingFiles(OutputInterface $output)
    {
        $output->writeln('<comment>Clearing old exports</comment>');

        foreach (glob($this->directoryInputsExports . '*') as $file) {
            if (is_file($file) && ($file !== $this->gitIgnoreFile)) {
                unlink($file);
            }
        }
    }

    private function generate(OutputInterface $output)
    {
        $output->writeln('<comment>Generating the exports</comment>');

        /** @var Orga_Model_Organization $organization */
        foreach (Orga_Model_Organization::loadList() as $organization) {
            $output->writeln(sprintf('  <info>%s</info>', $organization->getLabel()));

            foreach ($organization->getInputGranularities() as $inputGranularity) {
                $this->entityManager->clear();
                $inputGranularity = Orga_Model_Granularity::load($inputGranularity->getId());

                $output->writeln(sprintf('    <info>%s</info>', $inputGranularity->getLabel()));

                foreach ($inputGranularity->getOrderedCells() as $inputCell) {
                    if (!(count(glob($this->directoryInputsExports . $inputCell->getId() . '.*')) >0)) {
                        $inputCell = Orga_Model_Cell::load($inputCell->getId());
                        $this->exportService->saveCellInput($inputCell);
                        if (count(glob($this->directoryInputsExports . $inputCell->getId() . '.*')) >0) {
                            $output->writeln(sprintf('      <info>%s</info>', $inputCell->getExtendedLabel()));
                        }
                    }
                }

                unset($inputGranularity);
            }
        }
    }

    private function changePermissions(OutputInterface $output)
    {
        $output->writeln('<comment>Updating the file permissions</comment>');

        foreach (glob($this->directoryInputsExports . '*') as $file) {
            if (is_file($file) && ($file !== $this->gitIgnoreFile)) {
                chmod($file, 0777);
            }
        }
    }
}
