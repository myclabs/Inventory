<?php

namespace Orga\Domain\Service\ETL;

use Classification\Domain\ContextIndicator;
use Classification\Domain\Indicator;
use Classification\Domain\Axis as ClassificationAxis;
use Classification\Domain\Member as ClassificationMember;
use Core\Translation\TranslatedString;
use Core_EventDispatcher;
use Doctrine\ORM\EntityManager;
use DW\Application\Service\ReportService;
use DW\Domain\Axis as DWAxis;
use DW\Domain\Cube as DWCube;
use DW\Domain\Indicator as DWIndicator;
use DW\Domain\Member as DWMember;
use DW\Domain\Report as DWReport;
use DW\Domain\Result as DWResult;
use Mnapoli\Translated\Translator;
use Orga\Domain\Axis as OrgaAxis;
use Orga\Domain\Member as OrgaMember;
use Orga\Domain\Granularity;
use Orga\Domain\Cell;
use Orga\Domain\Report\GranularityReport;
use Core_Exception_NotFound;
use Core_Model_Query;
use ErrorException;
use Exception;
use Orga\Domain\Service\ETL\OrgaReportFactory;
use Orga\Domain\Workspace;

/**
 * ETLStructureService
 *
 * @author valentin.claras
 */
class ETLStructureService implements ETLStructureInterface
{
    /**
     * @var EntityManager
     */
    private $entityManager;

    /**
     * @var ETLDataService
     */
    private $etlDataService;

    /**
     * @var ReportService
     */
    private $reportService;

    /**
     * @var Core_EventDispatcher
     */
    private $eventDispatcher;

    /**
     * La locale par défaut de l'application.
     *
     * @var string
     */
    private $defaultLocale;

    /**
     * Les différentes locales de l'application.
     *
     * @var array|string[]
     */
    private $locales;

    /**
     * @var Translator
     */
    private $translator;


    /**
     * @param EntityManager $entityManager
     * @param ETLDataService $etlDataService
     * @param ReportService $reportService
     * @param Core_EventDispatcher $eventDispatcher
     * @param string $defaultLocale
     * @param string[] $locales
     * @param Translator $translator
     */
    public function __construct(
        EntityManager $entityManager,
        ETLDataService $etlDataService,
        ReportService $reportService,
        Core_EventDispatcher $eventDispatcher,
        $defaultLocale,
        array $locales,
        Translator $translator
    ) {
        $this->entityManager = $entityManager;
        $this->etlDataService = $etlDataService;
        $this->reportService = $reportService;
        $this->eventDispatcher = $eventDispatcher;
        $this->defaultLocale = $defaultLocale;
        $this->locales = $locales;
        $this->translator = $translator;
    }

    /**
     * @param Cell $cell
     */
    public function populateCellDWCube(Cell $cell)
    {
        $this->updateCellDWCubeLabel($cell);
        $this->populateDWCube(
            $cell->getDWCube(),
            $cell->getGranularity()->getWorkspace(),
            ['axes' => $cell->getGranularity()->getAxes(), 'members' => $cell->getMembers()]
        );
    }

    /**
     * @param Granularity $granularity
     */
    public function populateGranularityDWCube(Granularity $granularity)
    {
        $this->updateGranularityDWCubeLabel($granularity);
        $this->populateDWCube(
            $granularity->getDWCube(),
            $granularity->getWorkspace(),
            ['axes' => $granularity->getAxes()]
        );
    }

    /**
     * @param Cell $cell
     */
    private function updateCellDWCubeLabel(Cell $cell)
    {
        $cube = $cell->getDWCube();

        $labels = array_map(
            function (OrgaMember $member) {
                return $member->getLabel();
            },
            $cell->getMembers()
        );

        $cube->setLabel(TranslatedString::implode(Cell::LABEL_SEPARATOR, $labels));
    }

    /**
     * @param Granularity $granularity
     */
    private function updateGranularityDWCubeLabel(Granularity $granularity)
    {
        $cube = $granularity->getDWCube();

        $labels = array_map(
            function (OrgaAxis $axis) {
                return $axis->getLabel();
            },
            $granularity->getAxes()
        );

        $cube->setLabel(TranslatedString::implode(Granularity::LABEL_SEPARATOR, $labels));
    }

