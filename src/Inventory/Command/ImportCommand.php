<?php

namespace Inventory\Command;

use Account\Domain\Account;
use AF\Domain\AF;
use AF\Domain\AFLibrary;
use AF\Domain\Algorithm\Index\AlgoResultIndex;
use AF\Domain\Algorithm\Index\FixedIndex;
use AF\Domain\Algorithm\Numeric\NumericAlgo;
use AF\Domain\Algorithm\Numeric\NumericConstantAlgo;
use AF\Domain\Algorithm\Numeric\NumericExpressionAlgo;
use AF\Domain\Algorithm\Numeric\NumericInputAlgo;
use AF\Domain\Algorithm\Numeric\NumericParameterAlgo;
use Classification\Domain\Axis;
use Classification\Domain\ClassificationLibrary;
use Classification\Domain\Context;
use Classification\Domain\ContextIndicator;
use Classification\Domain\Indicator;
use Doctrine\ORM\EntityManager;
use Parameter\Domain\Family\Cell;
use Parameter\Domain\Family\Dimension;
use Parameter\Domain\Family\Family;
use Parameter\Domain\Family\FamilyReference;
use Parameter\Domain\Family\Member;
use Parameter\Domain\ParameterLibrary;
use Serializer\Serializer;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Unit\UnitAPI;
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

    protected function configure()
    {
        $this->setName('import')
            ->setDescription('Importe les données')
            ->addArgument('account', InputArgument::REQUIRED, 'ID of the account in which to import the data')
            ->addArgument('classificationLibrary', InputArgument::REQUIRED, 'Label of the classification library')
            ->addArgument('parameterLibrary', InputArgument::REQUIRED, 'Label of the parameter library')
            ->addArgument('afLibrary', InputArgument::REQUIRED, 'Label of the AF library');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $root = PACKAGE_PATH . '/data/exports/migration-3.0';

        /** @var Account $account */
        $account = $this->entityManager->find(Account::class, $input->getArgument('account'));

        $this->entityManager->beginTransaction();

        // Create the classification library
        $label = $input->getArgument('classificationLibrary');
        $output->writeln("<comment>Creating library '$label'</comment>");
        $classificationLibrary = new ClassificationLibrary($account, $label);
        $classificationLibrary->save();

        // Create the parameter library
        $label = $input->getArgument('parameterLibrary');
        $output->writeln("<comment>Creating library '$label'</comment>");
        $parameterLibrary = new ParameterLibrary($account, $label);
        $parameterLibrary->save();

        // Create the AF library
        $label = $input->getArgument('afLibrary');
        $output->writeln("<comment>Creating library '$label'</comment>");
        $afLibrary = new AFLibrary($account, $label);
        $afLibrary->save();


        $fixAlgoIndexation = function (NumericAlgo $algo, array $data) {
            $refContext = $data['refContext'];
            $refIndicator = $data['refIndicator'];
            if ($refContext && $refIndicator) {
                // TODO use the classification library!
                $contextIndicator = ContextIndicator::loadByRef($refContext, $refIndicator);
                $algo->setContextIndicator($contextIndicator);
            }
        };

        $serializer = new Serializer([
            'Classif_Model_Indicator' => [
                'class' => Indicator::class,
                'properties' => [
                    'unit' => [
                        'callback' => function ($var) {
                            return new UnitAPI($var);
                        },
                    ],
                    'ratioUnit' => [
                        'callback' => function ($var) {
                            return new UnitAPI($var);
                        },
                    ],
                ],
                'callbacks' => function (Indicator $object) use ($classificationLibrary) {
                    $this->setProperty($object, 'library', $classificationLibrary);
                },
            ],
            'Classif_Model_Axis' => [
                'class' => Axis::class,
                'callbacks' => function (Axis $object) use ($classificationLibrary) {
                    $this->setProperty($object, 'library', $classificationLibrary);
                },
            ],
            'Classif_Model_Member' => [
                'class' => \Classification\Domain\AxisMember::class,
                'properties' => [
                    '_directChildren' => [ 'name' => 'directChildren' ],
                    '_directParents' => [ 'name' => 'directParents' ],
                ],
            ],
            'Classif_Model_Context' => [
                'class' => Context::class,
                'callbacks' => function (Context $object) use ($classificationLibrary) {
                    $this->setProperty($object, 'library', $classificationLibrary);
                },
            ],
            'Classif_Model_ContextIndicator' => [
                'class' => ContextIndicator::class,
                'callbacks' => function (ContextIndicator $object) use ($classificationLibrary) {
                    $this->setProperty($object, 'library', $classificationLibrary);
                },
            ],
            'Techno\Domain\Category' => [
                'class' => \Parameter\Domain\Category::class,
                'callbacks' => function (\Parameter\Domain\Category $category) use ($parameterLibrary) {
                    $this->setProperty($category, 'library', $parameterLibrary);
                },
            ],
            'Techno\Domain\Family\Family' => [
                'class' => Family::class,
                'callbacks' => function (Family $family) use ($parameterLibrary) {
                    $this->setProperty($family, 'library', $parameterLibrary);
                },
            ],
            'Techno\Domain\Family\Cell' => ['class' => Cell::class],
            'Techno\Domain\Family\Member' => ['class' => Member::class],
            'Techno\Domain\Family\Dimension' => ['class' => Dimension::class],
            \AF\Domain\Category::class => [
                'callbacks' => function (\AF\Domain\Category $category) use ($afLibrary) {
                    $this->setProperty($category, 'library', $afLibrary);
                },
            ],
            AF::class => [
                'callbacks' => function (AF $af) use ($afLibrary) {
                    $this->setProperty($af, 'library', $afLibrary);
                },
            ],
            FixedIndex::class => [
                'properties' => [
                    'refClassifMember' => [ 'name' => 'refClassificationMember' ],
                    'refClassifAxis' => [ 'name' => 'refClassificationAxis' ],
                ],
            ],
            AlgoResultIndex::class => [
                'properties' => [
                    'refClassifAxis' => [ 'name' => 'refClassificationAxis' ],
                ],
            ],
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
                    function (NumericParameterAlgo $algo, array $data) use ($parameterLibrary) {
                        $familyRef = new FamilyReference($parameterLibrary->getId(), $data['familyRef']);
                        $this->setProperty($algo, 'familyReference', $familyRef);
                    },
                ],
            ],
        ]);

        // Import users
        $output->writeln('<comment>Importing users</comment>');
        $objects = $serializer->unserialize(file_get_contents($root . '/users.json'));
        foreach ($objects as $user) {
            if ($user instanceof User) {
                $output->writeln(sprintf('<info>Imported user: %s</info>', $user->getName()));
                $user->save();
            }
        }

        // Import the classification
        $output->writeln('<comment>Importing classification</comment>');
        $objects = $serializer->unserialize(file_get_contents($root . '/classification.json'));
        foreach ($objects as $object) {
            if ($object instanceof \Core_Model_Entity) {
                $object->save();
            }
        }
        $this->entityManager->flush();

        // Import the parameters
        $output->writeln('<comment>Importing parameters</comment>');
        $objects = $serializer->unserialize(file_get_contents($root . '/parameters.json'));
        foreach ($objects as $object) {
            if ($object instanceof Family) {
                $output->writeln(sprintf('<info>Imported family: %s</info>', $object->getLabel()));
            }
            if ($object instanceof \Core_Model_Entity) {
                $object->save();
            }
        }

        // Import the AF
        $output->writeln('<comment>Importing AF</comment>');
        $objects = $serializer->unserialize(file_get_contents($root . '/af.json'));
        foreach ($objects as $object) {
            if ($object instanceof \AF\Domain\AF) {
                $output->writeln(sprintf('<info>Imported AF: %s</info>', $object->getLabel()));
            }
            if ($object instanceof \Core_Model_Entity) {
                $object->save();
            }
        }

        $this->entityManager->flush();
        $this->entityManager->commit();
    }

    private function setProperty($object, $property, $value)
    {
        $refl = new \ReflectionProperty($object, $property);
        $refl->setAccessible(true);
        $refl->setValue($object, $value);
    }
}
