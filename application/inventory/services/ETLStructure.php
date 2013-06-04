<?php
/**
 * @package Inventory
 * @subpackage Service
 */

use Doctrine\ORM\EntityManager;

/**
 * Classe permettant de construire DW
 * @author valentin.claras
 * @package Inventory
 * @subpackage Service
 *
 */
class Inventory_Service_ETLStructure extends Core_Service
{
    /**
     * Renvoie l'instance Singleton de la classe.
     *
     * @return Inventory_Service_ETLStructure
     */
    public static function getInstance()
    {
        return parent::getInstance();
    }


    /**
     * Peuple le cube de DW avec les données issues de Classif et Orga.
     *
     * @param Inventory_Model_CellDataProvider $cellDataProvider
     */
    protected function populateCellDataProviderDWCubeService(Inventory_Model_CellDataProvider $cellDataProvider)
    {
        $this->populateDWCubeWithClassifAndOrgaCube(
            $cellDataProvider->getDWCube(),
            $cellDataProvider->getOrgaCell()->getGranularity()->getCube(),
            array(
                'axes' => $cellDataProvider->getOrgaCell()->getGranularity()->getAxes(),
                'members' => $cellDataProvider->getOrgaCell()->getMembers()
            )
        );
    }

    /**
     * Peuple le cube de DW avec les données issues de Classif et Orga.
     *
     * @param Inventory_Model_GranularityDataProvider $granularityDataProvider
     */
    protected function populateGranularityDataProviderDWCubeService(
        Inventory_Model_GranularityDataProvider $granularityDataProvider
    ) {
        $this->populateDWCubeWithClassifAndOrgaCube(
            $granularityDataProvider->getDWCube(),
            $granularityDataProvider->getOrgaGranularity()->getCube(),
            array(
                'axes' => $granularityDataProvider->getOrgaGranularity()->getAxes()
            )
        );
    }

