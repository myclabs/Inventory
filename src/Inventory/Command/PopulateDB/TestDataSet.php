<?php

namespace Inventory\Command\PopulateDB;

use Doctrine\ORM\EntityManager;
use Inventory\Command\PopulateDB\TestDataSet\PopulateAF;
use Inventory\Command\PopulateDB\TestDataSet\PopulateClassification;
use Inventory\Command\PopulateDB\TestDataSet\PopulateOrga;
use Inventory\Command\PopulateDB\TestDataSet\PopulateParameter;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Jeu de données de test.
 *
 * @author matthieu.napoli
 */
class TestDataSet
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
     * @var PopulateParameter
     */
    private $populateParameter;

    /**
     * @Inject
     * @var PopulateAF
     */
    private $populateAF;

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

        $output->writeln('<comment>Populating with the test data set</comment>');

        $this->populateClassification->run($output);
        $this->populateParameter->run($output);
        $this->populateAF->run($output);
        $this->populateOrga->run($output);
    }
}
