<?php

namespace Inventory\Command;

use JMS\Serializer\Serializer;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Techno\Domain\Category;

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
        $data = $this->exportTechno();

        echo $this->serializer->serialize($data, 'json') . PHP_EOL;
    }

    private function exportTechno()
    {
        $data = [];

        foreach (Category::loadRootCategories() as $rootCategory) {
            $data[] = $rootCategory;
        }

        return $data;
    }
}
