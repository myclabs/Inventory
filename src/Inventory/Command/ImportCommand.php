<?php

namespace Inventory\Command;

use Doctrine\ORM\EntityManager;
use Serializer\Serializer;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use User\Domain\User;

/**
 * Importe les données.
 *
 * @author matthieu.napoli
 */
class ImportCommand extends Command
{
    /**
     * @var EntityManager
     */
    private $entityManager;

    public function __construct(EntityManager $entityManager)
    {
        $this->entityManager = $entityManager;

        parent::__construct();
    }

    protected function configure()
    {
        $this->setName('import')
            ->setDescription('Importe les données');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $root = PACKAGE_PATH . '/data/exports/migration-3.0';

        $serializer = new Serializer([]);

        $output->writeln('<comment>Importing users</comment>');
        $objects = $serializer->unserialize(file_get_contents($root . '/users.json'));
        foreach ($objects as $user) {
            if ($user instanceof User) {
                $output->writeln(sprintf('<info>Imported user: %s</info>', $user->getName()));
                $user->save();
            }
        }

        $output->writeln('<comment>Importing parameters</comment>');
        $objects = $serializer->unserialize(file_get_contents($root . '/parameters.json'));
        foreach ($objects as $object) {
            if ($object instanceof \Techno\Domain\Category) {
                $output->writeln(sprintf('<info>Imported category: %s</info>', $object->getLabel()));
            }
            if ($object instanceof \Core_Model_Entity) {
                $object->save();
            }
        }

        $output->writeln('<comment>Importing AF</comment>');
        $objects = $serializer->unserialize(file_get_contents($root . '/af.json'));
        foreach ($objects as $object) {
            if ($object instanceof \AF\Domain\Category) {
                $output->writeln(sprintf('<info>Imported category: %s</info>', $object->getLabel()));
            }
            if ($object instanceof \Core_Model_Entity) {
                $object->save();
            }
        }

        $this->entityManager->flush();
    }
}
