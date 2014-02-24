<?php

namespace Inventory\Command;

use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Tools\SchemaTool;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Mise à jour de la BDD.
 *
 * @author matthieu.napoli
 */
class UpdateDBCommand extends Command
{
    /**
     * @var EntityManager
     */
    private $entityManager;

    /**
     * @var string
     */
    private $dbName;

    public function __construct(EntityManager $entityManager, $dbName)
    {
        $this->entityManager = $entityManager;
        $this->dbName = $dbName;

        parent::__construct();
    }

    protected function configure()
    {
        $this->setName('db:update')
            ->setDescription('Met à jour la BDD');
    }

    public function execute(InputInterface $input, OutputInterface $output)
    {
        $output->writeln(sprintf('<comment>Updating database %s</comment>', $this->dbName));

        // Utilisation du SchemaTool afin de créer les tables pour l'ensemble du Model.
        $schemaTool = new SchemaTool($this->entityManager);
        $schemaTool->updateSchema($this->entityManager->getMetadataFactory()->getAllMetadata());
    }
}
