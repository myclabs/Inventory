<?php

namespace Inventory\Command\PopulateDB;

use Inventory\Command\PopulateDB\BasicDataSet\PopulateUnit;
use Inventory\Command\PopulateDB\BasicDataSet\PopulateUser;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Jeu de données basique pour faire tourner l'application.
 */
class BasicDataSet
{
    /**
     * @var PopulateUser
     */
    private $populateUser;

    /**
     * @var PopulateUnit
     */
    private $populateUnit;

    public function __construct(PopulateUser $populateUser, PopulateUnit $populateUnit)
    {
        $this->populateUser = $populateUser;
        $this->populateUnit = $populateUnit;
    }

    public function run(OutputInterface $output)
    {
        $output->writeln('<comment>Populating with the basic data set</comment>');

        $this->populateUser->run($output);
        $this->populateUnit->run($output);
    }
}
