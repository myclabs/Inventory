<?php

namespace Inventory\Command;

use AF\Domain\Category as AFCategory;
use Serializer\Serializer;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Parameter\Domain\Category as TechnoCategory;
use User\Domain\User;

/**
 * Exporte les données.
 *
 * @author matthieu.napoli
 */
class ExportCommand extends Command
{
    protected function configure()
    {
        $this->setName('export')
            ->setDescription('Exporte les données');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $root = PACKAGE_PATH . '/data/exports/migration-3.0';

        $serializer = new Serializer([
            \DateTime::class => [
                'serialize' => true,
            ],
            User::class => [
                'properties' => [
                    'roles' => [
                        'exclude' => true,
                    ],
                    'authorizations' => [
                        'exclude' => true,
                    ],
                    'acl' => [
                        'exclude' => true,
                    ],
                ],
            ],
        ]);

        $output->writeln('<comment>Exporting users</comment>');
        $data = User::loadList();
        file_put_contents($root . '/users.json', $serializer->serialize($data));

        $output->writeln('<comment>Exporting classification</comment>');
        $data = [
            \Classif_Model_Indicator::loadList(),
            \Classif_Model_Axis::loadList(),
            \Classif_Model_Context::loadList(),
            \Classif_Model_ContextIndicator::loadList(),
        ];
        file_put_contents($root . '/classification.json', $serializer->serialize($data));

        $output->writeln('<comment>Exporting parameters</comment>');
        $data = TechnoCategory::loadRootCategories();
        file_put_contents($root . '/parameters.json', $serializer->serialize($data));

        $output->writeln('<comment>Exporting AF</comment>');
        $data = AFCategory::loadRootCategories();
        file_put_contents($root . '/af.json', $serializer->serialize($data));
    }
}
