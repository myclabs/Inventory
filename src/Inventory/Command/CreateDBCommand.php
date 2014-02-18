<?php

namespace Inventory\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Création de la BDD.
 *
 * @author matthieu.napoli
 */
class CreateDBCommand extends Command
{
    /**
     * @var UpdateDBCommand
     */
    private $updateCommand;

    private $dbHost;
    private $dbPort;
    private $dbUser;
    private $dbPassword;
    private $dbName;

    public function __construct(UpdateDBCommand $updateCommand, $dbHost, $dbPort, $dbUser, $dbPassword, $dbName)
    {
        $this->updateCommand = $updateCommand;
        $this->dbHost = $dbHost;
        $this->dbPort = $dbPort;
        $this->dbUser = $dbUser;
        $this->dbPassword = $dbPassword;
        $this->dbName = $dbName;

        parent::__construct();
    }

    protected function configure()
    {
        $this->setName('db:create')
            ->setDescription('Crée la BDD');
    }

    public function execute(InputInterface $input, OutputInterface $output)
    {
        $output->writeln(sprintf('<comment>Creating database %s</comment>', $this->dbName));

        // Suppression de l'ancienne base, puis création de la nouvelle.
        // Permet de s'assurer que les anciennes tables ont bien été supprimé.

        $options = sprintf(
            '-h %s -u %s ',
            $this->dbHost,
            $this->dbUser
        );
        if (! empty($this->dbPassword)) {
            $options .= ' -p' . $this->dbPassword;
        }
        if (! empty($this->dbPort)) {
            $options .= ' --port=' . $this->dbPort;
        }

        shell_exec(sprintf(
            'mysql %s -e "DROP DATABASE IF EXISTS %s"',
            $options,
            $this->dbName
        ));

        shell_exec(sprintf(
            'mysql %s -e "CREATE DATABASE %s"',
            $options,
            $this->dbName
        ));

        // Crée les tables
        $this->updateCommand->execute($input, $output);
    }
}
