<?php

namespace Inventory\Command;

use Account\Domain\Account;
use Doctrine\ORM\EntityManager;
use Parameter\Domain\ParameterLibrary;
use Serializer\Serializer;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
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
            ->setDescription('Importe les données')
            ->addArgument('account', InputArgument::REQUIRED, 'ID of the account in which to import the data')
            ->addArgument('parameterLibrary', InputArgument::REQUIRED, 'Label of the parameter library');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $root = PACKAGE_PATH . '/data/exports/migration-3.0';

        $serializer = new Serializer([
            'Techno\Domain\Category' => ['class' => \Parameter\Domain\Category::class],
            'Techno\Domain\Family\Family' => ['class' => \Parameter\Domain\Family\Family::class],
            'Techno\Domain\Family\Cell' => ['class' => \Parameter\Domain\Family\Cell::class],
            'Techno\Domain\Family\Member' => ['class' => \Parameter\Domain\Family\Member::class],
            'Techno\Domain\Family\Dimension' => ['class' => \Parameter\Domain\Family\Dimension::class],
        ]);

        /** @var Account $account */
        $account = $this->entityManager->find(Account::class, $input->getArgument('account'));

        $output->writeln('<comment>Importing users</comment>');
        $objects = $serializer->unserialize(file_get_contents($root . '/users.json'));
        foreach ($objects as $user) {
            if ($user instanceof User) {
                $output->writeln(sprintf('<info>Imported user: %s</info>', $user->getName()));
                $user->save();
            }
        }

        // Create the Parameter library
        $label = $input->getArgument('parameterLibrary');
        $output->writeln("<comment>Creating library '$label'</comment>");
        $parameterLibrary = new ParameterLibrary($account, $label);
        $parameterLibrary->save();

        $output->writeln('<comment>Importing parameters</comment>');
        $objects = $serializer->unserialize(file_get_contents($root . '/parameters.json'));
        foreach ($objects as $object) {
            if ($object instanceof \Parameter\Domain\Category) {
                $this->setProperty($object, 'library', $parameterLibrary);
                $output->writeln(sprintf('<info>Imported category: %s</info>', $object->getLabel()));
            }
            if ($object instanceof \Parameter\Domain\Family\Family) {
                $this->setProperty($object, 'library', $parameterLibrary);
                $output->writeln(sprintf('<info>Imported family: %s</info>', $object->getLabel()));
            }
            if ($object instanceof \Core_Model_Entity) {
                $object->save();
            }
        }

        $this->entityManager->flush();
        return;

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

    private function setProperty($object, $property, $value)
    {
        $refl = new \ReflectionProperty($object, $property);
        $refl->setAccessible(true);
        $refl->setValue($object, $value);
    }
}
