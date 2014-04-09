<?php

namespace Inventory\Command;

use Account\Domain\Account;
use AF\Domain\AFLibrary;
use AF\Domain\Algorithm\Numeric\NumericAlgo;
use AF\Domain\Algorithm\Numeric\NumericConstantAlgo;
use AF\Domain\Algorithm\Numeric\NumericExpressionAlgo;
use AF\Domain\Algorithm\Numeric\NumericInputAlgo;
use AF\Domain\Algorithm\Numeric\NumericParameterAlgo;
use Classification\Domain\ContextIndicator;
use Doctrine\ORM\EntityManager;
use Parameter\Domain\Family\FamilyReference;
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
     * @Inject
     * @var EntityManager
     */
    private $entityManager;

    /**
     * @var ParameterLibrary
     */
    private $parameterLibrary;

    protected function configure()
    {
        $this->setName('import')
            ->setDescription('Importe les données')
            ->addArgument('account', InputArgument::REQUIRED, 'ID of the account in which to import the data')
            ->addArgument('parameterLibrary', InputArgument::REQUIRED, 'Label of the parameter library')
            ->addArgument('afLibrary', InputArgument::REQUIRED, 'Label of the AF library');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $root = PACKAGE_PATH . '/data/exports/migration-3.0';

        $fixAlgoIndexation = function (NumericAlgo $algo, array $data) {
            $refContext = $data['refContext'];
            $refIndicator = $data['refIndicator'];
            if ($refContext && $refIndicator) {
                $contextIndicator = ContextIndicator::loadByRef($refContext, $refIndicator);
                $algo->setContextIndicator($contextIndicator);
            }
        };

        $serializer = new Serializer([
            'Techno\Domain\Category' => ['class' => \Parameter\Domain\Category::class],
            'Techno\Domain\Family\Family' => ['class' => \Parameter\Domain\Family\Family::class],
            'Techno\Domain\Family\Cell' => ['class' => \Parameter\Domain\Family\Cell::class],
            'Techno\Domain\Family\Member' => ['class' => \Parameter\Domain\Family\Member::class],
            'Techno\Domain\Family\Dimension' => ['class' => \Parameter\Domain\Family\Dimension::class],
            NumericInputAlgo::class => [
                'properties' => [
                    'refContext' => [ 'exclude' => true ],
                    'refIndicator' => [ 'exclude' => true ],
                ],
                'callbacks' => $fixAlgoIndexation,
            ],
            NumericExpressionAlgo::class => [
                'properties' => [
                    'refContext' => [ 'exclude' => true ],
                    'refIndicator' => [ 'exclude' => true ],
                ],
                'callbacks' => $fixAlgoIndexation,
            ],
            NumericConstantAlgo::class => [
                'properties' => [
                    'refContext' => [ 'exclude' => true ],
                    'refIndicator' => [ 'exclude' => true ],
                ],
                'callbacks' => $fixAlgoIndexation,
            ],
            NumericParameterAlgo::class => [
                'properties' => [
                    'refContext' => [ 'exclude' => true ],
                    'refIndicator' => [ 'exclude' => true ],
                    'familyRef' => [ 'exclude' => true ],
                ],
                'callbacks' => [
                    $fixAlgoIndexation,
                    function (NumericParameterAlgo $algo, array $data) {
                        $familyRef = new FamilyReference($this->parameterLibrary->getId(), $data['familyRef']);
                        $this->setProperty($algo, 'familyReference', $familyRef);
                    },
                ],
            ],
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

        // Create the parameter library
        $label = $input->getArgument('parameterLibrary');
        $output->writeln("<comment>Creating library '$label'</comment>");
        $this->parameterLibrary = new ParameterLibrary($account, $label);
        $this->parameterLibrary->save();

        // Import the parameters
        $output->writeln('<comment>Importing parameters</comment>');
        $objects = $serializer->unserialize(file_get_contents($root . '/parameters.json'));
        foreach ($objects as $object) {
            if ($object instanceof \Parameter\Domain\Category) {
                $this->setProperty($object, 'library', $this->parameterLibrary);
                $output->writeln(sprintf('<info>Imported category: %s</info>', $object->getLabel()));
            }
            if ($object instanceof \Parameter\Domain\Family\Family) {
                $this->setProperty($object, 'library', $this->parameterLibrary);
                $output->writeln(sprintf('<info>Imported family: %s</info>', $object->getLabel()));
            }
            if ($object instanceof \Core_Model_Entity) {
                $object->save();
            }
        }

        // Create the AF library
        $label = $input->getArgument('afLibrary');
        $output->writeln("<comment>Creating library '$label'</comment>");
        $afLibrary = new AFLibrary($account, $label);
        $afLibrary->save();

        // Import the AF
        $output->writeln('<comment>Importing AF</comment>');
        $objects = $serializer->unserialize(file_get_contents($root . '/af.json'));
        foreach ($objects as $object) {
            if ($object instanceof \AF\Domain\Category) {
                $this->setProperty($object, 'library', $afLibrary);
                $output->writeln(sprintf('<info>Imported category: %s</info>', $object->getLabel()));
            }
            if ($object instanceof \AF\Domain\AF) {
                $this->setProperty($object, 'library', $afLibrary);
                $output->writeln(sprintf('<info>Imported AF: %s</info>', $object->getLabel()));
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
