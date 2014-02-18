<?php

namespace Inventory\Command\PopulateDB;

use Inventory\Command\PopulateDB\BasicDataSet\PopulateUser;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Jeu de données basique pour faire tourner l'application.
 *
 * @Injectable(lazy=true)
 *
 * @author matthieu.napoli
 */
class BasicDataSet
{
    /**
     * @var PopulateUser
     */
    private $populateUser;

    public function __construct(PopulateUser $populateUser)
    {
        $this->populateUser = $populateUser;
    }

    public function run(OutputInterface $output)
    {
        $output->writeln('<comment>Populating with the basic data set</comment>');

        $this->populateUser->run($output);
    }
}
