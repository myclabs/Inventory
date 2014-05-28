<?php

namespace Orga\Command;

use Doctrine\ORM\EntityManager;
use Orga_Service_Export;
use Orga_Model_Organization;
use Orga_Model_Granularity;
use Orga_Model_Cell;
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
     * @var bool
     */
    private $rebuildInputStatus = false;


    public function __construct(EntityManager $entityManager)
    {
        $this->entityManager = $entityManager;

        parent::__construct();
    }

    protected function configure()
    {
        $this->setName('orga-cache:rebuild')
            ->setDescription('Regénère les caches d\'orga')
            ->addOption('input-status', null, InputOption::VALUE_NONE, "Update input status of cells");
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $options = $input->getArguments();
        if (empty($options) || $input->getOption('input-status')) {
            $this->rebuildInputStatus = true;
        }

        $this->traverseOrganizations($output);
        $this->entityManager->flush();
    }

    /**
     * @param OutputInterface $output
     */
    protected function traverseOrganizations(OutputInterface $output)
    {
        foreach (Orga_Model_Organization::loadList() as $organization) {
            $this->traverseGranularities($output, $organization);
            $output->writeln('<comment>Rebuilt organization ' . $organization->getId() . '</comment>');
        }
    }

    /**
     * @param OutputInterface $output
     * @param Orga_Model_Organization $organization
     */
    protected function traverseGranularities(OutputInterface $output, Orga_Model_Organization $organization)
    {
        foreach ($organization->getGranularities() as $granularity) {
            $this->traverseCells($output, $granularity);
        }
    }

    /**
     * @param OutputInterface $output
     * @param Orga_Model_Granularity $granularity
     */
    protected function traverseCells(OutputInterface $output, Orga_Model_Granularity $granularity)
    {
        foreach ($granularity->getCells() as $cell) {
            if ($this->rebuildInputStatus) {
                $cell->updateInputStatus();
            }
        }
    }
}
