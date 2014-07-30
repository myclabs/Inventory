<?php

namespace Orga\Command;

use Core\ContainerSingleton;
use Doctrine\ORM\EntityManager;
use Orga\Domain\Service\Export;
use Orga\Domain\Workspace;
use Orga\Domain\Granularity;
use Orga\Domain\Cell;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
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
     * @var \Orga\Domain\Service\Export
     */
    private $exportService;
    /**
     * @var string
     */
    private $directoryInputsExports;

    /**
     * @param EntityManager $entityManager
     * @param Export $exportService
     */
    public function __construct(EntityManager $entityManager, Export $exportService)
    {
        $this->entityManager = $entityManager;
        $this->exportService = $exportService;

        $this->directoryInputsExports = ContainerSingleton::getContainer()->get('exports.inputs.path');

        parent::__construct();
    }

    protected function configure()
    {
        $this->setName('export:rebuild')
            ->setDescription('Regénère les parties cellulaires des exports')
            ->addOption('no-clear', null, InputOption::VALUE_NONE, "Don't clear the existing files");
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        if (! $input->getOption('no-clear')) {
            $this->clearExistingFiles($output);
        }
        $this->generate($output);
        $this->changePermissions($output);
    }

    private function clearExistingFiles(OutputInterface $output)
    {
        $output->writeln('<comment>Clearing old exports</comment>');

        foreach (glob($this->directoryInputsExports . '*') as $file) {
            if (is_file($file) && (substr($file, 0, 1) != '.')) {
                unlink($file);
            }
        }
    }

    private function generate(OutputInterface $output)
    {
        $output->writeln('<comment>Generating the exports</comment>');

        /** @var \Orga\Domain\Workspace $workspace */
        foreach (Workspace::loadList() as $workspace) {
            $output->writeln(sprintf('  <info>%s</info>', $workspace->getLabel()->get('fr')));

            foreach ($workspace->getInputGranularities() as $inputGranularity) {
                $this->entityManager->clear();
                $inputGranularity = Granularity::load($inputGranularity->getId());

                $output->writeln(sprintf('    <info>%s</info>', $inputGranularity->getLabel()->get('fr')));

                foreach ($inputGranularity->getOrderedCells() as $inputCell) {
                    if (!(count(glob($this->directoryInputsExports . $inputCell->getId() . '.*')) >0)) {
                        $inputCell = Cell::load($inputCell->getId());
                        $this->exportService->saveCellInput($inputCell);
                        if (count(glob($this->directoryInputsExports . $inputCell->getId() . '.*')) >0) {
                            $output->writeln(sprintf(
                                '      <info>%s</info>',
                                $inputCell->getExtendedLabel()->get('fr')
                            ));
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
            if (is_file($file) && (substr($file, 0, 1) != '.')) {
                chmod($file, 0777);
            }
        }
    }
}