    /**
     * @param DWCube $dWCube
     * @param Workspace $orgaWorkspace
     * @param array $orgaFilters
     */
    private function populateDWCube(
        DWCube $dWCube,
        Workspace $orgaWorkspace,
        array $orgaFilters
    ) {
        $this->populateDWCubeWithOrga($dWCube, $orgaWorkspace, $orgaFilters);
        $this->populateDWCubeWithClassification($dWCube, $orgaWorkspace);
        $this->populateDWCubeWithAF($dWCube);
    }

    /**
     * @param DWCube $dWCube
     */
    private function populateDWCubeWithAF(DWCube $dWCube)
    {
        $inputStatusDWAxis = new DWAxis($dWCube);
        $inputStatusDWAxis->setRef('inputStatus');

        $finishedDWMember = new DWMember($inputStatusDWAxis);
        $finishedDWMember->setRef('finished');

        $completedDWMember = new DWMember($inputStatusDWAxis);
        $completedDWMember->setRef('completed');

        foreach ($this->locales as $localeId) {
            switch ($localeId) {
                case 'fr':
                    $inputStatusLabel = 'Statut de saisie';
                    $finishedLabel = 'Terminé';
                    $completedLabel = 'Complet';
                    break;
                case 'en':
                    $inputStatusLabel = 'Input status';
                    $finishedLabel = 'Finished';
                    $completedLabel = 'Complete';
                    break;
                default:
                    $inputStatusLabel = '';
                    $finishedLabel = '';
                    $completedLabel = '';
                    break;
            }

            $inputStatusDWAxis->getLabel()->set($inputStatusLabel, $localeId);
            $finishedDWMember->getLabel()->set($finishedLabel, $localeId);
            $completedDWMember->getLabel()->set($completedLabel, $localeId);
        }
    }

    /**
     * @param DWCube $dWCube
     * @param Workspace $orgaWorkspace
     */
    private function populateDWCubeWithClassification(
        DWCube $dWCube,
        Workspace $orgaWorkspace
    ) {
        $classificationIndicators = array_map(
            function (ContextIndicator $contextIndicator) {
                return $contextIndicator->getIndicator();
            },
            $orgaWorkspace->getContextIndicators()->toArray()
        );
        $classificationIndicators = array_unique($classificationIndicators);
        foreach ($classificationIndicators as $classificationIndicator) {
            /** @var ContextIndicator $classificationContextIndicator */
            $this->copyIndicatorFromClassificationToDWCube($classificationIndicator, $dWCube);
        }

        foreach ($orgaWorkspace->getClassificationAxes() as $classificationAxis) {
            $this->copyAxisAndMembersFromClassificationToDW($classificationAxis, $dWCube);
        }
    }

    /**
     * @param Indicator $classificationIndicator
     * @param DWCube $dWCube
     */
    private function copyIndicatorFromClassificationToDWCube(Indicator $classificationIndicator, DWCube $dWCube)
    {
        $dWIndicator = new DWIndicator($dWCube);
        $dWIndicator->setRef(
            $classificationIndicator->getLibrary()->getId() . '_' . $classificationIndicator->getRef()
        );
        $dWIndicator->setUnit($classificationIndicator->getUnit());
        $dWIndicator->setRatioUnit($classificationIndicator->getRatioUnit());
        $dWIndicator->setLabel(clone $classificationIndicator->getLabel());
    }

