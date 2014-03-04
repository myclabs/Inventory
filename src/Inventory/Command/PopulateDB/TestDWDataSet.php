<?php

namespace Inventory\Command\PopulateDB;

use Doctrine\ORM\EntityManager;
use Inventory\Command\PopulateDB\TestDWDataSet\PopulateClassif;
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
     * @var BasicDataSet
     */
    private $basicDataSet;

    /**
     * @var EntityManager
     */
    private $entityManager;

    /**
     * @var PopulateClassif
     */
    private $populateClassif;

    /**
     * @var PopulateOrga
     */
    private $populateOrga;

    public function __construct(
        BasicDataSet $basicDataSet,
        EntityManager $entityManager,
        PopulateClassif $populateClassif,
        PopulateOrga $populateOrga
    ) {
        $this->basicDataSet = $basicDataSet;
        $this->entityManager = $entityManager;
        $this->populateClassif = $populateClassif;
        $this->populateOrga = $populateOrga;
    }

    public function run(OutputInterface $output)
    {
        // Charge le jeu de données basique
        $this->basicDataSet->run($output);
        $this->entityManager->clear();

        $output->writeln('<comment>Populating with the testDW data set</comment>');

        $this->populateClassif->run($output);
        $this->populateOrga->run($output);
    }
}
