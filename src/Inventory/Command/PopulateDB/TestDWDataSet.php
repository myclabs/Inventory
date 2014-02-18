<?php

namespace Inventory\Command\PopulateDB;

use Doctrine\ORM\EntityManager;
use Inventory\Command\PopulateDB\TestDWDataSet\PopulateClassification;
use Inventory\Command\PopulateDB\TestDWDataSet\PopulateOrga;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Jeu de données de test.
 *
 * @Injectable(lazy=true)
 *
 * @author matthieu.napoli
 */
class TestDWDataSet
{
    /**
     * @var BasicDataSet
     */
    private $basicDataSet;

    /**
     * @var EntityManager
     */
    private $entityManager;

    /**
     * @var PopulateClassification
     */
    private $populateClassification;

    /**
     * @var PopulateOrga
     */
    private $populateOrga;

    public function __construct(
        BasicDataSet $basicDataSet,
        EntityManager $entityManager,
        PopulateClassification $populateClassification,
        PopulateOrga $populateOrga
    ) {
        $this->basicDataSet = $basicDataSet;
        $this->entityManager = $entityManager;
        $this->populateClassification = $populateClassification;
        $this->populateOrga = $populateOrga;
    }

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
