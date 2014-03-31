<?php

namespace Inventory\Command\PopulateDB;

use Inventory\Command\PopulateDB\BasicDataSet\PopulateAccount;
use Inventory\Command\PopulateDB\BasicDataSet\PopulateUnit;
use Inventory\Command\PopulateDB\BasicDataSet\PopulateUser;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Jeu de données basique pour faire tourner l'application.
 *
 * @Injectable(lazy=true)
 */
class BasicDataSet
{
    /**
     * @Inject
     * @var PopulateUser
     */
    private $populateUser;

    /**
     * @Inject
     * @var PopulateUnit
     */
    private $populateUnit;

    /**
     * @Inject
     * @var PopulateAccount
     */
    private $populateAccount;

    public function run(OutputInterface $output)
    {
        $output->writeln('<comment>Populating with the basic data set</comment>');

        $this->populateUser->run($output);
        $this->populateUnit->run($output);
        $this->populateAccount->run($output);
    }
}
