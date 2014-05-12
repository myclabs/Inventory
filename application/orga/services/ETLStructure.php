<?php

use Classification\Domain\ContextIndicator;
use Classification\Domain\Indicator;
use Classification\Domain\Axis;
use Classification\Domain\Member;
use Doctrine\ORM\EntityManager;
use Gedmo\Translatable\Entity\Repository\TranslationRepository;
use Mnapoli\Translated\TranslationHelper;

/**
 * Classe permettant de construire les DW.
 *
 * @author valentin.claras
 */
class Orga_Service_ETLStructure
{
    /**
     * @var EntityManager
     */
    private $entityManager;

    /**
     * @var Orga_Service_ETLData
     */
    private $etlDataService;

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
     * @var TranslationHelper
     */
    private $translationHelper;


    /**
     * @param EntityManager        $entityManager
     * @param Orga_Service_ETLData $etlDataService
     * @param Core_EventDispatcher $eventDispatcher
     * @param string               $defaultLocale
     * @param string[]             $locales
     * @param TranslationHelper    $translationHelper
     */
    public function __construct(
        EntityManager $entityManager,
        Orga_Service_ETLData $etlDataService,
        Core_EventDispatcher $eventDispatcher,
        $defaultLocale,
        array $locales,
        TranslationHelper $translationHelper
    ) {
        $this->entityManager = $entityManager;
        $this->etlDataService = $etlDataService;
        $this->eventDispatcher = $eventDispatcher;
        $this->defaultLocale = $defaultLocale;
        $this->locales = $locales;
        $this->translationHelper = $translationHelper;
    }

    /**
     * Traduit les labels des objets originaux dans DW.
     *
     * @param Indicator|Axis|Member|Orga_Model_Axis|Orga_Model_Member $originalEntity
     * @param DW_Model_Indicator|DW_Model_Axis|DW_Model_Member $dWEntity
     */
    protected function translateEntity($originalEntity, $dWEntity)
    {
        /** @var $translationRepository TranslationRepository */
        $translationRepository = $this->entityManager->getRepository(\Gedmo\Translatable\Entity\Translation::class);

        $originalTranslations = $translationRepository->findTranslations($originalEntity);

        if (isset($originalTranslations[$this->defaultLocale])) {
            $dWEntity->setLabel($originalTranslations[$this->defaultLocale]);
        } else {
            $dWEntity->setLabel($originalEntity->getLabel());
        }
        // Traductions.
        foreach ($this->locales as $localeId) {
            if (isset($originalTranslations[$localeId]['label'])) {
                $translationRepository->translate(
                    $dWEntity,
                    'label',
                    $localeId,
                    $originalTranslations[$localeId]['label']
                );
            }
        }
    }

    /**
     * Vérifie que les traductions sont à jour entre les objets originaux et ceux de DW.
     *
     * @param Indicator|Axis|Member|Orga_Model_Axis|Orga_Model_Member $originalEntity
     * @param DW_Model_Indicator|DW_Model_Axis|DW_Model_Member $dWEntity
     *
     * @return bool
     */
    protected function areTranslationsDifferent($originalEntity, $dWEntity)
    {
        /** @var $translationRepository TranslationRepository */
        $translationRepository = $this->entityManager->getRepository(\Gedmo\Translatable\Entity\Translation::class);

        $originalTranslations = $translationRepository->findTranslations($originalEntity);
        $dWTranslations = $translationRepository->findTranslations($dWEntity);

        // Traductions
        foreach ($this->locales as $localeId) {
            if (isset($originalTranslations[$localeId])) {
                $originalLabel = $originalTranslations[$localeId]['label'];
            } else {
                $originalLabel = '';
            }
            if (isset($dWTranslations[$localeId])) {
                $dWLabel = $dWTranslations[$localeId]['label'];
            } else {
                $dWLabel = '';
            }

            if ($originalLabel != $dWLabel) {
                return true;
            }
        }

        return false;
    }


    /**
     * Peuple le cube de DW avec les données issues de Classification et Orga.
     *
     * @param Orga_Model_Cell $cell
     */
    public function populateCellDWCube(Orga_Model_Cell $cell)
    {
        $this->updateCellDWCubeLabel($cell);
        $this->populateDWCubeWithClassificationAndOrga(
            $cell->getDWCube(),
            $cell->getGranularity()->getOrganization(),
            ['axes' => $cell->getGranularity()->getAxes(), 'members' => $cell->getMembers()]
        );
    }

    /**
     * Peuple le cube de DW avec les données issues de Classification et Orga.
     *
     * @param Orga_Model_Granularity $granularity
     */
    public function populateGranularityDWCube(Orga_Model_Granularity $granularity)
    {
        $this->updateGranularityDWCubeLabel($granularity);
        $this->populateDWCubeWithClassificationAndOrga(
            $granularity->getDWCube(),
            $granularity->getOrganization(),
            ['axes' => $granularity->getAxes()]
        );
    }