    /**
     * @param ClassificationAxis $classificationAxis
     * @param DWCube $dwCube
     * @param array &$associationArray
     */
    private function copyAxisAndMembersFromClassificationToDW(
        ClassificationAxis $classificationAxis,
        DWCube $dwCube,
        &$associationArray = []
    ) {
        $dWAxis = new DWAxis($dwCube);
        $dWAxis->setRef('c_' . $classificationAxis->getLibrary()->getId() . '_' . $classificationAxis->getRef());
        $dWAxis->setLabel(clone $classificationAxis->getLabel());

        $associationArray['axes'][$classificationAxis->getRef()] = $dWAxis;
        $narrowerAxis = $classificationAxis->getDirectNarrower();
        if ($narrowerAxis !== null) {
            $dWAxis->setDirectNarrower($associationArray['axes'][$narrowerAxis->getRef()]);
        }

        foreach ($classificationAxis->getMembers() as $classificationMember) {
            $dWMember = new DWMember($dWAxis);
            $dWMember->setRef($classificationMember->getRef());
            $dWMember->setPosition($classificationMember->getPosition());
            $dWMember->setLabel(clone $classificationMember->getLabel());

            $memberIdentifier = $classificationMember->getAxis()->getRef() . '_' . $classificationMember->getRef();
            $associationArray['members'][$memberIdentifier] = $dWMember;
            foreach ($classificationMember->getDirectChildren() as $narrowerClassificationMember) {
                $narrowerIdentifier = $narrowerClassificationMember->getAxis()->getRef()
                    . '_' . $narrowerClassificationMember->getRef();
                $associationArray['members'][$narrowerIdentifier]->setDirectParentForAxis($dWMember);
            }
        }

        foreach ($classificationAxis->getDirectBroaders() as $broaderClassificationAxis) {
            $this->copyAxisAndMembersFromClassificationToDW($broaderClassificationAxis, $dwCube, $associationArray);
        }
    }

    /**
     * @param DWCube $dWCube
     * @param Workspace $orgaWorkspace
     * @param array $orgaFilters
     */
    private function populateDWCubeWithOrga(
        DWCube $dWCube,
        Workspace $orgaWorkspace,
        $orgaFilters
    ) {
        foreach ($orgaWorkspace->getRootAxes() as $orgaAxis) {
            $this->copyAxisAndMembersFromOrgaToDW($orgaAxis, $dWCube, $orgaFilters);
        }
    }

    /**
     * @param OrgaAxis $orgaAxis
     * @param DWCube $dwCube
     * @param array $orgaFilters
     * @param array &$associationArray
     */
    private function copyAxisAndMembersFromOrgaToDW(
        OrgaAxis $orgaAxis,
        DWCube $dwCube,
        $orgaFilters,
        &$associationArray = []
    ) {
        if (in_array($orgaAxis, $orgaFilters['axes'])) {
            return;
        } else {
            $filteringOrgaBroaderAxes = array();
            foreach ($orgaFilters['axes'] as $filteringOrgaAxis) {
                if ($orgaAxis->isNarrowerThan($filteringOrgaAxis)) {
                    $filteringOrgaBroaderAxes[] = $filteringOrgaAxis;
                }
            }
        }

        $dWAxis = new DWAxis($dwCube);
        $dWAxis->setRef('o_' . $orgaAxis->getRef());
        $dWAxis->setLabel(clone $orgaAxis->getLabel());

        $associationArray['axes'][$orgaAxis->getRef()] = $dWAxis;
        $narrowerAxis = $orgaAxis->getDirectNarrower();
        if ($narrowerAxis !== null) {
            $dWAxis->setDirectNarrower($associationArray['axes'][$narrowerAxis->getRef()]);
        }

        foreach ($orgaAxis->getOrderedMembers() as $orgaMember) {
            if (isset($orgaFilters['members'])) {
                foreach ($filteringOrgaBroaderAxes as $filteringOrgaAxis) {
                    foreach ($orgaFilters['members'] as $filteringOrgaMember) {
                        /** @var OrgaMember $filteringOrgaMember */
                        if (($filteringOrgaMember->getAxis() === $filteringOrgaAxis)
                            && (in_array($filteringOrgaMember, $orgaMember->getAllParents()))
                        ) {
                            continue 2;
                        }
                    }
                    continue 2;
                }
            }

            $dWMember = new DWMember($dWAxis);
            $dWMember->setRef($orgaMember->getRef());
            $dWMember->setLabel(clone $orgaMember->getLabel());

            $memberIdentifier = $orgaMember->getAxis()->getRef() . '_' . $orgaMember->getCompleteRef();
            $associationArray['members'][$memberIdentifier] = $dWMember;
            foreach ($orgaMember->getDirectChildren() as $narrowerOrgaMember) {
                $narrowerIdentifier = $narrowerOrgaMember->getAxis()->getRef() . '_'
                    . $narrowerOrgaMember->getCompleteRef();
                if (isset($associationArray['members'][$narrowerIdentifier])) {
                    $associationArray['members'][$narrowerIdentifier]->setDirectParentForAxis($dWMember);
                }
            }
        }

        foreach ($orgaAxis->getDirectBroaders() as $broaderAxis) {
            $this->copyAxisAndMembersFromOrgaToDW($broaderAxis, $dwCube, $orgaFilters, $associationArray);
        }
    }

