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
use AF\Domain\Input\Select\SelectMultiInput;
use AF\Domain\InputSet\PrimaryInputSet;
use AF\Domain\InputSet\SubInputSet;
use AF\Domain\Output\OutputElement;
use AF\Domain\Output\OutputIndex;
use AF\Domain\Output\OutputTotal;
use Classification\Domain\Axis;
use Classification\Domain\ClassificationLibrary;
use Classification\Domain\Context;
use Classification\Domain\ContextIndicator;
use Classification\Domain\Indicator;
use Core\Translation\TranslatedString;
use Core_Exception_NotFound;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\EntityManager;
use Exception;
use Orga\Model\ACL\CellAdminRole;
use Orga\Model\ACL\CellContributorRole;
use Orga\Model\ACL\CellManagerRole;
use Orga\Model\ACL\CellObserverRole;
use Orga\Model\ACL\OrganizationAdminRole;
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
use MyCLabs\ACL\ACL;

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
     * @Inject
     * @var ACL
     */
    private $acl;

    /**
     * @Inject("translation.defaultLocale")
     * @var string
     */
    private $defaultLanguage;

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
        $classificationLibrary = new ClassificationLibrary(
            $account,
            new TranslatedString($label, $this->defaultLanguage)
        );
        $classificationLibrary->save();

        // Create the parameter library
        $label = $input->getArgument('parameterLibrary');
        $output->writeln("<comment>Creating library '$label'</comment>");
        $parameterLibrary = new ParameterLibrary($account, new TranslatedString($label, $this->defaultLanguage));
        $parameterLibrary->save();

        // Create the AF library
        $label = $input->getArgument('afLibrary');
        $output->writeln("<comment>Creating library '$label'</comment>");
        $afLibrary = new AFLibrary($account, new TranslatedString($label, $this->defaultLanguage));
        $afLibrary->save();


        $fixAlgoIndexation = function (NumericAlgo $algo, array $data) use ($classificationLibrary) {
            $refContext = $data['refContext'];
            $refIndicator = $data['refIndicator'];
            if ($refContext && $refIndicator) {
                $this->setProperty(
                    $algo,
                    'contextIndicator',
                    $classificationLibrary->getContextIndicatorByRef($refContext, $refIndicator)
                );
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
                    $classificationLibrary->addIndicator($object);
                },
            ],
            'Classif_Model_Axis' => [
                'class' => Axis::class,
                'callbacks' => function (Axis $object) use ($classificationLibrary) {
                    $this->setProperty($object, 'library', $classificationLibrary);
                    $classificationLibrary->addAxis($object);
                },
            ],
            'Classif_Model_Member' => [
                'class' => \Classification\Domain\Member::class,
                'properties' => [
                    '_directChildren' => [ 'name' => 'directChildren' ],
                    '_directParents' => [ 'name' => 'directParents' ],
                ],
            ],
            'Classif_Model_Context' => [
                'class' => Context::class,
                'callbacks' => function (Context $object) use ($classificationLibrary) {
                    $this->setProperty($object, 'library', $classificationLibrary);
                    $classificationLibrary->addContext($object);
                },
            ],
            'Classif_Model_ContextIndicator' => [
                'class' => ContextIndicator::class,
                'callbacks' => function (ContextIndicator $object) use ($classificationLibrary) {
                    $this->setProperty($object, 'library', $classificationLibrary);
                    $classificationLibrary->addContextIndicator($object);
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
                    $afLibrary->addCategory($category);
                },
            ],
            AF::class => [
                'callbacks' => function (AF $af) use ($afLibrary) {
                    $this->setProperty($af, 'library', $afLibrary);
                    $afLibrary->addAF($af);
                },
            ],
            FixedIndex::class => [
                'properties' => [
                    'refClassifMember' => [ 'name' => 'refMember' ],
                    'refClassifAxis' => [ 'exclude' => true ],
                ],
                'callbacks' => [
                    function (FixedIndex $algo, array $data) use ($classificationLibrary) {
                        $axis = $classificationLibrary->getAxisByRef($data['refClassifAxis']);
                        $this->setProperty($algo, 'axis', $axis);
                    },
                ],
            ],
            AlgoResultIndex::class => [
                'properties' => [
                    'refClassifAxis' => [ 'exclude' => true ],
                ],
                'callbacks' => [
                    function (AlgoResultIndex $algo, array $data) use ($classificationLibrary) {
                        $axis = $classificationLibrary->getAxisByRef($data['refClassifAxis']);
                        $this->setProperty($algo, 'axis', $axis);
                    },
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
            \Orga_Model_Organization::class => [
                'callbacks' => function (\Orga_Model_Organization $object) use ($account, $classificationLibrary) {
                        $this->setProperty($object, 'account', $account);
                        $this->setProperty($object, 'contextIndicators', new ArrayCollection());
                        foreach ($classificationLibrary->getContextIndicators() as $contextIndicator) {
                            $object->addContextIndicator($contextIndicator);
                        }
                    },
                'properties' => [
                    'acl' => [ 'exclude' => true ],
                    'adminRoles' => [ 'exclude' => true ],
                ],
            ],
            \Orga_Model_Axis::class => [],
            \Orga_Model_Member::class => [],
            \Orga_Model_Granularity::class => [
                'properties' => [
                    'dWCube' => [ 'exclude' => true ],
                ],
            ],
            \Orga_Model_Cell::class => [
                'properties' => [
                    'dWCube' => [ 'exclude' => true ],
                    'dWResults' => [ 'exclude' => true ],
                    'socialCommentsForAFInputSetPrimary' => [ 'name' => 'commentsForAFInputSetPrimary' ],
                    'socialGenericActions' => [ 'exclude' => true ],
                    'docLibraryForSocialGenericActions' => [ 'exclude' => true ],
                    'socialContextActions' => [ 'exclude' => true ],
                    'docLibraryForSocialContextActions' => [ 'exclude' => true ],
                    'acl' => [ 'exclude' => true ],
                    'adminRoles' => [ 'exclude' => true ],
                    'managerRoles' => [ 'exclude' => true ],
                    'contributorRoles' => [ 'exclude' => true ],
                    'observerRoles' => [ 'exclude' => true ],
                ],
                'callbacks' => function (\Orga_Model_Cell $object) {
                    foreach ($object->getCommentsForInputSetPrimary() as $comment) {
                        $this->setProperty($comment, 'cell', $object);
                    }
                },
            ],
            PrimaryInputSet::class => [
                'properties' => [
                    'refAF' => [ 'name' => 'af' ],
                    'af' => [
                        'callback' => function ($var) use ($afLibrary) {
                            foreach ($afLibrary->getAFList() as $af) {
                                if ($af->getRef() == $var) {
                                    return $af;
                                }
                            }
                            throw new Exception('AF "' . $var . '" NOT FOUND !');
                        },
                    ]
                ],
            ],
            SubInputSet::class => [
                'properties' => [
                    'refAF' => [ 'name' => 'af' ],
                    'af' => [
                        'callback' => function ($var) use ($afLibrary) {
                            foreach ($afLibrary->getAFList() as $af) {
                                if ($af->getRef() == $var) {
                                    return $af;
                                }
                            }
                            throw new Exception('AF "' . $var . '" NOT FOUND !');
                        },
                    ]
                ],
            ],
            SelectMultiInput::class => [
                'properties' => [
                    'value' => [
                        'callback' => function ($array) {
                            return new ArrayCollection($array);
                        },
                    ]
                ],
            ],
            OutputTotal::class => [
                'properties' => [
                    'refIndicator' => [ 'name' => 'indicator' ],
                    'indicator' => [
                        'callback' => function ($var) use ($classificationLibrary) {
                            return $classificationLibrary->getIndicatorByRef($var);
                        },
                    ]
                ],
            ],
            OutputIndex::class => [
                'properties' => [
                    'refAxis' => [ 'name' => 'axis' ],
                    'axis' => [
                        'callback' => function ($var) use ($classificationLibrary) {
                            return $classificationLibrary->getAxisByRef($var);
                        },
                    ]
                ],
            ],
            OutputElement::class => [
                'properties' => [
                    'algo' => [
                        'exclude' => true,
                    ],
                ],
                'callbacks' => [
                    function (OutputElement $outputElement, array $data) use ($parameterLibrary) {
                        $algo = $outputElement->getInputSet()->getAF()->getAlgoByRef($data['algo']);
                        $this->setProperty($outputElement, 'algo', $algo);
                    },
                ],
            ],
            'Social_Model_Comment' => [
                'class' => \Orga_Model_Cell_InputComment::class,
                'properties' => [
                    'author' => [
                        'callback' => function ($var) {
                            return User::loadByEmail($var);
                        },
                    ]
                ],
            ],
            \Orga_Model_GranularityReport::class => [ 'exclude' => true ],
            \Orga_Model_CellReport::class => [ 'exclude' => true ],
            \DW_Model_Cube::class => [
                'properties' => [
                    'axes' => [ 'exclude' => true ],
                    'indicators' => [ 'exclude' => true ],
                    'reports' => [ 'exclude' => true ],
                ],
            ],
            \DW_Model_Axis::class => [ 'exclude' => true ],
            \DW_Model_Member::class => [ 'exclude' => true ],
            \DW_Model_Indicator::class => [ 'exclude' => true ],
            \DW_Model_Result::class => [ 'exclude' => true ],
            \DW_Model_Report::class => [
                'exclude' => true
            ],
            \DW_Model_Filter::class => [
                'exclude' => true
            ],
            'User\Domain\ACL\Role\AdminRole' => [
                'exclude' => true,
            ],
            'User\Domain\ACL\Authorization\NamedResourceAuthorization' => [ 'exclude' => true ],
            'User\Domain\ACL\Resource\NamedResource' => [ 'exclude' => true ],
            'User\Domain\ACL\Role\UserRole' => [ 'exclude' => true ],
            'User\Domain\ACL\Authorization\UserAuthorization' => [ 'exclude' => true ],
            'Orga\Model\ACL\OrganizationAuthorization' => [ 'exclude' => true ],
            'Orga\Model\ACL\Role\OrganizationAdminRole' => [ 'exclude' => true ],
            'Orga\Model\ACL\CellAuthorization' => [ 'exclude' => true ],
            'Orga\Model\ACL\Role\CellAdminRole' => [ 'exclude' => true ],
            'Orga\Model\ACL\Role\CellManagerRole' => [ 'exclude' => true ],
            'Orga\Model\ACL\Role\CellContributorRole' => [ 'exclude' => true ],
            'Orga\Model\ACL\Role\CellObserverRole' => [ 'exclude' => true ],
        ]);

        // Import users
        $output->writeln('<comment>Importing users</comment>');
        $objects = $serializer->unserialize(file_get_contents($root . '/users.json'));
        foreach ($objects as $user) {
            if ($user instanceof User) {
                // Vérifie si l'utilisateur n'existe pas déjà (par ex. le super admin)
                try {
                    User::loadByEmail($user->getEmail());
                    continue;
                } catch (Core_Exception_NotFound $e) {
                }

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
                $output->writeln(sprintf('<info>Imported family: %s</info>', $object->getLabel()->get('fr')));
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
                $output->writeln(sprintf('<info>Imported AF: %s</info>', $object->getLabel()->get('fr')));
            }
            if ($object instanceof \Core_Model_Entity) {
                $object->save();
            }
        }
        $this->entityManager->flush();

        // Import Orga
        $output->writeln('<comment>Importing Orga</comment>');
        $objects = $serializer->unserialize(file_get_contents($root . '/orga.json'), $output);
        $output->writeln('<info>unserialization done</info>');
        foreach ($objects as $object) {
            if (($object instanceof \Core_Model_Entity) && !($object instanceof User)) {
                $object->save();
            }
        }

        $output->writeln('<info>flushing</info>');
        $this->entityManager->flush();
        $this->entityManager->clear();


        // Managing Orga.
        /** @var \Orga_Model_Organization $organization */
        $account = $this->entityManager->find(Account::class, $input->getArgument('account'));

        // Regenerate DW cubes.
        $output->writeln('<comment>Regenerating DW Cubes</comment>');
        $queryOrgaAccount = new \Core_Model_Query();
        $queryOrgaAccount->filter->addCondition('account', $account);
        /** @var \Orga_Model_Organization $organization */
        foreach (\Orga_Model_Organization::loadList($queryOrgaAccount) as $organization) {
            foreach ($organization->getGranularities() as $granularity) {
                $granularity->setCellsGenerateDWCubes($granularity->getCellsGenerateDWCubes());
            }
            $organization->save();
            $this->entityManager->flush();
        }
        $this->entityManager->flush();

        // Import DW reports and filter
        $serializer = new Serializer([]);
        $output->writeln('<comment>Importing Reports</comment>');
        $objects = $serializer->unserialize(file_get_contents($root . '/reports.json'));
        foreach ($objects as $object) {
            if (($object instanceof \StdClass) && ($object->type === "organization")) {
                $organization = $this->getOrganizationByLabel($object->label['fr']);
                foreach ($object->granularitiesReports as $granularityObject) {
                    $granularityAxes = [];
                    foreach ($granularityObject->granularityAxes as $refAxis) {
                        $granularityAxes[] = $organization->getAxisByRef($refAxis);
                    }
                    $granularity = $organization->getGranularityByRef(
                        \Orga_Model_Granularity::buildRefFromAxes($granularityAxes)
                    );

                    $dwCube = $granularity->getDWCube();
                    /** @var \DW_Model_Report $reportObject */
                    foreach ($granularityObject->granularityReports as $reportObject) {
                        $report = new \DW_Model_Report($dwCube);

                        $errorHappened = 0;

                        $report->setLabel(clone $reportObject->getLabel());
                        $report->setChartType($reportObject->getChartType());
                        $report->setSortType($reportObject->getSortType());
                        $report->setWithUncertainty($reportObject->getWithUncertainty());

                        if ($reportObject->getNumerator() != null) {
                            try {
                                $report->setNumerator(
                                    $dwCube->getIndicatorByRef(
                                        $classificationLibrary->getId() . '_' . $reportObject->getNumerator()
                                    )
                                );
                            } catch (\Core_Exception_NotFound $e) {
                                $errorHappened++;
                            }
                        }
                        if (!$errorHappened) {
                            if ($reportObject->getNumeratorAxis1() != null) {
                                $numeratorAxisRef = $reportObject->getNumeratorAxis1();
                                if (strstr($numeratorAxisRef, 'c_') === 0) {
                                    $numeratorAxisRef = 'c_' . $classificationLibrary->getId() . '_'
                                        . substr($reportObject->getNumeratorAxis1(), 2);
                                }
                                try {
                                    $report->setNumeratorAxis1(
                                        $dwCube->getAxisByRef($numeratorAxisRef)
                                    );
                                } catch (\Core_Exception_NotFound $e) {
                                    $errorHappened = true;
                                }
                            }
                            if (!$errorHappened) {
                                if ($reportObject->getNumeratorAxis2() != null) {
                                    $numeratorAxisRef = $reportObject->getNumeratorAxis2();
                                    if (strstr($numeratorAxisRef, 'c_') === 0) {
                                        $numeratorAxisRef = 'c_' . $classificationLibrary->getId() . '_'
                                            . substr($reportObject->getNumeratorAxis2(), 2);
                                    }
                                    try {
                                        $report->setNumeratorAxis2(
                                            $dwCube->getAxisByRef($numeratorAxisRef)
                                        );
                                    } catch (\Core_Exception_NotFound $e) {
                                        $errorHappened = true;
                                    }
                                }
                            }
                            if ($reportObject->getDenominator() != null) {
                                try {
                                    $report->setDenominator(
                                        $dwCube->getIndicatorByRef(
                                            $classificationLibrary->getId() . '_' . $reportObject->getDenominator()
                                        )
                                    );
                                } catch (\Core_Exception_NotFound $e) {
                                    $errorHappened++;
                                }
                                if (!$errorHappened) {
                                    if ($reportObject->getDenominatorAxis1() != null) {
                                        $denominatorAxisRef = $reportObject->getDenominatorAxis1();
                                        if (strstr($denominatorAxisRef, 'c_') === 0) {
                                            $denominatorAxisRef = 'c_' . $classificationLibrary->getId() . '_'
                                                . substr($reportObject->getDenominatorAxis1(), 2);
                                        }
                                        try {
                                            $report->setDenominatorAxis1(
                                                $dwCube->getAxisByRef($denominatorAxisRef)
                                            );
                                        } catch (\Core_Exception_NotFound $e) {
                                            $errorHappened = true;
                                        }
                                    }
                                    if (!$errorHappened) {
                                        if ($reportObject->getDenominatorAxis2() != null) {
                                            $denominatorAxisRef = $reportObject->getDenominatorAxis2();
                                            if (strstr($denominatorAxisRef, 'c_') === 0) {
                                                $denominatorAxisRef = 'c_' . $classificationLibrary->getId() . '_'
                                                    . substr($reportObject->getDenominatorAxis2(), 2);
                                            }
                                            try {
                                                $report->setDenominatorAxis2(
                                                    $dwCube->getAxisByRef($denominatorAxisRef)
                                                );
                                            } catch (\Core_Exception_NotFound $e) {
                                                $errorHappened = true;
                                            }
                                        }
                                    }
                                }
                            }
                        }
                        foreach ($reportObject->getFilters() as $filterObject) {
                            $filterAxisRef = $filterObject->getAxis();
                            if (strstr($filterAxisRef, 'c_') === 0) {
                                $filterAxisRef = 'c_' . $classificationLibrary->getId() . '_'
                                    . substr($filterObject->getAxis(), 2);
                            }
                            try {
                                $axis = $dwCube->getAxisByRef($filterAxisRef);
                            } catch (\Core_Exception_NotFound $e) {
                                $errorHappened = true;
                                continue;
                            }
                            $filter = new \DW_Model_Filter($report, $axis);
                            foreach ($filterObject->getMembers() as $refMember) {
                                try {
                                    $filter->addMember(
                                        $axis->getMemberByRef($refMember)
                                    );
                                } catch (\Core_Exception_NotFound $e) {
                                    $errorHappened = true;
                                }
                            }
                        }
                        if ($errorHappened) {
                            $output->writeln(
                                '<error>'.
                                'Configuration broken while migrating report '.
                                '"' . $report->getLabel()->get('fr') . '"'.
                                '</error>'
                            );
                        }
                        $report->save();
                    }
                }
            }
        }
        $this->entityManager->flush();

        $output->writeln('<comment>Importing ACL</comment>');
        $objects = $serializer->unserialize(file_get_contents($root . '/acl.json'));
        foreach ($objects as $object) {
            if (($object instanceof \StdClass) && ($object->type === "organization")) {
                $organization = $this->getOrganizationByLabel($object->label['fr']);

                foreach ($object->admins as $adminEmail) {
                    $output->writeln(sprintf(
                        '<comment>%s admin of organization %s</comment>',
                        $adminEmail,
                        $organization->getLabel()->get('fr')
                    ));
                    $this->acl->grant(
                        User::loadByEmail($adminEmail),
                        new OrganizationAdminRole(
                            User::loadByEmail($adminEmail),
                            $organization
                        )
                    );
                }

                foreach ($object->granularitiesACL as $granularityObject) {
                    $granularityAxes = [];
                    foreach ($granularityObject->granularityAxes as $refAxis) {
                        $granularityAxes[] = $organization->getAxisByRef($refAxis);
                    }
                    $granularity = $organization->getGranularityByRef(
                        \Orga_Model_Granularity::buildRefFromAxes($granularityAxes)
                    );

                    foreach ($granularityObject->cellsACL as $cellObject) {
                        $members = [];
                        foreach ($cellObject->members as $refAxisMember) {
                            list($refAxis, $refMember) = explode(';', $refAxisMember);
                            $axis = $organization->getAxisByRef($refAxis);
                            $members[] = $axis->getMemberByCompleteRef($refMember);
                        }
                        $cell = $granularity->getCellByMembers($members);

                        foreach ($cellObject->admins as $adminEmail) {
                            $output->writeln(
                                '<comment>'.$adminEmail.' admin of cell '.$cell->getLabel()->get('fr').'</comment>'
                            );
                            $this->acl->grant(
                                User::loadByEmail($adminEmail),
                                new CellAdminRole(User::loadByEmail($adminEmail), $cell)
                            );
                        }
                        foreach ($cellObject->managers as $managerEmail) {
                            $output->writeln(
                                '<comment>'.$managerEmail.' manager of cell '.$cell->getLabel()->get('fr').'</comment>'
                            );
                            $this->acl->grant(
                                User::loadByEmail($managerEmail),
                                new CellManagerRole(User::loadByEmail($managerEmail), $cell)
                            );
                        }
                        foreach ($cellObject->contributors as $contributorEmail) {
                            $output->writeln(
                                '<comment>'.$contributorEmail.' contributor of cell '.$cell->getLabel()->get('fr').'</comment>'
                            );
                            $this->acl->grant(
                                User::loadByEmail($contributorEmail),
                                new CellContributorRole(User::loadByEmail($contributorEmail), $cell)
                            );
                        }
                        foreach ($cellObject->observers as $observerEmail) {
                            $output->writeln(
                                '<comment>'.$observerEmail.' observer of cell '.$cell->getLabel()->get('fr').'</comment>'
                            );
                            $this->acl->grant(
                                User::loadByEmail($observerEmail),
                                new CellObserverRole(User::loadByEmail($observerEmail), $cell)
                            );
                        }
                    }
                }
            }
        }

        $this->entityManager->commit();
    }

    private function setProperty($object, $property, $value)
    {
        $refl = new \ReflectionProperty($object, $property);
        $refl->setAccessible(true);
        $refl->setValue($object, $value);
    }

    /**
     * @param string $label
     * @return \Orga_Model_Organization
     */
    private function getOrganizationByLabel($label)
    {
        foreach (\Orga_Model_Organization::loadList() as $organization) {
            if ($organization->getLabel()->get('fr') === $label) {
                return $organization;
            }
        }
        throw new Exception;
    }
}
