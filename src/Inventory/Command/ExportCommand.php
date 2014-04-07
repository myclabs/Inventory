<?php

namespace Inventory\Command;

use AF\Domain\Category as AFCategory;
use JMS\Serializer\Serializer;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Techno\Domain\Category as TechnoCategory;
use User\Domain\User;

/**
 * Exporte les données.
 *
 * @author matthieu.napoli
 */
class ExportCommand extends Command
{
    /**
     * @var Serializer
     */
    private $serializer;

    public function __construct(Serializer $serializer)
    {
        $this->serializer = $serializer;

        parent::__construct();
    }

    protected function configure()
    {
        $this->setName('export')
            ->setDescription('Exporte les données');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $root = PACKAGE_PATH . '/data/exports/migration-3.0';

        $output->writeln('<comment>Exporting users</comment>');
        $users = $this->exportUsers();
        file_put_contents($root . '/users.json', $this->serializer->serialize($users, 'json'));

        $output->writeln('<comment>Exporting parameters</comment>');
        $parameters = $this->exportTechno();
        file_put_contents($root . '/parameters.json', $this->serializer->serialize($parameters, 'json'));
    }

    private function exportTechno()
    {
        return TechnoCategory::loadRootCategories();
    }

    private function exportAF()
    {
        return AFCategory::loadRootCategories();
    }

    private function exportUsers()
    {
        return User::loadList();
    }
}
