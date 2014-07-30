<?php

namespace Orga\Command;

use Doctrine\ORM\EntityManager;
use Orga\Domain\Workspace;
use Orga\Domain\Granularity;
use Orga\Domain\Service\Cell\Input\CellInputUpdaterInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Commande re-génerant les cache d'orga.
 *
 * @author valentin.claras
 * @author matthieu.napoli
 */
class UpdateCacheCommand extends Command
{
    /**
     * @var EntityManager
     */
    private $entityManager;
    /**
     * @var \Orga\Domain\Service\Cell\Input\CellInputUpdaterInterface
     */
    private $inputService;

    /**
     * @var bool
     */
    private $rebuildInputStatus = false;

    /**
     * @var bool
     */
    private $rebuildInputInconsistencies = false;


    public function __construct(EntityManager $entityManager, CellInputUpdaterInterface $inputService)
    {
        $this->entityManager = $entityManager;
        $this->inputService = $inputService;

        parent::__construct();
    }

    protected function configure()
    {
        $this->setName('orga-cache:rebuild')
            ->setDescription('Regénère les caches d\'orga')
            ->addOption('input-status', null, InputOption::VALUE_NONE, "Update input status of cells")
            ->addOption('input-inconsistencies', null, InputOption::VALUE_NONE, "Update input inconsistencies of InputSet");
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        if ($input->getOption('input-status')) {
            $this->rebuildInputStatus = true;
        }
        if ($input->getOption('input-inconsistencies')) {
            $this->rebuildInputInconsistencies = true;
        }

        $this->traverseWorkspaces($output);
        $this->entityManager->flush();
    }

    /**
     * @param OutputInterface $output
     */
    protected function traverseWorkspaces(OutputInterface $output)
    {
        foreach (Workspace::loadList() as $workspace) {
            $this->traverseGranularities($output, $workspace);
            $output->writeln('<comment>Rebuilt workspace ' . $workspace->getId() . '</comment>');
        }
    }

    /**
     * @param OutputInterface $output
     * @param \Orga\Domain\Workspace $workspace
     */
    protected function traverseGranularities(OutputInterface $output, Workspace $workspace)
    {
        foreach ($workspace->getGranularities() as $granularity) {
            $this->traverseCells($output, $granularity);
        }
    }

    /**
     * @param OutputInterface $output
     * @param Granularity $granularity
     */
    protected function traverseCells(OutputInterface $output, Granularity $granularity)
    {
        foreach ($granularity->getCells() as $cell) {
            if ($this->rebuildInputStatus) {
                $cell->updateInputStatus();
            }
            if ($this->rebuildInputInconsistencies) {
                $this->inputService->updateInconsistencyForCell($cell);
            }
        }
    }
}
