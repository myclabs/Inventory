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
//        $data = $this->exportAF();
        $data = $this->exportUsers();

        echo $this->serializer->serialize($data, 'json') . PHP_EOL;
        echo json_encode(json_decode($this->serializer->serialize($data, 'json')), JSON_PRETTY_PRINT);
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