    /**
     * Peuple le cube de DW avec les données issues de Classif et Orga.
     *
     * @param DW_Model_Cube $dWCube
     * @param Orga_Model_Cube $orgaCube
     * @param array $orgaFilters
     */
    protected function populateDWCubeWithClassifAndOrgaCube($dWCube, $orgaCube, $orgaFilters)
    {
        $this->populateDWCubeWithOrgaCube($dWCube, $orgaCube, $orgaFilters);
        $this->populateDWCubeWithClassif($dWCube);
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
        $dWIndicator = new DW_Model_Indicator();
        $dWIndicator->setLabel($classifIndicator->getLabel());
        $dWIndicator->setRef('classif_'.$classifIndicator->getRef());
        $dWIndicator->setUnit($classifIndicator->getUnit());
        $dWIndicator->setRatioUnit($classifIndicator->getRatioUnit());
        $dWIndicator->setCube($dWCube);
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
        $dWAxis = new DW_Model_Axis();
        $dWAxis->setLabel($classifAxis->getLabel());
        $dWAxis->setRef('classif_'.$classifAxis->getRef());
        $dWAxis->setCube($dwCube);
        $associationArray['axes'][$classifAxis->getRef()] = $dWAxis;
        $narrowerAxis = $classifAxis->getDirectNarrower();
        if ($narrowerAxis !== null) {
            $dWAxis->setDirectNarrower($associationArray['axes'][$narrowerAxis->getRef()]);
        }

        foreach ($classifAxis->getMembers() as $classifMember) {
            $dWMember = new DW_Model_Member();
            $dWMember->setLabel($classifMember->getLabel());
            $dWMember->setRef('classif_'.$classifMember->getRef());
            $dWMember->setAxis($dWAxis);
            $dWMember->setPosition($classifMember->getPosition());
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
     * @param Orga_Model_Cube $orgaCube
     * @param array $orgaFilters
     */
    protected function populateDWCubeWithOrgaCube($dWCube, $orgaCube, $orgaFilters)
    {
        foreach ($orgaCube->getRootAxes() as $orgaAxis) {
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

        $dWAxis = new DW_Model_Axis();
        $dWAxis->setLabel($orgaAxis->getLabel());
        $dWAxis->setRef('orga_'.$orgaAxis->getRef());
        $dWAxis->setCube($dwCube);
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

            $dWMember = new DW_Model_Member();
            $dWMember->setLabel($orgaMember->getLabel());
            $dWMember->setRef('orga_'.$orgaMember->getRef());
            $dWMember->setAxis($dWAxis);
            $memberIdentifier = $orgaMember->getAxis()->getRef().'_'.$orgaMember->getCompleteRef();
            $associationArray['members'][$memberIdentifier] = $dWMember;
            foreach ($orgaMember->getDirectChildren() as $narrowerClassifMember) {
                $narrowerIdentifier = $narrowerClassifMember->getAxis()->getRef().'_'
                    .$narrowerClassifMember->getCompleteRef();
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
     * @param Inventory_Model_GranularityReport $granularityReport
     */
    protected function createCellsReportFromGranularityReportService($granularityReport)
    {
        foreach ($granularityReport->getGranularityDataProvider()->getOrgaGranularity()->getCells() as $orgaCell) {
            $cellDataProvider = Inventory_Model_CellDataProvider::loadByOrgaCell($orgaCell);
            $this->copyGranularityReportToCellDataProviderDWCube($granularityReport, $cellDataProvider->getDWCube());
        }
    }

    /**
     * Mets à jour les Report des cubes des cellules copiés depuis le cube de la granularité.
     *
     * @param Inventory_Model_GranularityReport $granularityReport
     */
    protected function updateCellsReportFromGranularityReportService($granularityReport)
    {
        $entityManagers = Zend_Registry::get('EntityManagers');
        $granularityReportAsString = $granularityReport->getGranularityDataProviderDWReport()->getAsString();
        foreach ($granularityReport->getCellDataProviderDWReports() as $cellDataProviderDWReport) {
            $cellDataProviderDWReportId = $cellDataProviderDWReport->getKey()['id'];
            $cellDataProviderDWReportIdCube = $cellDataProviderDWReport->getCube()->getKey()['id'];
            $cellDataProviderDWReport = DW_Model_Report::getFromString(
                preg_replace(
                    '#^(\{"id":)([0-9]+)(,"idCube":)([0-9]+)(,.+\})$#',
                    '${1}'.$cellDataProviderDWReportId.'${3}'.$cellDataProviderDWReportIdCube.'${5}',
                    $granularityReportAsString
                )
            );
            $entityManagers['default']->flush($cellDataProviderDWReport);
        }
    }

    /**
     * Ajoute les Reports de la granularité au DW Cube d'une cellule.
     *
     * @param Inventory_Model_CellDataProvider $cellDataProvider
     */
    protected function addGranularityReportsToCellDataProviderDWCubeService($cellDataProvider)
    {
        foreach ($cellDataProvider->getGranularityDataProvider()->getGranularityReports() as $granularityReport) {
            $this->copyGranularityReportToCellDataProviderDWCube(
                $granularityReport,
                $cellDataProvider->getDWCube()
            );
        }
    }

    /**
     * Copie le Report d'un Cube de DW d'un GranularityDataProvider dans le Cube d'un CellDataProvider.
     *
     * @param Inventory_Model_GranularityReport $granularityReport
     * @param DW_Model_Cube $dWCube
     */
    protected function copyGranularityReportToCellDataProviderDWCube($granularityReport, $dWCube)
    {
        $clonedReport = clone $granularityReport->getGranularityDataProviderDWReport();

        // Déplace le report cloné dans le nouveau cube (et met à jour ses infos)
        $clonedReport->setCube($dWCube);

        $granularityReport->addCellDataProviderDWReport($clonedReport);
    }

    /**
     * Indique si les cubes de DW d'un projt donné est à jour vis à vis de données de Classif et Orga.
     *
     * @param Inventory_Model_Project $project
     *
     * @return bool
     */
    protected function areProjetDWCubesUpToDateService($project)
    {
        foreach ($project->getOrgaCube()->getGranularities() as $orgaGranularity) {
            $granularityDataProvider = Inventory_Model_GranularityDataProvider::loadByOrgaGranularity(
                $orgaGranularity
            );

            if ($granularityDataProvider->getCellsGenerateDWCubes()) {
                if (!($this->isGranularityDataProviderDWCubeUpToDateService($granularityDataProvider))) {
                    return false;
                }

                foreach ($orgaGranularity->getCells() as $orgaCell) {
                    $cellDataProvider = Inventory_Model_CellDataProvider::loadByOrgaCell($orgaCell);
                    if (!($this->isCellDataProviderDWCubeUpToDateService($cellDataProvider))) {
                        return false;
                    }
                }
            }
        }

        return true;
    }

    /**
     * Indique si le cube de DW d'un GranularityDataProvider donné est à jour vis à vis des données de Classif et Orga.
     *
     * @param Inventory_Model_GranularityDataProvider $granularityDataProvider
     *
     * @return bool
     */
    protected function isGranularityDataProviderDWCubeUpToDateService($granularityDataProvider)
    {
        return $this->isDWCubeUpToDate(
            $granularityDataProvider->getDWCube(),
            $granularityDataProvider->getOrgaGranularity()->getCube(),
            array(
                'axes' => $granularityDataProvider->getOrgaGranularity()->getAxes()
            )
        );
    }

    /**
     * Indique si le cube de DW d'un CellDataProvider donné est à jour vis à vis des données de Classif et Orga.
     *
     * @param Inventory_Model_CellDataProvider $cellDataProvider
     *
     * @return bool
     */
    protected function isCellDataProviderDWCubeUpToDateService($cellDataProvider)
    {
        return $this->isDWCubeUpToDate(
            $cellDataProvider->getDWCube(),
            $cellDataProvider->getOrgaCell()->getGranularity()->getCube(),
            array(
                'axes' => $cellDataProvider->getOrgaCell()->getGranularity()->getAxes(),
                'members' => $cellDataProvider->getOrgaCell()->getMembers()
            )
        );
    }


    /**
     * Indique les différences entre un cube de DW donné el les données de Classif et Orga.
     *
     * @param DW_Model_Cube $dWCube
     * @param Orga_Model_Cube $orgaCube
     * @param array $orgaFilters
     *
     * @return bool
     */
    protected function isDWCubeUpToDate($dWCube, $orgaCube, $orgaFilters)
    {
        return !(
            $this->areDWIndicatorsDifferentFromClassif($dWCube)
            || $this->areDWAxesDifferentFromClassifAndOrga($dWCube, $orgaCube, $orgaFilters)
        );
    }

    /**
     * Compare les différences entre une liste d'indicateurs de DW et ceux de Classif.
     *
     * @param DW_Model_Cube $dWCube
     *
     * @return bool
     */
    protected function areDWIndicatorsDifferentFromClassif($dWCube)
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

        if ((count($classifIndicators) > 0) || (count($dWIndicators) > 1)) {
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
        if (('classif_'.$classifIndicator->getRef() !== $dWIndicator->getRef())
            || ($classifIndicator->getLabel() !== $dWIndicator->getLabel())
            || ($classifIndicator->getUnit()->getRef() !== $dWIndicator->getUnit()->getRef())
            || ($classifIndicator->getRatioUnit()->getRef() !== $dWIndicator->getRatioUnit()->getRef())
        ) {
            return true;
        }

        return false;
    }

    /**
     * Compare les différences entre une liste d'indicateurs de DW et ceux de Classif.
     *
     * @param DW_Model_Cube $dWCube
     * @param Orga_Model_Cube $orgaCube
     * @param array $orgaFilters
     *
     * @return bool
     */
    protected function areDWAxesDifferentFromClassifAndOrga($dWCube, $orgaCube, $orgaFilters)
    {
        $queryClassifRootAxes = new Core_Model_Query();
        $queryClassifRootAxes->filter->addCondition(
            Classif_Model_Axis::QUERY_NARROWER,
            null,
            Core_Model_Filter::OPERATOR_NULL
        );
        $classifRootAxes = Classif_Model_Axis::loadList($queryClassifRootAxes);
        $dWRootAxes = $dWCube->getRootAxes();

        foreach (Classif_Model_Axis::loadList($queryClassifRootAxes) as $classifIndex => $classifAxis) {
            /** @var Classif_Model_Axis $classifAxis */
            foreach ($dWCube->getRootAxes() as $dWIndex => $dWAxis) {
                if (!($this->isDWAxisDifferentFromClassif($dWAxis, $classifAxis))) {
                    unset($classifRootAxes[$classifIndex]);
                    unset($dWRootAxes[$dWIndex]);
                }
            }
        }

        $orgaRootAxes = $orgaCube->getRootAxes();

        foreach ($orgaCube->getRootAxes() as $orgaIndex => $orgaAxis) {
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
        if (('classif_'.$classifAxis->getRef() !== $dWAxis->getRef())
            || ($classifAxis->getLabel() !== $dWAxis->getLabel())
            || ((($classifAxis->getDirectNarrower() !== null) || ($dWAxis->getDirectNarrower() !== null))
                && (($classifAxis->getDirectNarrower() === null) || ($dWAxis->getDirectNarrower() === null)
                || ('classif_'.$classifAxis->getDirectNarrower()->getRef() !== $dWAxis->getDirectNarrower()->getRef())))
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
        if (('classif_'.$classifMember->getRef() !== $dWMember->getRef())
            || ($classifMember->getLabel() !== $dWMember->getLabel())
        ) {
            return true;
        } else {
            $classifParentMembers = $classifMember->getDirectParents();
            $dWParentMembers = $dWMember->getDirectParents();

            foreach ($classifMember->getDirectParents() as $classifIndex => $classifParentMember) {
                foreach ($dWMember->getDirectParents() as $dWIndex => $dWParentMember) {
                    if ('classif_'.$classifParentMember->getRef() === $dWParentMember->getRef()) {
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

        if (('orga_'.$orgaAxis->getRef() !== $dWAxis->getRef())
            || ($orgaAxis->getLabel() !== $dWAxis->getLabel())
            || ((($orgaAxis->getDirectNarrower() !== null) || ($dWAxis->getDirectNarrower() !== null))
                && (($orgaAxis->getDirectNarrower() === null) || ($dWAxis->getDirectNarrower() === null)
                || ('orga_'.$orgaAxis->getDirectNarrower()->getRef() !== $dWAxis->getDirectNarrower()->getRef())))
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

        $orgaMembers = $orgaAxis->getMembers();
        $dWMembers = $dWAxis->getMembers();

        foreach ($orgaAxis->getMembers() as $orgaIndex => $orgaMember) {
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
        if (('orga_'.$orgaMember->getRef() !== $dWMember->getRef())
            || ($orgaMember->getLabel() !== $dWMember->getLabel())
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
                    if ('orga_'.$orgaParentMember->getRef() === $dWParentMember->getRef()) {
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
     * Réinitialise les cubes de DW des CellDataProvider d'un projet donné.
     *
     * @param Inventory_Model_Project $project
     */
    protected function resetProjectDWCubesService($project)
    {
        $entityManagers = Zend_Registry::get('EntityManagers');
        /** @var EntityManager $entityManager */
        $entityManager = $entityManagers['default'];

        foreach ($project->getOrgaCube()->getGranularities() as $orgaGranularity) {
            $granularityDataProvider = Inventory_Model_GranularityDataProvider::loadByOrgaGranularity($orgaGranularity);
            if ($granularityDataProvider->getCellsGenerateDWCubes()) {
                $this->resetGranularityDataProviderDWCubesService($granularityDataProvider);
                $entityManager->flush();
                foreach ($orgaGranularity->getCells() as $orgaCell) {
                    $entityManager->clear();
                    $this->resetCellDataProviderDWCubesService(
                        Inventory_Model_CellDataProvider::loadByOrgaCell($orgaCell)
                    );
                    $entityManager->flush();
                    $entityManager->clear();
                }
            }
        }
    }

    /**
     * Réinitialise le cube de DW d'un GranularityDataProvider.
     *
     * @param Inventory_Model_GranularityDataProvider $granularityDataProvider
     */
    protected function resetGranularityDataProviderDWCubesService($granularityDataProvider)
    {
        $this->resetDWCube(
            $granularityDataProvider->getDWCube(),
            $granularityDataProvider->getOrgaGranularity()->getCube(),
            array(
                'axes' => $granularityDataProvider->getOrgaGranularity()->getAxes()
            )
        );
    }

    /**
     * Régénère les cubes de données ET (PUIS) relance les calculs des formulaires comptables d'une cellule
     * ET de toutes ses sous-cellules.
     *
     * @param Inventory_Model_CellDataProvider $cellDataProvider
     */
    protected function resetCellDataProviderAndChildrenCalculationsAndDWCubesService($cellDataProvider)
    {
        $this->resetCellDataProviderAndChildrenDWCubesService($cellDataProvider);
        Inventory_Service_ETLData::getInstance()->calculateResultsForCellDataProviderAndChildren($cellDataProvider);
    }

    /**
     * Régénère les cubes de données d'une cellule et de toutes ses sous-cellules.
     * Ne relance PAS les calculs des formulaires comptables.
     *
     * @param Inventory_Model_CellDataProvider $cellDataProvider
     */
    protected function resetCellDataProviderAndChildrenDWCubesService($cellDataProvider)
    {
        $this->resetCellDataProviderDWCubesService($cellDataProvider);

        foreach ($cellDataProvider->getOrgaCell()->getChildCells() as $childOrgaCell) {
            $this->resetCellDataProviderDWCubesService(
                Inventory_Model_CellDataProvider::loadByOrgaCell($childOrgaCell)
            );
        }
    }

    /**
     * Régénère les cubes de données d'une cellule.
     * Ne regénère PAS les cubes de données des sous-cellules.
     * Ne relance PAS les calculs des formulaires comptables.
     *
     * @param Inventory_Model_CellDataProvider $cellDataProvider
     * @todo Renommer en enlevant le "s" à "Cubes" car un seul cube concerné.
     */
    protected function resetCellDataProviderDWCubesService(Inventory_Model_CellDataProvider $cellDataProvider)
    {
        if ($cellDataProvider->getGranularityDataProvider()->getCellsGenerateDWCubes()) {

            Inventory_Service_ETLData::getInstance()->clearDWResultsForCellDataProvider($cellDataProvider);

            $this->resetDWCube(
                $cellDataProvider->getDWCube(),
                $cellDataProvider->getOrgaCell()->getGranularity()->getCube(),
                array(
                    'axes' => $cellDataProvider->getOrgaCell()->getGranularity()->getAxes(),
                    'members' => $cellDataProvider->getOrgaCell()->getMembers()
                )
            );

            Inventory_Service_ETLData::getInstance()->populateDWResultsForCellDataProvider($cellDataProvider);
        }
    }

    /**
     * Réinitialise un cube de DW donné.
     *
     * @param DW_Model_Cube $dWCube
     * @param Orga_Model_Cube $orgaCube
     * @param array $orgaFilter
     */
    protected function resetDWCube($dWCube, $orgaCube, $orgaFilter)
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
            foreach ($dWReport->getFilters() as $dWFilter) {
                $dWFilter->delete();
            }
            $emptyDWReportString = '{'.
                '"id":'.$dWReport->getKey()['id'].',"idCube":'.$dWReport->getCube()->getKey()['id'].',"label":"",'.
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

        $entityManagers = Zend_Registry::get('EntityManagers');
        $entityManagers['default']->flush();

        $this->populateDWCubeWithClassifAndOrgaCube($dWCube, $orgaCube, $orgaFilter);
        $dWCube->save();

        $entityManagers = Zend_Registry::get('EntityManagers');
        $entityManagers['default']->flush();

        // Copie des Reports.
        foreach ($dWReportsAsString as $dWReportString) {
            try {
                $newReport = DW_Model_Report::getFromString($dWReportString);
                $newReport->save();
            } catch (Core_Exception_NotFound $e) {
                // Le rapport n'est pas compatible avec la nouvelle version du cube.
            }
        }

        $entityManagers = Zend_Registry::get('EntityManagers');
        $entityManagers['default']->flush();
    }

}