    /**
     * Met à jour les labels du Cube de DW de la Cell donnée.
     *
     * @param Orga_Model_Cell $cell
     */
    protected function updateCellDWCubeLabel(Orga_Model_Cell $cell)
    {
        /** @var $translationRepository TranslationRepository */
        $translationRepository = $this->entityManager->getRepository(\Gedmo\Translatable\Entity\Translation::class);

        $labels = [];
        if (!$cell->hasMembers()) {
            foreach ($this->locales as $localeId) {
                $labels[$localeId] = __('Orga', 'navigation', 'labelGlobalCell', [], $localeId);
            }
        } else {
            foreach ($this->locales as $localeId) {
                $labelParts = [];
                foreach ($cell->getMembers() as $member) {
                    $originalTranslations = $translationRepository->findTranslations($member);
                    if (isset($originalTranslations[$localeId])) {
                        $labelParts[] = $originalTranslations[$localeId]['label'];
                    } elseif (isset($originalTranslations[$this->defaultLocale])) {
                        $labelParts[] = $originalTranslations[$this->defaultLocale]['label'];
                    } else {
                        $labelParts[] = $this->translationHelper->toString($member->getLabel());
                    }
                }
                $labels[$localeId] = implode(Orga_Model_Cell::LABEL_SEPARATOR, $labelParts);
            }
        }

        $this->updateDWCubeLabel($cell->getDWCube(), $labels);
    }

    /**
     * Met à jour les labels du Cube de DW de la Cell donnée.
     *
     * @param Orga_Model_Granularity $granularity
     */
    protected function updateGranularityDWCubeLabel(Orga_Model_Granularity $granularity)
    {
        /** @var $translationRepository TranslationRepository */
        $translationRepository = $this->entityManager->getRepository(\Gedmo\Translatable\Entity\Translation::class);

        $labels = [];
        if (!$granularity->hasAxes()) {
            foreach ($this->locales as $localeId) {
                $labels[$localeId] = __('Orga', 'navigation', 'labelGlobalCell', [], $localeId);
            }
        } else {
            $axes = $granularity->getAxes();
            // Suppression des erreurs avec '@' dans le cas ou des proxies sont utilisées.
            @uasort($axes, [Orga_Model_Axis::class, 'orderAxes']);
            foreach ($this->locales as $localeId) {
                $labelParts = [];
                foreach ($axes as $axis) {
                    $originalTranslations = $translationRepository->findTranslations($axis);
                    if (isset($originalTranslations[$localeId])) {
                        $labelParts[] = $originalTranslations[$localeId]['label'];
                    } elseif (isset($originalTranslations[$this->defaultLocale])) {
                        $labelParts[] = $originalTranslations[$this->defaultLocale]['label'];
                    } else {
                        $labelParts[] = $this->translationHelper->toString($axis->getLabel());
                    }
                }
                $labels[$localeId] = implode(Orga_Model_Granularity::LABEL_SEPARATOR, $labelParts);
            }
        }

        $this->updateDWCubeLabel($granularity->getDWCube(), $labels);
    }

    /**
     * Met à jour les labels d'un Cube de DW donné.
     *
     * @param DW_Model_Cube $dWCube
     * @param array $labels
     */
    protected function updateDWCubeLabel(DW_Model_Cube $dWCube, $labels)
    {
        $this->translationHelper->setMany($dWCube->getLabel(), $labels);
    }

    /**
     * Peuple le cube de DW avec les données issues de Classification et Orga.
     *
     * @param DW_Model_Cube $dWCube
     * @param Orga_Model_Organization $orgaOrganization
     * @param array $orgaFilters
     */
    protected function populateDWCubeWithClassificationAndOrga($dWCube, $orgaOrganization, array $orgaFilters)
    {
        $this->populateDWCubeWithOrgaOrganization($dWCube, $orgaOrganization, $orgaFilters);
        $this->populateDWCubeWithClassification($dWCube, $orgaOrganization);
        $this->populateDWCubeWithAF($dWCube);
    }

