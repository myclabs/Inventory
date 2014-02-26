<?php

namespace Inventory\Command\PopulateDB;

use Doctrine\ORM\EntityManager;
use Inventory\Command\PopulateDB\TestDWDataSet\PopulateClassification;
use Inventory\Command\PopulateDB\TestDWDataSet\PopulateOrga;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Jeu de données de test.
 *
 * @author matthieu.napoli
 */
class TestDWDataSet
{
    /**
     * @Inject
     * @var BasicDataSet
     */
    private $basicDataSet;

    /**
     * @Inject
     * @var EntityManager
     */
    private $entityManager;

    /**
     * @Inject
     * @var PopulateClassification
     */
    private $populateClassification;

    /**
     * @Inject
     * @var PopulateOrga
     */
    private $populateOrga;

    public function run(OutputInterface $output)
    {
        // Charge le jeu de données basique
        $this->basicDataSet->run($output);
        $this->entityManager->clear();

        $output->writeln('<comment>Populating with the testDW data set</comment>');

        $this->populateClassification->run($output);
        $this->populateOrga->run($output);
    }
}