    /**
     * @param Granularity $granularity
     */
    public function resetGranularityDWCube(Granularity $granularity)
    {
        if (!$granularity->getCellsGenerateDWCubes()) {
            return;
        }

        // Ne pas mettre à jour les rapports des cellules alors qu'aucune modification n'a eu lieu.
        $this->eventDispatcher->removeListener(OrgaReportFactory::class, DWReport::class);

        $this->updateGranularityDWCubeLabel($granularity);
        $this->resetDWCube(
            $granularity->getDWCube(),
            $granularity->getWorkspace(),
            ['axes' => $granularity->getAxes()]
        );
    }

    /**
     * @param Cell $cell
     * @throws ErrorException
     * @throws Exception
     */
    public function resetCellDWCube(Cell $cell)
    {
        if (!$cell->getGranularity()->getCellsGenerateDWCubes()) {
            return;
        }

        try {
            // Début de transaction.
            $this->entityManager->beginTransaction();

            $this->etlDataService->clearDWResultsForCell($cell);
            $this->entityManager->flush();

            $this->updateCellDWCubeLabel($cell);
            $this->resetDWCube(
                $cell->getDWCube(),
                $cell->getGranularity()->getWorkspace(),
                [
                    'axes' => $cell->getGranularity()->getAxes(),
                    'members' => $cell->getMembers()
                ]
            );

            $this->etlDataService->populateDWResultsForCell($cell);
            $this->entityManager->flush();

            // Fin de transaction.
            $this->entityManager->commit();
        } catch (ErrorException $e) {
            // Annulation de la transaction.
            $this->entityManager->rollback();

            throw $e;
        }
    }

    /**
     * @param DWCube $dWCube
     * @param Workspace $orgaWorkspace
     * @param array $orgaFilter
     */
    private function resetDWCube(DWCube $dWCube, Workspace $orgaWorkspace, array $orgaFilter)
    {
        set_time_limit(0);

        $queryCube = new Core_Model_Query();
        $queryCube->filter->addCondition(DWReport::QUERY_CUBE, $dWCube);
        // Suppression des résultats.
        foreach (DWResult::loadList($queryCube) as $dWResult) {
            $dWResult->delete();
        }

        // Préparation à la copie des Reports.
        $dWReportsAsString = array();
        foreach (DWReport::loadList($queryCube) as $dWReport) {
            /** @var DWReport $dWReport */
            $dWReportsAsString[] = $this->reportService->getReportAsJson($dWReport);
            $dWReport->reset();
            $dWReport->save();
        }

        // Suppression des axes et indicateurs.
        foreach ($dWCube->getIndicators() as $dWIndicator) {
            $dWIndicator->delete();
        }
        foreach ($dWCube->getRootAxes() as $dWRootAxis) {
            $dWRootAxis->delete();
        }

        // Suppression des données du cube et vidage des Report.
        $this->entityManager->flush();

        $this->populateDWCube($dWCube, $orgaWorkspace, $orgaFilter);
        $dWCube->save();

        // Peuplement du cube effectif.
        $this->entityManager->flush();

        // Copie des Reports.
        foreach ($dWReportsAsString as $dWReportString) {
            try {
                $newReport = $this->reportService->getReportFromJson($dWReportString);
                $newReport->save();
            } catch (Core_Exception_NotFound $e) {
                // Le rapport n'est pas compatible avec la nouvelle version du cube.
            }
        }

        // Copie des rapports.
        $this->entityManager->flush();
    }
}