    /**
     * Peuple le cube de DW avec un axe indiquant le status de l'AF.
     *
     * @param DW_Model_Cube $dWCube
     */
    protected function populateDWCubeWithAF(DW_Model_Cube $dWCube)
    {
        /** @var $translationRepository TranslationRepository */
        $translationRepository = $this->entityManager->getRepository(Gedmo\Translatable\Entity\Translation::class);

        $inputStatusDWAxis = new DW_Model_Axis($dWCube);
        $inputStatusDWAxis->setRef('inputStatus');

        $finishedDWMember = new DW_Model_Member($inputStatusDWAxis);
        $finishedDWMember->setRef('finished');

        $completedDWMember = new DW_Model_Member($inputStatusDWAxis);
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

            $translationRepository->translate($inputStatusDWAxis, 'label', $localeId, $inputStatusLabel);
            $translationRepository->translate($finishedDWMember, 'label', $localeId, $finishedLabel);
            $translationRepository->translate($completedDWMember, 'label', $localeId, $completedLabel);
        }
    }

    /**
     * Peuple le cube de DW avec les données issues de Classification.
     *
     * @param DW_Model_Cube $dWCube
     * @param Orga_Model_Organization $orgaOrganization
     */
    protected function populateDWCubeWithClassification(DW_Model_Cube $dWCube, Orga_Model_Organization $orgaOrganization)
    {
        $classificationIndicators = array_map(
            function ($contextIndicator) { return $contextIndicator->getIndicator(); },
            $orgaOrganization->getContextIndicators()->toArray()
        );
        $classificationIndicators = array_unique($classificationIndicators);
        foreach ($classificationIndicators as $classificationIndicator) {
            /** @var ContextIndicator $classificationContextIndicator */
            $this->copyIndicatorFromClassificationToDWCube($classificationIndicator, $dWCube);
        }

        foreach ($orgaOrganization->getClassificationAxes() as $classificationAxis) {
            $this->copyAxisAndMembersFromClassificationToDW($classificationAxis, $dWCube);
        }
    }

    /**
     * Copie un indicateur de Classification dans un cube de DW.
     *
     * @param Indicator $classificationIndicator
     * @param DW_Model_Cube $dWCube
     */
    protected function copyIndicatorFromClassificationToDWCube($classificationIndicator, $dWCube)
    {
        $dWIndicator = new DW_Model_Indicator($dWCube);
        $dWIndicator->setRef($classificationIndicator->getLibrary()->getId().'_'.$classificationIndicator->getRef());
        $dWIndicator->setUnit($classificationIndicator->getUnit());
        $dWIndicator->setRatioUnit($classificationIndicator->getRatioUnit());
        $this->translateEntity($classificationIndicator, $dWIndicator);
    }

    /**
     * Copie un axe de Classification dans un cube DW.
     *
     * @param Axis $classificationAxis
     * @param DW_Model_Cube $dwCube
     * @param array &$associationArray
     */
    protected function copyAxisAndMembersFromClassificationToDW($classificationAxis, $dwCube, & $associationArray = [])
    {
        $dWAxis = new DW_Model_Axis($dwCube);
        $dWAxis->setRef('c_'.$classificationAxis->getLibrary()->getId().'_'.$classificationAxis->getRef());
        $this->translateEntity($classificationAxis, $dWAxis);

        $associationArray['axes'][$classificationAxis->getRef()] = $dWAxis;
        $narrowerAxis = $classificationAxis->getDirectNarrower();
        if ($narrowerAxis !== null) {
            $dWAxis->setDirectNarrower($associationArray['axes'][$narrowerAxis->getRef()]);
        }

        foreach ($classificationAxis->getMembers() as $classificationMember) {
            $dWMember = new DW_Model_Member($dWAxis);
            $dWMember->setRef($classificationMember->getRef());
            $dWMember->setPosition($classificationMember->getPosition());
            $this->translateEntity($classificationMember, $dWMember);

            $memberIdentifier = $classificationMember->getAxis()->getRef().'_'.$classificationMember->getRef();
            $associationArray['members'][$memberIdentifier] = $dWMember;
            foreach ($classificationMember->getDirectChildren() as $narrowerClassificationMember) {
                $narrowerIdentifier = $narrowerClassificationMember->getAxis()->getRef().'_'.$narrowerClassificationMember->getRef();
                $dWMember->addDirectChild($associationArray['members'][$narrowerIdentifier]);
            }
        }

        foreach ($classificationAxis->getDirectBroaders() as $broaderClassificationAxis) {
            $this->copyAxisAndMembersFromClassificationToDW($broaderClassificationAxis, $dwCube, $associationArray);
        }
    }

    /**
     * Peuple le cube de DW avec les données issues de Classification.
     *
     * @param DW_Model_Cube $dWCube
     * @param Orga_Model_Organization $orgaOrganization
     * @param array $orgaFilters
     */
    protected function populateDWCubeWithOrgaOrganization($dWCube, $orgaOrganization, $orgaFilters)
    {
        foreach ($orgaOrganization->getRootAxes() as $orgaAxis) {
            $this->copyAxisAndMembersFromOrgaToDW($orgaAxis, $dWCube, $orgaFilters);
        }
    }

    /**
     * Copie un axe de Orga dans un cube DW.
     *
     * @param Orga_Model_Axis $orgaAxis
     * @param DW_Model_Cube $dwCube
     * @param array $orgaFilters
     * @param array &$associationArray
     */
    protected function copyAxisAndMembersFromOrgaToDW($orgaAxis, $dwCube, $orgaFilters, & $associationArray = [])
    {
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

        $dWAxis = new DW_Model_Axis($dwCube);
        $dWAxis->setRef('o_'.$orgaAxis->getRef());
        $this->translateEntity($orgaAxis, $dWAxis);

        $associationArray['axes'][$orgaAxis->getRef()] = $dWAxis;
        $narrowerAxis = $orgaAxis->getDirectNarrower();
        if ($narrowerAxis !== null) {
            $dWAxis->setDirectNarrower($associationArray['axes'][$narrowerAxis->getRef()]);
        }

        foreach ($orgaAxis->getOrderedMembers() as $orgaMember) {
            if (isset($orgaFilters['members'])) {
                foreach ($filteringOrgaBroaderAxes as $filteringOrgaAxis) {
                    foreach ($orgaFilters['members'] as $filteringOrgaMember) {
                        if (($filteringOrgaMember->getAxis() === $filteringOrgaAxis)
                            && (in_array($filteringOrgaMember, $orgaMember->getAllParents()))
                        ) {
                            continue 2;
                        }
                    }
                    continue 2;
                }
            }

            $dWMember = new DW_Model_Member($dWAxis);
            $dWMember->setRef($orgaMember->getRef());
            $this->translateEntity($orgaMember, $dWMember);

            $memberIdentifier = $orgaMember->getAxis()->getRef().'_'.$orgaMember->getCompleteRef();
            $associationArray['members'][$memberIdentifier] = $dWMember;
            foreach ($orgaMember->getDirectChildren()->toArray() as $narrowerOrgaMember) {
                $narrowerIdentifier = $narrowerOrgaMember->getAxis()->getRef().'_'
                    .$narrowerOrgaMember->getCompleteRef();
                if (isset($associationArray['members'][$narrowerIdentifier])) {
                    $dWMember->addDirectChild($associationArray['members'][$narrowerIdentifier]);
                }
            }
        }

        foreach ($orgaAxis->getDirectBroaders() as $broaderAxis) {
            $this->copyAxisAndMembersFromOrgaToDW($broaderAxis, $dwCube, $orgaFilters, $associationArray);
        }
    }

    /**
     * Copie un Report depuis le Cube de DW d'une granularité dans les Cubes de ses cellules.
     *
     * @param Orga_Model_GranularityReport $granularityReport
     */
    public function createCellsDWReportFromGranularityReport($granularityReport)
    {
        $granularity = Orga_Model_Granularity::loadByDWCube($granularityReport->getGranularityDWReport()->getCube());
        foreach ($granularity->getCells() as $cell) {
            $this->copyGranularityReportToCellDWCube($granularityReport, $cell->getDWCube());
        }
    }

    /**
     * Mets à jour les Report des cubes des cellules copiés depuis le cube de la granularité.
     *
     * @param Orga_Model_GranularityReport $granularityReport
     */
    public function updateCellsDWReportFromGranularityReport($granularityReport)
    {
        $granularityReportAsString = $granularityReport->getGranularityDWReport()->getAsString();
        foreach ($granularityReport->getCellDWReports() as $cellDWReport) {
            $cellDWReportId = $cellDWReport->getId();
            $cellDWReportDWCubeId = $cellDWReport->getCube()->getId();
            $cellDWReport = DW_Model_Report::getFromString(
                preg_replace(
                    '#^(\{"id":)([0-9]+)(,"idCube":)([0-9]+)(,.+\})$#',
                    '${1}'.$cellDWReportId.'${3}'.$cellDWReportDWCubeId.'${5}',
                    $granularityReportAsString
                )
            );
            $this->translateEntity($granularityReport->getGranularityDWReport(), $cellDWReport);
            $this->entityManager->flush($cellDWReport);
        }
    }

    /**
     * Ajoute les Reports de la granularité au DW Cube d'une cellule.
     *
     * @param Orga_Model_Cell $cell
     */
    public function addGranularityDWReportsToCellDWCube(Orga_Model_Cell $cell)
    {
        foreach ($cell->getGranularity()->getDWCube()->getReports() as $granularityDWReport) {
            $granularityReport = Orga_Model_GranularityReport::loadByGranularityDWReport($granularityDWReport);
            $this->copyGranularityReportToCellDWCube($granularityReport, $cell->getDWCube());
        }
    }

    /**
     * Copie le Report d'un Cube de DW d'un Granularity dans le Cube d'un Cell.
     *
     * @param Orga_Model_GranularityReport $granularityReport
     * @param DW_Model_Cube                $dWCube
     */
    protected function copyGranularityReportToCellDWCube(
        Orga_Model_GranularityReport $granularityReport,
        DW_Model_Cube $dWCube
    ) {
        $reportCopy = $granularityReport->getGranularityDWReport()->copyToCube($dWCube);
        $granularityReport->addCellDWReport($reportCopy);
    }

    /**
     * Indique si les cubes de DW d'un projt donné est à jour vis à vis de données de Classification et Orga.
     *
     * @param Orga_Model_Organization $organization
     *
     * @return bool
     */
    public function areOrganizationDWCubesUpToDate(Orga_Model_Organization $organization)
    {
        foreach ($organization->getOrderedGranularities() as $granularity) {
            if ($granularity->getCellsGenerateDWCubes()) {
                if (!($this->isGranularityDWCubeUpToDate($granularity))) {
                    return false;
                }

                foreach ($granularity->getCells() as $cell) {
                    if (!($this->isCellDWCubeUpToDate($cell))) {
                        return false;
                    }
                }
            }
        }

        return true;
    }

    /**
     * Indique si le cube de DW d'un Granularity donné est à jour vis à vis des données de Classification et Orga.
     *
     * @param Orga_Model_Granularity $granularity
     *
     * @return bool
     */
    public function isGranularityDWCubeUpToDate($granularity)
    {
        return $this->isDWCubeUpToDate(
            $granularity->getDWCube(),
            $granularity->getOrganization(),
            [
                'axes' => $granularity->getAxes()
            ]
        );
    }

    /**
     * Indique si le cube de DW d'un Cell donné est à jour vis à vis des données de Classification et Orga.
     *
     * @param Orga_Model_Cell $cell
     *
     * @return bool
     */
    public function isCellDWCubeUpToDate($cell)
    {
        return $this->isDWCubeUpToDate(
            $cell->getDWCube(),
            $cell->getGranularity()->getOrganization(),
            [
                'axes' => $cell->getGranularity()->getAxes(),
                'members' => $cell->getMembers()
            ]
        );
    }

    /**
     * Indique les différences entre un cube de DW donné el les données de Classification et Orga.
     *
     * @param DW_Model_Cube $dWCube
     * @param Orga_Model_Organization $orgaOrganization
     * @param array $orgaFilters
     *
     * @return bool
     */
    protected function isDWCubeUpToDate($dWCube, $orgaOrganization, $orgaFilters)
    {
        return $this->areDWIndicatorsUpToDate($dWCube, $orgaOrganization)
            && $this->areDWAxesUpToDate($dWCube, $orgaOrganization, $orgaFilters);
    }

    /**
     * Compare les différences entre une liste d'indicateurs de DW et ceux de Classification.
     *
     * @param DW_Model_Cube $dWCube
     * @param Orga_Model_Organization $orgaOrganization
     *
     * @return bool
     */
    protected function areDWIndicatorsUpToDate($dWCube, $orgaOrganization)
    {
        $classificationIndicators = [];
        foreach ($orgaOrganization->getContextIndicators() as $classificationContextIndicator) {
            if (!in_array($classificationContextIndicator->getIndicator(), $classificationIndicators)) {
                $classificationIndicators[] = $classificationContextIndicator->getIndicator();
            }
        }
        $dWIndicators = $dWCube->getIndicators();

        foreach ($classificationIndicators as $classificationIndex => $classificationIndicator) {
            /** @var Indicator $classificationIndicator */
            foreach ($dWCube->getIndicators() as $dWIndex => $dWIndicator) {
                if (! $this->isDWIndicatorDifferentFromClassification($dWIndicator, $classificationIndicator)) {
                    unset($classificationIndicators[$classificationIndex]);
                    unset($dWIndicators[$dWIndex]);
                }
            }
        }

        if ((count($classificationIndicators) > 0) || (count($dWIndicators) > 0)) {
            return false;
        }
        return true;
    }

    /**
     * Compare les différences entre une liste d'indicateurs de DW et ceux de Classification.
     *
     * @param DW_Model_Indicator $dWIndicator
     * @param Indicator $classificationIndicator
     *
     * @return bool
     */
    protected function isDWIndicatorDifferentFromClassification($dWIndicator, $classificationIndicator)
    {
        if (($classificationIndicator->getRef() !== $dWIndicator->getRef())
            || ($classificationIndicator->getLibrary()->getId().'_'.$classificationIndicator->getUnit()->getRef()
                !== $dWIndicator->getUnit()->getRef())
            || ($classificationIndicator->getRatioUnit()->getRef() !== $dWIndicator->getRatioUnit()->getRef())
            || ($this->areTranslationsDifferent($classificationIndicator, $dWIndicator))
        ) {
            return true;
        }

        return false;
    }

    /**
     * Compare les différences entre une liste d'indicateurs de DW et ceux de Classification.
     *
     * @param DW_Model_Cube $dWCube
     * @param Orga_Model_Organization $orgaOrganization
     * @param array $orgaFilters
     *
     * @return bool
     */
    protected function areDWAxesUpToDate($dWCube, $orgaOrganization, $orgaFilters)
    {
        $dWRootAxes = $dWCube->getRootAxes();

        // Classification.
        $classificationRootAxes = $orgaOrganization->getClassificationAxes();
        foreach ($orgaOrganization->getClassificationAxes() as $classificationIndex => $classificationAxis) {
            /** @var Axis $classificationAxis */
            foreach ($dWCube->getRootAxes() as $dWIndex => $dWAxis) {
                if (!($this->isDWAxisDifferentFromClassification($dWAxis, $classificationAxis))) {
                    unset($classificationRootAxes[$classificationIndex]);
                    unset($dWRootAxes[$dWIndex]);
                }
            }
        }

        // Orga.
        $orgaRootAxes = $orgaOrganization->getRootAxes();
        foreach ($orgaOrganization->getRootAxes() as $orgaIndex => $orgaAxis) {
            foreach ($dWCube->getRootAxes() as $dWIndex => $dWAxis) {
                if (!($this->isDWAxisDifferentFromOrga($dWAxis, $orgaAxis, $orgaFilters))) {
                    unset($orgaRootAxes[$orgaIndex]);
                    unset($dWRootAxes[$dWIndex]);
                }
            }
        }

        if ((count($classificationRootAxes) > 0) || (count($orgaRootAxes) > 0) || (count($dWRootAxes) > 1)) {
            return false;
        }

        return true;
    }

    /**
     * Compare un axe de DW et un de Classification.
     *
     * @param DW_Model_Axis $dWAxis
     * @param Axis $classificationAxis
     *
     * @return bool
     */
    protected function isDWAxisDifferentFromClassification($dWAxis, $classificationAxis)
    {
        if (('c_'.$classificationAxis->getRef() !== $dWAxis->getRef())
            || ((($classificationAxis->getDirectNarrower() !== null) || ($dWAxis->getDirectNarrower() !== null))
                && (($classificationAxis->getDirectNarrower() === null) || ($dWAxis->getDirectNarrower() === null)
                || ('c_'.$classificationAxis->getDirectNarrower()->getRef() !== $dWAxis->getDirectNarrower()->getRef())))
            || ($this->areTranslationsDifferent($classificationAxis, $dWAxis))
            || ($this->areDWMembersDifferentFromClassification($dWAxis, $classificationAxis))
        ) {
            return true;
        } else {
            $classificationBroaderAxes = $classificationAxis->getDirectBroaders();
            $dWBroaderAxes = $dWAxis->getDirectBroaders();

            foreach ($classificationAxis->getDirectBroaders() as $classificationIndex => $classificationBroaderAxis) {
                foreach ($dWAxis->getDirectBroaders() as $dWIndex => $dWBroaderAxis) {
                    if (!($this->isDWAxisDifferentFromClassification($dWBroaderAxis, $classificationBroaderAxis))) {
                        unset($classificationBroaderAxes[$classificationIndex]);
                        unset($dWBroaderAxes[$dWIndex]);
                    }
                }
            }

            if ((count($classificationBroaderAxes) > 0) || (count($dWBroaderAxes) > 0)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Compare un membre de DW et un de Classification.
     *
     * @param DW_Model_Axis $dWAxis
     * @param Axis $classificationAxis
     *
     * @return bool
     */
    protected function areDWMembersDifferentFromClassification($dWAxis, $classificationAxis)
    {
        $classificationMembers = $classificationAxis->getMembers();
        $dWMembers = $dWAxis->getMembers();

        foreach ($classificationAxis->getMembers() as $classificationIndex => $classificationMember) {
            foreach ($dWAxis->getMembers() as $dWIndex => $dWMember) {
                if (!($this->isDWMemberDifferentFromClassification($dWMember, $classificationMember))) {
                    unset($classificationMembers[$classificationIndex]);
                    unset($dWMembers[$dWIndex]);
                }
            }
        }

        if ((count($classificationMembers) > 0) || (count($dWMembers) > 0)) {
            return true;
        }

        return false;
    }

    /**
     * Compare un membre de DW et un de Classification.
     *
     * @param DW_Model_Member $dWMember
     * @param Member $classificationMember
     *
     * @return bool
     */
    protected function isDWMemberDifferentFromClassification($dWMember, $classificationMember)
    {
        if (($classificationMember->getRef() !== $dWMember->getRef())
            || ($this->areTranslationsDifferent($classificationMember, $dWMember))
        ) {
            return true;
        } else {
            $classificationParentMembers = $classificationMember->getDirectParents();
            $dWParentMembers = $dWMember->getDirectParents();

            foreach ($classificationMember->getDirectParents() as $classificationIndex => $classificationParentMember) {
                foreach ($dWMember->getDirectParents() as $dWIndex => $dWParentMember) {
                    if ($classificationParentMember->getRef() === $dWParentMember->getRef()) {
                        unset($classificationParentMembers[$classificationIndex]);
                        unset($dWParentMembers[$dWIndex]);
                    }
                }
            }

            if ((count($classificationParentMembers) > 0) || (count($dWParentMembers) > 0)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Compare un axe de DW et un de Orga.
     *
     * @param DW_Model_Axis $dWAxis
     * @param Orga_Model_Axis $orgaAxis
     * @param array $orgaFilters
     *
     * @return bool
     */
    protected function isDWAxisDifferentFromOrga($dWAxis, $orgaAxis, $orgaFilters)
    {
        if (in_array($orgaAxis, $orgaFilters['axes'])) {
            return false;
        }

        if (('o_'.$orgaAxis->getRef() !== $dWAxis->getRef())
            || ((($orgaAxis->getDirectNarrower() !== null) || ($dWAxis->getDirectNarrower() !== null))
                && (($orgaAxis->getDirectNarrower() === null) || ($dWAxis->getDirectNarrower() === null)
                || ('o_'.$orgaAxis->getDirectNarrower()->getRef() !== $dWAxis->getDirectNarrower()->getRef())))
            || ($this->areTranslationsDifferent($orgaAxis, $dWAxis))
            || ($this->areDWMembersDifferentFromOrga($dWAxis, $orgaAxis, $orgaFilters))
        ) {
            return true;
        } else {
            $orgaBroaderAxes = $orgaAxis->getDirectBroaders();
            $dWBroaderAxes = $dWAxis->getDirectBroaders();

            foreach ($orgaAxis->getDirectBroaders() as $orgaIndex => $orgaBroaderAxis) {
                if (in_array($orgaBroaderAxis, $orgaFilters['axes'])) {
                    unset($orgaBroaderAxes[$orgaIndex]);
                    continue;
                }
                foreach ($dWAxis->getDirectBroaders() as $dWIndex => $dWBroaderAxis) {
                    if (!($this->isDWAxisDifferentFromOrga($dWBroaderAxis, $orgaBroaderAxis, $orgaFilters))) {
                        unset($orgaBroaderAxes[$orgaIndex]);
                        unset($dWBroaderAxes[$dWIndex]);
                    }
                }
            }

            if ((count($orgaBroaderAxes) > 0) || (count($dWBroaderAxes) > 0)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Compare un membre de DW et un de Orga.
     *
     * @param DW_Model_Axis $dWAxis
     * @param Orga_Model_Axis $orgaAxis
     * @param array $orgaFilters
     *
     * @return bool
     */
    protected function areDWMembersDifferentFromOrga($dWAxis, $orgaAxis, $orgaFilters)
    {
        $filteringOrgaBroaderAxes = array();
        foreach ($orgaFilters['axes'] as $filteringOrgaAxis) {
            if ($orgaAxis->isNarrowerThan($filteringOrgaAxis)) {
                $filteringOrgaBroaderAxes[] = $filteringOrgaAxis;
            }
        }

        $orgaMembers = $orgaAxis->getOrderedMembers()->toArray();
        $dWMembers = $dWAxis->getMembers();

        foreach ($orgaAxis->getOrderedMembers()->toArray() as $orgaIndex => $orgaMember) {
            if (isset($orgaFilters['members'])) {
                foreach ($filteringOrgaBroaderAxes as $filteringOrgaAxis) {
                    foreach ($orgaFilters['members'] as $filteringOrgaMember) {
                        if (($filteringOrgaMember->getAxis() === $filteringOrgaAxis)
                            && (in_array($filteringOrgaMember, $orgaMember->getAllParents()))
                        ) {
                            continue 2;
                        }
                    }
                    unset($orgaMembers[$orgaIndex]);
                    continue 2;
                }
            }

            foreach ($dWAxis->getMembers() as $dWIndex => $dWMember) {
                if (!($this->isDWMemberDifferentFromOrga($dWMember, $orgaMember, $orgaFilters))) {
                    unset($orgaMembers[$orgaIndex]);
                    unset($dWMembers[$dWIndex]);
                }
            }
        }

        if ((count($orgaMembers) > 0) || (count($dWMembers) > 0)) {
            return true;
        }

        return false;
    }

    /**
     * Compare un membre de DW et un de Orga.
     *
     * @param DW_Model_Member $dWMember
     * @param Orga_Model_Member $orgaMember
     * @param array $orgaFilters
     *
     * @return bool
     */
    protected function isDWMemberDifferentFromOrga($dWMember, $orgaMember, $orgaFilters)
    {
        if (($orgaMember->getRef() !== $dWMember->getRef())
            || ($this->areTranslationsDifferent($dWMember, $orgaMember))
        ) {
            return true;
        } else {
            $orgaParentMembers = $orgaMember->getDirectParents()->toArray();
            $dWParentMembers = $dWMember->getDirectParents();

            foreach ($orgaMember->getDirectParents() as $index => $orgaParentMember) {
                if (in_array($orgaParentMember->getAxis(), $orgaFilters['axes'])) {
                    unset($orgaParentMembers[$index]);
                    continue;
                }

                foreach ($dWMember->getDirectParents() as $dWIndex => $dWParentMember) {
                    if ($orgaParentMember->getRef() === $dWParentMember->getRef()) {
                        unset($orgaParentMembers[$index]);
                        unset($dWParentMembers[$dWIndex]);
                    }
                }
            }

            if ((count($orgaParentMembers) > 0) || (count($dWParentMembers) > 0)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Réinitialise les cubes de DW des Cell d'un projet donné.
     *
     * @param Orga_Model_Organization $organization
     */
    public function resetOrganizationDWCubes(Orga_Model_Organization $organization)
    {
        foreach ($organization->getOrderedGranularities() as $granularity) {
            // Optimisation de la mémoire.
            $this->entityManager->clear();
            $granularity = Orga_Model_Granularity::load($granularity->getId());

            if ($granularity->getCellsGenerateDWCubes()) {
                $this->resetGranularityAndCellsDWCubes($granularity);
            }
        }
    }

    /**
     * Réinitialise le cube de DW d'un Granularity.
     *
     * @param Orga_Model_Granularity $granularity
     */
    public function resetGranularityAndCellsDWCubes(Orga_Model_Granularity $granularity)
    {
        $this->eventDispatcher->removeListener(Orga_Service_Report::class, DW_Model_Report::class);

        foreach ($granularity->getCells() as $cell) {
            $cell = Orga_Model_Cell::load($cell->getId());
            $this->resetCellDWCube($cell);

            // Optimisation de la mémoire.
            $this->entityManager->clear();
        }

        $granularity = Orga_Model_Granularity::load($granularity->getId());
        $this->updateGranularityDWCubeLabel($granularity);
        $this->resetDWCube(
            $granularity->getDWCube(),
            $granularity->getOrganization(),
            [ 'axes' => $granularity->getAxes() ]
        );
    }

    /**
     * Régénère les cubes de données ET (PUIS) relance les calculs des formulaires comptables d'une cellule
     * ET de toutes ses sous-cellules.
     *
     * @param Orga_Model_Cell $cell
     */
    public function resetCellAndChildrenCalculationsAndDWCubes(Orga_Model_Cell $cell)
    {
        $this->etlDataService->calculateResultsForCellAndChildren($cell);
        $this->resetCellAndChildrenDWCubes($cell);
    }

    /**
     * Régénère les cubes de données d'une cellule et de toutes ses sous-cellules.
     * Ne relance PAS les calculs des formulaires comptables.
     *
     * @param Orga_Model_Cell $cell
     */
    public function resetCellAndChildrenDWCubes(Orga_Model_Cell $cell)
    {
        $this->resetCellDWCube($cell);

        foreach ($cell->getChildCells() as $childCell) {
            // Optimisation de la mémoire.
            $this->entityManager->clear();
            $childCell = Orga_Model_Cell::load($childCell->getId());

            $this->resetCellDWCube($childCell);
        }
    }

    /**
     * Régénère la structure du Cube de DW d'une cellule.
     *
     * @param Orga_Model_Cell $cell
     * @throws ErrorException
     * @throws Exception
     */
    public function resetCellDWCube(Orga_Model_Cell $cell)
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
                $cell->getGranularity()->getOrganization(),
                array(
                    'axes' => $cell->getGranularity()->getAxes(),
                    'members' => $cell->getMembers()
                )
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
     * Réinitialise un cube de DW donné.
     *
     * @param DW_Model_Cube $dWCube
     * @param Orga_Model_Organization $orgaOrganization
     * @param array $orgaFilter
     */
    protected function resetDWCube(DW_Model_Cube $dWCube, Orga_Model_Organization $orgaOrganization, array $orgaFilter)
    {
        set_time_limit(0);

        $queryCube = new Core_Model_Query();
        $queryCube->filter->addCondition(DW_Model_Report::QUERY_CUBE, $dWCube);
        // Suppression des résultats.
        foreach (DW_Model_Result::loadList($queryCube) as $dWResult) {
            $dWResult->delete();
        }

        // Préparation à la copie des Reports.
        $dWReportsAsString = array();
        foreach (DW_Model_Report::loadList($queryCube) as $dWReport) {
            /** @var DW_Model_Report $dWReport */
            $dWReportsAsString[] = $dWReport->getAsString();
            // TODO http://42lareponse.fr/wp-content/uploads/2013/05/what-the-fuck-is-this1.jpg
            $emptyDWReportString = '{'.
                '"id":'.$dWReport->getKey()['id'].',"idCube":'.$dWCube->getId().',"label":"",'.
                '"refNumerator":null,"refNumeratorAxis1":null,"refNumeratorAxis2":null,'.
                '"refDenominator":null,"refDenominatorAxis1":null,"refDenominatorAxis2":null,'.
                '"chartType":null,"sortType":"orderResultByDecreasingValue","withUncertainty":false,'.
                '"filters":[]'.
            '}';
            $dWReportReset = DW_Model_Report::getFromString($emptyDWReportString);
            $dWReportReset->save();
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

        $this->populateDWCubeWithClassificationAndOrga($dWCube, $orgaOrganization, $orgaFilter);
        $dWCube->save();

        // Peuplement du cube effectif.
        $this->entityManager->flush();

        // Copie des Reports.
        foreach ($dWReportsAsString as $dWReportString) {
            try {
                $newReport = DW_Model_Report::getFromString($dWReportString);
                $newReport->save();
            } catch (Core_Exception_NotFound $e) {
                // Le rapport n'est pas compatible avec la nouvelle version du cube.
            }
        }

        // Copie des rapports.
        $this->entityManager->flush();
    }
}
