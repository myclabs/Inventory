<?php

namespace Inventory\Command;

use AF\Domain\Algorithm\Numeric\NumericAlgo;
use AF\Domain\Category as AFCategory;
use Classification\Domain\Axis;
use Classification\Domain\Context;
use Classification\Domain\ContextIndicator;
use Classification\Domain\Indicator;
use Doctrine\Common\Collections\Collection;
use Serializer\CustomSerializerForMigration;
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
        ini_set('xdebug.max_nesting_level', 1000);

        $root = PACKAGE_PATH . '/data/exports/migration-3.0';

        $serializer = new CustomSerializerForMigration([
            \AF\Domain\Output\OutputElement::class => [
                'properties' => [
                    'algo' => [
                        'transform' => function (NumericAlgo $algo) {
                            return $algo->getRef();
                        },
                    ],
                ],
            ],
            \Orga\Domain\ACL\WorkspaceAdminRole::class => [ 'exclude' => true ],
            \Orga\Domain\ACL\CellAdminRole::class => [ 'exclude' => true ],
            \Orga\Domain\ACL\CellManagerRole::class => [ 'exclude' => true ],
            \Orga\Domain\ACL\CellContributorRole::class => [ 'exclude' => true ],
            \Orga\Domain\ACL\CellObserverRole::class => [ 'exclude' => true ],
            \DW\Domain\Cube::class => [ 'exclude' => true ],
            \DW\Domain\Axis::class => [ 'exclude' => true ],
            \DW\Domain\Member::class => [ 'exclude' => true ],
            \DW\Domain\Indicator::class => [ 'exclude' => true ],
            \DW\Domain\Result::class => [ 'exclude' => true ],
            \DW\Domain\Report::class => [ 'exclude' => true ],
            \DW\Domain\Filter::class => [ 'exclude' => true ],
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
            \Orga\Domain\Cell\CellInputComment::class => [
                'properties' => [
                    'author' => [
                        'transform' => function (User $author) {
                            return $author->getEmail();
                        },
                    ],
                ],
            ],
            \Orga\Domain\Cell::class => [
                'properties' => [
                    'acl' => [ 'exclude' => true ],
                    'dwResults' => [ 'exclude' => true ],
                ],
            ],
            \Orga\Domain\SubCellsGroup::class => [
                'properties' => [
                    'aF' => [
                        'transform' => function (\AF\Domain\AF $af = null) {
                            if ($af) {
                                return $af->getLabel();
                            } else {
                                return null;
                            }
                        },
                    ],
                ],
            ],
            \Calc_UnitValue::class => [
                'serialize' => true,
            ],
            \Calc_Value::class => [
                'serialize' => true,
            ],
        ]);

        $output->writeln('<comment>Exporting users</comment>');
        $data = User::loadList();
        file_put_contents($root . '/users.json', $serializer->serialize($data));

        $output->writeln('<comment>Exporting classification</comment>');
        $data = [
            Indicator::loadList(),
            Axis::loadList(),
            Context::loadList(),
            ContextIndicator::loadList(),
        ];
        file_put_contents($root . '/classification.json', $serializer->serialize($data));

        $output->writeln('<comment>Exporting parameters</comment>');
        $data = TechnoCategory::loadRootCategories();
        file_put_contents($root . '/parameters.json', $serializer->serialize($data));

        $output->writeln('<comment>Exporting AF</comment>');
        $data = AFCategory::loadRootCategories();
        file_put_contents($root . '/af.json', $serializer->serialize($data));

        $output->writeln('<comment>Exporting Orga</comment>');
        $data = \Orga\Domain\Workspace::loadList();
        file_put_contents($root . '/orga.json', $serializer->serialize($data));

        $reportsData = [];
        $aclData = [];
        /** @var \Orga\Domain\Workspace $workspace */
        foreach (\Orga\Domain\Workspace::loadList() as $workspace) {
            $workspaceAdmins = [];
            foreach ($workspace->getAdminRoles() as $adminRoles) {
                $workspaceAdmins[] = $adminRoles->getSecurityIdentity()->getEmail();
            }

            $granularitiesACL = [];
            $granularitiesReports = [];

            foreach ($workspace->getGranularities() as $granularity) {
                if ($granularity->getCellsWithACL()) {
                    $cellsACL = [];
                    foreach ($granularity->getCells() as $cell) {
                        $cellAdmins = [];
                        foreach ($cell->getAdminRoles() as $cellAdmin) {
                            $cellAdmins[] = $cellAdmin->getSecurityIdentity()->getEmail();
                        }
                        $cellManagers = [];
                        foreach ($cell->getManagerRoles() as $cellManager) {
                            $cellManagers[] = $cellManager->getSecurityIdentity()->getEmail();
                        }
                        $cellContributors = [];
                        foreach ($cell->getContributorRoles() as $cellContributor) {
                            $cellContributors[] = $cellContributor->getSecurityIdentity()->getEmail();
                        }
                        $cellObservers = [];
                        foreach ($cell->getObserverRoles() as $cellObserver) {
                            $cellObservers[] = $cellObserver->getSecurityIdentity()->getEmail();
                        }
                        if ((count($cellAdmins) > 0) || (count($cellManagers) > 0)
                            || (count($cellContributors) > 0) || (count($cellObservers) > 0)) {
                            $cellMembers = $cell->getMembers();
                            $cellDataObject = new \StdClass();
                            $cellDataObject->type = 'cell';
                            $cellDataObject->members = array_map(
                                function (\Orga\Domain\Member $m) {
                                    return $m->getAxis()->getRef() . ';' . $m->getCompleteRef();
                                },
                                $cellMembers
                            );
                            $cellDataObject->admins = $cellAdmins;
                            $cellDataObject->managers = $cellManagers;
                            $cellDataObject->contributors = $cellContributors;
                            $cellDataObject->observers = $cellObservers;
                            $cellsACL[] = $cellDataObject;
                        }
                    }

                    if (count($cellsACL) > 0) {
                        $granularityAxes = $granularity->getAxes();
                        $granularityDataObject = new \StdClass();
                        $granularityDataObject->type = 'granularity';
                        $granularityDataObject->granularityAxes = array_map(
                            function (\Orga\Domain\Axis $a) {
                                return $a->getRef();
                            },
                            $granularityAxes
                        );
                        $granularityDataObject->cellsACL = $cellsACL;
                        $granularitiesACL[] = $granularityDataObject;
                    }
                }

                if ($granularity->getCellsGenerateDWCubes()) {
                    $granularityReports = [];
                    foreach ($granularity->getDWCube()->getReports() as $granularityReport) {
                        $granularityReports[] = $granularityReport;
                    }
                    // Les rapports personnalisés ne fonctionnent pas dans la version actuelle.
                    //@see http://tasks.myc-sense.com/issues/7077
                    $cellsReports = [];
                    $granularityAxes = $granularity->getAxes();
                    $granularityDataObject = new \StdClass();
                    $granularityDataObject->type = 'granularity';
                    $granularityDataObject->granularityAxes = array_map(
                        function (\Orga\Domain\Axis $a) {
                            return $a->getRef();
                        },
                        $granularityAxes
                    );
                    $granularityDataObject->granularityReports = $granularityReports;
                    $granularityDataObject->cellsReports = $cellsReports;
                    $granularitiesReports[] = $granularityDataObject;
                }
            }
            if ((count($workspaceAdmins) > 0) || (count($granularitiesACL) > 0)) {
                $workspaceDataObject = new \StdClass();
                $workspaceDataObject->type = 'organization';
                $workspaceDataObject->label = $workspace->getLabel();
                $workspaceDataObject->admins = $workspaceAdmins;
                $workspaceDataObject->granularitiesACL = $granularitiesACL;
                $aclData[] = $workspaceDataObject;
            }
            if (count($granularitiesReports) > 0) {
                $workspaceDataObject = new \StdClass();
                $workspaceDataObject->type = 'organization';
                $workspaceDataObject->label = $workspace->getLabel();
                $workspaceDataObject->granularitiesReports = $granularitiesReports;
                $reportsData[] = $workspaceDataObject;
            }
        }

        $output->writeln('<comment>Exporting Reports</comment>');
        $reportsSerializer = new CustomSerializerForMigration(
            [
                \DW\Domain\Report::class => [
                    'properties' => [
                        'cube' => [
                            'exclude' => true,
                        ],
                        'numerator' => [
                            'transform' => function (\DW\Domain\Indicator $i) {
                                return ($i != null) ? $i->getRef() : null;
                            },
                        ],
                        'denominator' => [
                            'transform' => function (\DW\Domain\Indicator $i) {
                                return ($i != null) ? $i->getRef() : null;
                            },
                        ],
                        'numeratorAxis1' => [
                            'transform' => function (\DW\Domain\Axis $i) {
                                return ($i != null) ? $i->getRef() : null;
                            },
                        ],
                        'numeratorAxis2' => [
                            'transform' => function (\DW\Domain\Axis $i) {
                                return ($i != null) ? $i->getRef() : null;
                            },
                        ],
                        'denominatorAxis1' => [
                            'transform' => function (\DW\Domain\Axis $i) {
                                return ($i != null) ? $i->getRef() : null;
                            },
                        ],
                        'denominatorAxis2' => [
                            'transform' => function (\DW\Domain\Axis $i) {
                                return ($i != null) ? $i->getRef() : null;
                            },
                        ],
                    ],
                ],
                \DW\Domain\Filter::class => [
                    'properties' => [
                        'cube' => [
                            'exclude' => true,
                        ],
                        'axis' => [
                            'transform' => function (\DW\Domain\Axis $i) {
                                return $i->getRef();
                            },
                        ],
                        'members' => [
                            'transform' => function (Collection $c) {
                                $members = $c->toArray();
                                return array_map(function (\DW\Domain\Member $m) {
                                    return $m->getRef();
                                }, $members);
                            },
                        ],
                    ],
                ],
            ]
        );
        file_put_contents($root . '/reports.json', $reportsSerializer->serialize($reportsData));

        $output->writeln('<comment>Exporting ACL</comment>');
        $aclSerializer = new CustomSerializerForMigration([]);
        file_put_contents($root . '/acl.json', $aclSerializer->serialize($aclData));
    }
}
