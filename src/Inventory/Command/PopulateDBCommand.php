<?php

namespace Inventory\Command;

use Inventory\Command\PopulateDB\BasicDataSet;
use Inventory\Command\PopulateDB\TestDataSet;
use Inventory\Command\PopulateDB\TestDWDataSet;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Remplit la BDD avec un jeu de données.
 *
 * @author matthieu.napoli
 */
class PopulateDBCommand extends Command
{
    /**
     * @Inject
     * @var CreateDBCommand
     */
    private $createCommand;

    /**
     * @Inject
     * @var BasicDataSet
     */
    private $basicDataSet;

    /**
     * @Inject
     * @var TestDataSet
     */
    private $testDataSet;

    /**
     * @Inject
     * @var TestDWDataSet
     */
    private $testDWDataSet;

    protected function configure()
    {
        $this->setName('db:populate')
            ->setDescription('Crée et remplit la BDD avec un jeu de données')
            ->addArgument('dataset', InputArgument::OPTIONAL, 'Le jeu de données à charger', 'basic');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        // Recrée la BDD
        $this->createCommand->execute($input, $output);

        $dataset = $input->getArgument('dataset');

        switch ($dataset) {
            case 'basic':
                $this->basicDataSet->run($output);
                break;
            case 'test':
                $this->testDataSet->run($output);
                break;
            case 'testDW':
                $this->testDWDataSet->run($output);
                break;
            default:
                throw new \InvalidArgumentException(sprintf(
                    "<error>Unknown dataset '%s', valid values are %s.</error>",
                    $dataset,
                    implode(', ', ['basic', 'test', 'testDW'])
                ));
        }
    }
}
