<?php
/**
 * @package Orga
 * @subpackage Service
 */

use Doctrine\ORM\EntityManager;

/**
 * Classe permettant de construire les DW.
 * @author valentin.claras
 * @package Orga
 * @subpackage Service
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
     * @param EntityManager $entityManager
     * @param Orga_Service_ETLData $etlDataService
     */
    public function __construct(EntityManager $entityManager, Orga_Service_ETLData $etlDataService)
    {
        $this->entityManager = $entityManager;
        $this->etlDataService = $etlDataService;
    }

    /**
     * Traduit les labels des objets originaux dans DW.
     *
     * @param Classif_Model_Indicator|Classif_Model_Axis|Classif_Model_Member|Orga_Model_Axis|Orga_Model_Member $originalEntity
     * @param DW_Model_Indicator|DW_Model_Axis|DW_Model_Member $dWEntity
     */
    protected function translateEntity($originalEntity, $dWEntity)
    {
        /** @var $translationRepository \Gedmo\Translatable\Entity\Repository\TranslationRepository */
        $translationRepository = $this->entityManager->getRepository('Gedmo\Translatable\Entity\Translation');
        $defaultLocale = Zend_Registry::get('configuration')->translation->defaultLocale;

        $originalTranslations = $translationRepository->findTranslations($originalEntity);

        if (isset($originalTranslations[$defaultLocale])) {
            $dWEntity->setLabel($originalTranslations[$defaultLocale]);
        } else {
            $dWEntity->setLabel($originalEntity->getLabel());
        }
        // Traductions.
        foreach (Zend_Registry::get('languages') as $localeId) {
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
     * @param Classif_Model_Indicator|Classif_Model_Axis|Classif_Model_Member|Orga_Model_Axis|Orga_Model_Member $originalEntity
     * @param DW_Model_Indicator|DW_Model_Axis|DW_Model_Member $dWEntity
     *
     * @return bool
     */
    protected function areTranslationsDifferent($originalEntity, $dWEntity)
    {
        /** @var $translationRepository \Gedmo\Translatable\Entity\Repository\TranslationRepository */
        $translationRepository = $this->entityManager->getRepository('Gedmo\Translatable\Entity\Translation');

        $originalTranslations = $translationRepository->findTranslations($originalEntity);
        $dWTranslations = $translationRepository->findTranslations($dWEntity);

        // Traductions
        foreach (Zend_Registry::get('languages') as $localeId) {
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

            if($originalLabel != $dWLabel) {
                return true;
            }
        }

        return false;
    }


    /**
     * Peuple le cube de DW avec les données issues de Classif et Orga.
     *
     * @param Orga_Model_Cell $cell
     */
    public function populateCellDWCube(Orga_Model_Cell $cell)
    {
        $this->updateCellDWCubeLabel($cell);
        $this->populateDWCubeWithClassifAndOrga(
            $cell->getDWCube(),
            $cell->getGranularity()->getOrganization(),
            ['axes' => $cell->getGranularity()->getAxes(), 'members' => $cell->getMembers()]
        );
    }

    /**
     * Peuple le cube de DW avec les données issues de Classif et Orga.
     *
     * @param Orga_Model_Granularity $granularity
     */
    public function populateGranularityDWCube(Orga_Model_Granularity $granularity)
    {
        $this->updateGranularityDWCubeLabel($granularity);
        $this->populateDWCubeWithClassifAndOrga(
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
        /** @var $translationRepository \Gedmo\Translatable\Entity\Repository\TranslationRepository */
        $translationRepository = $this->entityManager->getRepository('Gedmo\Translatable\Entity\Translation');
        $defaultLocale = Zend_Registry::get('configuration')->translation->defaultLocale;

        $labels = [];
        if (!$cell->hasMembers()) {
            foreach (Zend_Registry::get('languages') as $localeId) {
                $labels[$localeId] = Core_Translate::get('Orga', 'navigation', 'labelGlobalCell', [], $localeId);
            }
        } else {
            foreach (Zend_Registry::get('languages') as $localeId) {
                $labelParts = [];
                foreach ($cell->getMembers() as $member) {
                    $originalTranslations = $translationRepository->findTranslations($member);
                    if (isset($originalTranslations[$localeId])) {
                        $labelParts[] = $originalTranslations[$localeId]['label'];
                    } elseif (isset($originalTranslations[$defaultLocale])) {
                        $labelParts[] = $originalTranslations[$defaultLocale]['label'];
                    } else {
                        $labelParts[] = $member->getLabel();
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
        /** @var $translationRepository \Gedmo\Translatable\Entity\Repository\TranslationRepository */
        $translationRepository = $this->entityManager->getRepository('Gedmo\Translatable\Entity\Translation');
        $defaultLocale = Zend_Registry::get('configuration')->translation->defaultLocale;

        $labels = [];
        if (!$granularity->hasAxes()) {
            foreach (Zend_Registry::get('languages') as $localeId) {
                $labels[$localeId] = Core_Translate::get('Orga', 'navigation', 'labelGlobalCell', [], $localeId);
            }
        } else {
            $axes = $granularity->getAxes();
            // Suppression des erreurs avec '@' dans le cas ou des proxies sont utilisées.
            @uasort($axes, array('Orga_Model_Axis', 'orderAxes'));
            foreach (Zend_Registry::get('languages') as $localeId) {
                $labelParts = [];
                foreach ($axes as $axis) {
                    $originalTranslations = $translationRepository->findTranslations($axis);
                    if (isset($originalTranslations[$localeId])) {
                        $labelParts[] = $originalTranslations[$localeId]['label'];
                    } elseif (isset($originalTranslations[$defaultLocale])) {
                        $labelParts[] = $originalTranslations[$defaultLocale]['label'];
                    } else {
                        $labelParts[] = $axis->getLabel();
                    }
                }
                $labels[$localeId] = implode(Orga_Model_Granularity::LABEL_SEPARATOR, $labelParts);
            }
        }

        $this->updateDWCubeLabel($granularity->getDWCube(), $labels);
    }

    /**
     * Met à jour les labels d'um Cube de DW donné.
     *
     * @param DW_Model_Cube $dWCube
     * @param array $labels
     */
    protected function updateDWCubeLabel(DW_Model_Cube $dWCube, $labels)
    {
        $dWCube->getLabel();
        /** @var $translationRepository \Gedmo\Translatable\Entity\Repository\TranslationRepository */
        $translationRepository = $this->entityManager->getRepository('Gedmo\Translatable\Entity\Translation');
        foreach ($labels as $localeId => $label) {
            $translationRepository->translate($dWCube, 'label', $localeId, $label);
        }
    }

    /**
     * Peuple le cube de DW avec les données issues de Classif et Orga.
     *
     * @param DW_Model_Cube $dWCube
     * @param Orga_Model_Organization $orgaOrganization
     * @param array $orgaFilters
     */
    protected function populateDWCubeWithClassifAndOrga($dWCube, $orgaOrganization, array $orgaFilters)
    {
        $this->populateDWCubeWithOrgaOrganization($dWCube, $orgaOrganization, $orgaFilters);
        $this->populateDWCubeWithClassif($dWCube);
        $this->populateDWCubeWithAF($dWCube);
    }

    /**
     * Peuple le cube de DW avec un axe indiquant le status de l'AF.
     *
     * @param DW_Model_Cube $dWCube
     */
    protected function populateDWCubeWithAF(DW_Model_Cube $dWCube)
    {
        /** @var $translationRepository \Gedmo\Translatable\Entity\Repository\TranslationRepository */
        $translationRepository = $this->entityManager->getRepository('Gedmo\Translatable\Entity\Translation');

        $inputStatusDWAxis = new DW_Model_Axis($dWCube);
        $inputStatusDWAxis->setRef('inputStatus');

        $finishedDWMember = new DW_Model_Member($inputStatusDWAxis);
        $finishedDWMember->setRef('finished');

        $completedDWMember = new DW_Model_Member($inputStatusDWAxis);
        $completedDWMember->setRef('completed');

        foreach (Zend_Registry::get('languages') as $localeId) {
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
     * Peuple le cube de DW avec les données issues de Classif.
     *
     * @param DW_Model_Cube $dWCube
     */
    protected function populateDWCubeWithClassif(DW_Model_Cube $dWCube)
    {
        $queryOrdered = new Core_Model_Query();
        $queryOrdered->order->addOrder(Classif_Model_Indicator::QUERY_POSITION);
        foreach (Classif_Model_Indicator::loadList($queryOrdered) as $classifIndicator) {
            /** @var Classif_Model_Indicator $classifIndicator */
            $this->copyIndicatorFromClassifToDWCube($classifIndicator, $dWCube);
        }

        $queryRootAxes = new Core_Model_Query();
        $queryRootAxes->filter->addCondition(
            Classif_Model_Axis::QUERY_NARROWER,
            null,
            Core_Model_Filter::OPERATOR_NULL
        );
        foreach (Classif_Model_Axis::loadList($queryRootAxes) as $classifAxis) {
            /** @var Classif_Model_Axis $classifAxis */
            $this->copyAxisAndMembersFromClassifToDW($classifAxis, $dWCube);
        }
    }

    /**
     * Copie un indicateur de Classif dans un cube de DW.
     *
     * @param Classif_Model_Indicator $classifIndicator
     * @param DW_Model_Cube $dWCube
     */
    protected function copyIndicatorFromClassifToDWCube($classifIndicator, $dWCube)
    {
        $dWIndicator = new DW_Model_Indicator($dWCube);
        $dWIndicator->setRef($classifIndicator->getRef());
        $dWIndicator->setUnit($classifIndicator->getUnit());
        $dWIndicator->setRatioUnit($classifIndicator->getRatioUnit());
        $this->translateEntity($classifIndicator, $dWIndicator);
    }

    /**
     * Copie un axe de Classif dans un cube DW.
     *
     * @param Classif_Model_Axis $classifAxis
     * @param DW_Model_Cube $dwCube
     * @param array &$associationArray
     */
    protected function copyAxisAndMembersFromClassifToDW($classifAxis, $dwCube, & $associationArray=array())
    {
        $dWAxis = new DW_Model_Axis($dwCube);
        $dWAxis->setRef('c_'.$classifAxis->getRef());
        $this->translateEntity($classifAxis, $dWAxis);

        $associationArray['axes'][$classifAxis->getRef()] = $dWAxis;
        $narrowerAxis = $classifAxis->getDirectNarrower();
        if ($narrowerAxis !== null) {
            $dWAxis->setDirectNarrower($associationArray['axes'][$narrowerAxis->getRef()]);
        }

        foreach ($classifAxis->getMembers() as $classifMember) {
            $dWMember = new DW_Model_Member($dWAxis);
            $dWMember->setRef($classifMember->getRef());
            $dWMember->setPosition($classifMember->getPosition());
            $this->translateEntity($classifMember, $dWMember);

            $memberIdentifier = $classifMember->getAxis()->getRef().'_'.$classifMember->getRef();
            $associationArray['members'][$memberIdentifier] = $dWMember;
            foreach ($classifMember->getDirectChildren() as $narrowerClassifMember) {
                $narrowerIdentifier = $narrowerClassifMember->getAxis()->getRef().'_'.$narrowerClassifMember->getRef();
                $dWMember->addDirectChild($associationArray['members'][$narrowerIdentifier]);
            }
        }

        foreach ($classifAxis->getDirectBroaders() as $broaderClassifAxis) {
            $this->copyAxisAndMembersFromClassifToDW($broaderClassifAxis, $dwCube, $associationArray);
        }
    }

    /**
     * Peuple le cube de DW avec les données issues de Classif.
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
    protected function copyAxisAndMembersFromOrgaToDW($orgaAxis, $dwCube, $orgaFilters, & $associationArray=array())
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

        foreach ($orgaAxis->getMembers() as $orgaMember) {
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
            foreach ($orgaMember->getDirectChildren() as $narrowerOrgaMember) {
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
        $queryDWCube = new Core_Model_Query();
        $queryDWCube->filter->addCondition(DW_Model_Report::QUERY_CUBE, $cell->getGranularity()->getDWCube());
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
     * Indique si les cubes de DW d'un projt donné est à jour vis à vis de données de Classif et Orga.
     *
     * @param Orga_Model_Organization $organization
     *
     * @return bool
     */
    public function areOrganizationDWCubesUpToDate(Orga_Model_Organization $organization)
    {
        foreach ($organization->getGranularities() as $granularity) {
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
     * Indique si le cube de DW d'un Granularity donné est à jour vis à vis des données de Classif et Orga.
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
            array(
                'axes' => $granularity->getAxes()
            )
        );
    }

    /**
     * Indique si le cube de DW d'un Cell donné est à jour vis à vis des données de Classif et Orga.
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
            array(
                'axes' => $cell->getGranularity()->getAxes(),
                'members' => $cell->getMembers()
            )
        );
    }

    /**
     * Indique les différences entre un cube de DW donné el les données de Classif et Orga.
     *
     * @param DW_Model_Cube $dWCube
     * @param Orga_Model_Organization $orgaOrganization
     * @param array $orgaFilters
     *
     * @return bool
     */
    protected function isDWCubeUpToDate($dWCube, $orgaOrganization, $orgaFilters)
    {
        return !(
            $this->areDWIndicatorsUpToDate($dWCube)
            || $this->areDWAxesUpToDate($dWCube, $orgaOrganization, $orgaFilters)
        );
    }

    /**
     * Compare les différences entre une liste d'indicateurs de DW et ceux de Classif.
     *
     * @param DW_Model_Cube $dWCube
     *
     * @return bool
     */
    protected function areDWIndicatorsUpToDate($dWCube)
    {
        $classifIndicators = Classif_Model_Indicator::loadList();
        $dWIndicators = $dWCube->getIndicators();

        foreach (Classif_Model_Indicator::loadList() as $classifIndex => $classifIndicator) {
            /** @var Classif_Model_Indicator $classifIndicator */
            foreach ($dWCube->getIndicators() as $dWIndex => $dWIndicator) {
                if (!($this->isDWIndicatorDifferentFromClassif($dWIndicator, $classifIndicator))) {
                    unset($classifIndicators[$classifIndex]);
                    unset($dWIndicators[$dWIndex]);
                }
            }
        }

        if ((count($classifIndicators) > 0) || (count($dWIndicators) > 0)) {
            return true;
        }

        return false;
    }

    /**
     * Compare les différences entre une liste d'indicateurs de DW et ceux de Classif.
     *
     * @param DW_Model_Indicator $dWIndicator
     * @param Classif_Model_Indicator $classifIndicator
     *
     * @return bool
     */
    protected function isDWIndicatorDifferentFromClassif($dWIndicator, $classifIndicator)
    {
        if (($classifIndicator->getRef() !== $dWIndicator->getRef())
            || ($classifIndicator->getUnit()->getRef() !== $dWIndicator->getUnit()->getRef())
            || ($classifIndicator->getRatioUnit()->getRef() !== $dWIndicator->getRatioUnit()->getRef())
            || ($this->areTranslationsDifferent($classifIndicator, $dWIndicator))
        ) {
            return true;
        }

        return false;
    }

    /**
     * Compare les différences entre une liste d'indicateurs de DW et ceux de Classif.
     *
     * @param DW_Model_Cube $dWCube
     * @param Orga_Model_Organization $orgaOrganization
     * @param array $orgaFilters
     *
     * @return bool
     */
    protected function areDWAxesUpToDate($dWCube, $orgaOrganization, $orgaFilters)
    {
        $queryClassifRootAxes = new Core_Model_Query();
        $queryClassifRootAxes->filter->addCondition(
            Classif_Model_Axis::QUERY_NARROWER,
            null,
            Core_Model_Filter::OPERATOR_NULL
        );
        $dWRootAxes = $dWCube->getRootAxes();

        // Classif.
        $classifRootAxes = Classif_Model_Axis::loadList($queryClassifRootAxes);
        foreach (Classif_Model_Axis::loadList($queryClassifRootAxes) as $classifIndex => $classifAxis) {
            /** @var Classif_Model_Axis $classifAxis */
            foreach ($dWCube->getRootAxes() as $dWIndex => $dWAxis) {
                if (!($this->isDWAxisDifferentFromClassif($dWAxis, $classifAxis))) {
                    unset($classifRootAxes[$classifIndex]);
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

        if ((count($classifRootAxes) > 0) || (count($orgaRootAxes) > 0) || (count($dWRootAxes) > 1)) {
            return true;
        }

        return false;
    }

    /**
     * Compare un axe de DW et un de Classif.
     *
     * @param DW_Model_Axis $dWAxis
     * @param Classif_Model_Axis $classifAxis
     *
     * @return bool
     */
    protected function isDWAxisDifferentFromClassif($dWAxis, $classifAxis)
    {
        if (('c_'.$classifAxis->getRef() !== $dWAxis->getRef())
            || ((($classifAxis->getDirectNarrower() !== null) || ($dWAxis->getDirectNarrower() !== null))
                && (($classifAxis->getDirectNarrower() === null) || ($dWAxis->getDirectNarrower() === null)
                || ('c_'.$classifAxis->getDirectNarrower()->getRef() !== $dWAxis->getDirectNarrower()->getRef())))
            || ($this->areTranslationsDifferent($classifAxis, $dWAxis))
            || ($this->areDWMembersDifferentFromClassif($dWAxis, $classifAxis))
        ) {
            return true;
        } else {
            $classifBroaderAxes = $classifAxis->getDirectBroaders();
            $dWBroaderAxes = $dWAxis->getDirectBroaders();

            foreach ($classifAxis->getDirectBroaders() as $classifIndex => $classifBroaderAxis) {
                foreach ($dWAxis->getDirectBroaders() as $dWIndex => $dWBroaderAxis) {
                    if (!($this->isDWAxisDifferentFromClassif($dWBroaderAxis, $classifBroaderAxis))) {
                        unset($classifBroaderAxes[$classifIndex]);
                        unset($dWBroaderAxes[$dWIndex]);
                    }
                }
            }

            if ((count($classifBroaderAxes) > 0) || (count($dWBroaderAxes) > 0)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Compare un membre de DW et un de Classif.
     *
     * @param DW_Model_Axis $dWAxis
     * @param Classif_Model_Axis $classifAxis
     *
     * @return bool
     */
    protected function areDWMembersDifferentFromClassif($dWAxis, $classifAxis)
    {
        $classifMembers = $classifAxis->getMembers();
        $dWMembers = $dWAxis->getMembers();

        foreach ($classifAxis->getMembers() as $classifIndex => $classifMember) {
            foreach ($dWAxis->getMembers() as $dWIndex => $dWMember) {
                if (!($this->isDWMemberDifferentFromClassif($dWMember, $classifMember))) {
                    unset($classifMembers[$classifIndex]);
                    unset($dWMembers[$dWIndex]);
                }
            }
        }

        if ((count($classifMembers) > 0) || (count($dWMembers) > 0)) {
            return true;
        }

        return false;
    }

    /**
     * Compare un membre de DW et un de Classif.
     *
     * @param DW_Model_Member $dWMember
     * @param Classif_Model_Member $classifMember
     *
     * @return bool
     */
    protected function isDWMemberDifferentFromClassif($dWMember, $classifMember)
    {
        if (($classifMember->getRef() !== $dWMember->getRef())
            || ($this->areTranslationsDifferent($classifMember, $dWMember))
        ) {
            return true;
        } else {
            $classifParentMembers = $classifMember->getDirectParents();
            $dWParentMembers = $dWMember->getDirectParents();

            foreach ($classifMember->getDirectParents() as $classifIndex => $classifParentMember) {
                foreach ($dWMember->getDirectParents() as $dWIndex => $dWParentMember) {
                    if ($classifParentMember->getRef() === $dWParentMember->getRef()) {
                        unset($classifParentMembers[$classifIndex]);
                        unset($dWParentMembers[$dWIndex]);
                    }
                }
            }

            if ((count($classifParentMembers) > 0) || (count($dWParentMembers) > 0)) {
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

        $orgaMembers = $orgaAxis->getMembers()->toArray();
        $dWMembers = $dWAxis->getMembers();

        foreach ($orgaAxis->getMembers()->toArray() as $orgaIndex => $orgaMember) {
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
                } else {
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
            $orgaParentMembers = $orgaMember->getDirectParents();
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
        foreach ($organization->getGranularities() as $granularity) {
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
        /** @var Core_EventDispatcher $eventDispatcher */
        $eventDispatcher = Zend_Registry::get('container')->get('Core_EventDispatcher');
        $eventDispatcher->removeListener('Orga_Model_GranularityReport', 'DW_Model_Report');

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
            array(
                'axes' => $granularity->getAxes()
            )
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
     */
    public function resetCellDWCube(Orga_Model_Cell $cell)
    {
        if ($cell->getGranularity()->getCellsGenerateDWCubes()) {
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

        $this->populateDWCubeWithClassifAndOrga($dWCube, $orgaOrganization, $orgaFilter);
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