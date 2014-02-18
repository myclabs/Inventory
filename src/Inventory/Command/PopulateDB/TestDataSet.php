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
 * @Injectable(lazy=true)
 *
 * @author matthieu.napoli
 */
class TestDataSet
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
     * @var PopulateParameter
     */
    private $populateParameter;

    /**
     * @var PopulateAF
     */
    private $populateAF;

    /**
     * @var PopulateOrga
     */
    private $populateOrga;

    public function __construct(
        BasicDataSet $basicDataSet,
        EntityManager $entityManager,
        PopulateClassification $populateClassification,
        PopulateParameter $populateParameter,
        PopulateAF $populateAF,
        PopulateOrga $populateOrga
    ) {
        $this->basicDataSet = $basicDataSet;
        $this->entityManager = $entityManager;
        $this->populateClassification = $populateClassification;
        $this->populateParameter = $populateParameter;
        $this->populateAF = $populateAF;
        $this->populateOrga = $populateOrga;
    }

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
