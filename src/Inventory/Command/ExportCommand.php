<?php

namespace Inventory\Command;

use AF\Domain\Category as AFCategory;
use Serializer\Serializer;
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
    protected function configure()
    {
        $this->setName('export')
            ->setDescription('Exporte les données');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        ini_set('xdebug.max_nesting_level', 1000);

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

        $output->writeln('<comment>Exporting Orga</comment>');
        $data = \Orga_Model_Organization::loadList();
        file_put_contents($root . '/orga.json', $serializer->serialize($data));

        $reportsData = [];
        $aclData = [];
        /** @var \Orga_Model_Organization $organization */
        foreach (\Orga_Model_Organization::loadList() as $organization) {
            $organizationAdmins = [];
            foreach ($organization->getAdminRoles() as $adminRoles) {
                $organizationAdmins[] = $adminRoles->getUser()->getEmail();
            }

            $cellsACL = [];
            $granularitiesReports = [];

            foreach ($organization->getGranularities() as $granularity) {
                if ($granularity->getCellsWithACL()) {
                    foreach ($granularity->getCells() as $cell) {
                        $cellAdmins = [];
                        foreach ($cell->getAdminRoles() as $cellAdmin) {
                            $cellAdmins[] = $cellAdmin->getUser()->getEmail();
                        }
                        $cellManagers = [];
                        foreach ($cell->getManagerRoles() as $cellManager) {
                            $cellManagers[] = $cellManager->getUser()->getEmail();
                        }
                        $cellContributors = [];
                        foreach ($cell->getContributorRoles() as $cellContributor) {
                            $cellContributors[] = $cellContributor->getUser()->getEmail();
                        }
                        $cellObserver = [];
                        foreach ($cell->getObserverRoles() as $cellObserver) {
                            $cellObservers[] = $cellObserver->getUser()->getEmail();
                        }
                        if ((count($cellAdmins) > 0) || (count($cellManagers) > 0)
                            || (count($cellContributors) > 0) || (count($cellObservers) > 0)) {
                            $cellMembers = $cell->getMembers();
                            $cellDataObject = new \StdClass();
                            $cellDataObject->type = 'cell';
                            $cellDataObject->members = array_map(
                                function ($m) { return $m->getAxis()->getRef() . ';' . $m->getRef(); },
                                $cellMembers
                            );
                            $cellDataObject->admins = $cellAdmins;
                            $cellDataObject->admins = $cellManagers;
                            $cellDataObject->admins = $cellContributors;
                            $cellDataObject->admins = $cellObservers;
                            $cellsACL[] = $cellDataObject;
                        }
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
                        function ($a) { return $a->getRef(); },
                        $granularityAxes
                    );
                    $granularityDataObject->granularityReports = $granularityReports;
                    $granularityDataObject->cellsReports = $cellsReports;
                    $granularitiesReports[] = $granularityDataObject;
                }
            }
            if ((count($organizationAdmins) > 0) || (count($cellsACL) > 0)) {
                $organizationDataObject = new \StdClass();
                $organizationDataObject->type = 'organization';
                $organizationDataObject->label = $organization->getLabel();
                $organizationDataObject->admins = $organizationAdmins;
                $organizationDataObject->cellsACL = $cellsACL;
                $aclData[] = $organizationDataObject;
            }
            if (count($granularitiesReports) > 0) {
                $organizationDataObject = new \StdClass();
                $organizationDataObject->type = 'organization';
                $organizationDataObject->label = $organization->getLabel();
                $organizationDataObject->granularitiesReports = $granularitiesReports;
                $reportsData[] = $organizationDataObject;
            }
        }

        $output->writeln('<comment>Exporting Reports</comment>');
        $reportsSerializer = new Serializer(
            [
                \DW_Model_Report::class => [
                    'properties' => [
                        'cube' => [
                            'exclude' => true,
                        ],
                        'numerator' => [
                            'transform' => function ($i) { return ($i != null) ? $i->getRef() : null; },
                        ],
                        'denominator' => [
                            'transform' => function ($i) { return ($i != null) ? $i->getRef() : null; },
                        ],
                        'numeratorAxis1' => [
                            'transform' => function ($i) { return ($i != null) ? $i->getRef() : null; },
                        ],
                        'numeratorAxis2' => [
                            'transform' => function ($i) { return ($i != null) ? $i->getRef() : null; },
                        ],
                        'denominatorAxis1' => [
                            'transform' => function ($i) { return ($i != null) ? $i->getRef() : null; },
                        ],
                        'denominatorAxis2' => [
                            'transform' => function ($i) { return ($i != null) ? $i->getRef() : null; },
                        ],
                    ],
                ],
                \DW_Model_Filter::class => [
                    'properties' => [
                        'cube' => [
                            'exclude' => true,
                        ],
                        'axis' => [
                            'transform' => function ($i) { return $i->getRef(); },
                        ],
                        'members' => [
                            'transform' => function ($i) {
                                    $members = $i->toArray();
                                    return array_map(function ($m) { return $m->getRef(); }, $members);
                                },
                        ],
                    ],
                ],
            ],
            false
        );
        file_put_contents($root . '/reports.json', $reportsSerializer->serialize($reportsData));

        $output->writeln('<comment>Exporting ACL</comment>');
        $aclSerializer = new Serializer([]);
        file_put_contents($root . '/acl.json', $aclSerializer->serialize($aclData));
    }
}
