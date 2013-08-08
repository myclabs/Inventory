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
class Orga_Service_ETLStructure extends Core_Singleton
{
    /**
     * Renvoie l'instance Singleton de la classe.
     *
     * @return Orga_Service_ETLStructure
     */
    public static function getInstance()
    {
        return parent::getInstance();
    }


    /**
     * Peuple le cube de DW avec les données issues de Classif et Orga.
     *
     * @param Orga_Model_Cell $cell
     */
    public function populateCellDWCube(Orga_Model_Cell $cell)
    {
        $this->populateDWCubeWithClassifAndOrgaOrganization(
            $cell->getDWCube(),
            $cell->getGranularity()->getOrganization(),
            array(
                'axes' => $cell->getGranularity()->getAxes(),
                'members' => $cell->getMembers()
            )
        );
    }

    /**
     * Peuple le cube de DW avec les données issues de Classif et Orga.
     *
     * @param Orga_Model_Granularity $granularity
     */
    public function populateGranularityDWCube(Orga_Model_Granularity $granularity)
    {
        $this->populateDWCubeWithClassifAndOrgaOrganization(
            $granularity->getDWCube(),
            $granularity->getOrganization(),
            array(
                'axes' => $granularity->getAxes()
            )
        );
    }

    /**
     * Peuple le cube de DW avec les données issues de Classif et Orga.
     *
     * @param DW_Model_Cube $dWCube
     * @param Orga_Model_Organization $orgaOrganization
     * @param array $orgaFilters
     */
    protected function populateDWCubeWithClassifAndOrgaOrganization($dWCube, $orgaOrganization, $orgaFilters)
    {
        $this->populateDWCubeWithOrgaOrganization($dWCube, $orgaOrganization, $orgaFilters);
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
        $dWIndicator = new DW_Model_Indicator($dWCube);
        $dWIndicator->setLabel($classifIndicator->getLabel());
        $dWIndicator->setRef('classif_'.$classifIndicator->getRef());
        $dWIndicator->setUnit($classifIndicator->getUnit());
        $dWIndicator->setRatioUnit($classifIndicator->getRatioUnit());
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
        $dWAxis->setLabel($classifAxis->getLabel());
        $dWAxis->setRef('classif_'.$classifAxis->getRef());
        $associationArray['axes'][$classifAxis->getRef()] = $dWAxis;
        $narrowerAxis = $classifAxis->getDirectNarrower();
        if ($narrowerAxis !== null) {
            $dWAxis->setDirectNarrower($associationArray['axes'][$narrowerAxis->getRef()]);
        }

        foreach ($classifAxis->getMembers() as $classifMember) {
            $dWMember = new DW_Model_Member($dWAxis);
            $dWMember->setLabel($classifMember->getLabel());
            $dWMember->setRef('classif_'.$classifMember->getRef());
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
        $dWAxis->setLabel($orgaAxis->getLabel());
        $dWAxis->setRef('orga_'.$orgaAxis->getRef());
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
            $dWMember->setLabel($orgaMember->getLabel());
            $dWMember->setRef('orga_'.$orgaMember->getRef());
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
        $entityManagers = Zend_Registry::get('EntityManagers');
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
            $entityManagers['default']->flush($cellDWReport);
        }
    }

    /**
     * Ajoute les Reports de la granularité au DW Cube d'une cellule.
     *
     * @param Orga_Model_Cell $cell
     */
    public function addGranularityDWReportsToCellDWCube($cell)
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
     * @param DW_Model_Cube $dWCube
     */
    protected function copyGranularityReportToCellDWCube($granularityReport, $dWCube)
    {
        $granularityReport->addCellDWReport($granularityReport->getGranularityDWReport()->copyToCube($dWCube));
    }

    /**
     * Indique si les cubes de DW d'un projt donné est à jour vis à vis de données de Classif et Orga.
     *
     * @param Orga_Model_Organization $organization
     *
     * @return bool
     */
    public function areOrganizationDWCubesUpToDate($organization)
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
            $this->areDWIndicatorsDifferentFromClassif($dWCube)
            || $this->areDWAxesDifferentFromClassifAndOrga($dWCube, $orgaOrganization, $orgaFilters)
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
     * @param Orga_Model_Organization $orgaOrganization
     * @param array $orgaFilters
     *
     * @return bool
     */
    protected function areDWAxesDifferentFromClassifAndOrga($dWCube, $orgaOrganization, $orgaFilters)
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

        $orgaRootAxes = $orgaOrganization->getRootAxes();

        foreach ($orgaOrganization->getRootAxes() as $orgaIndex => $orgaAxis) {
            foreach ($dWCube->getRootAxes() as $dWIndex => $dWAxis) {
                if (!($this->isDWAxisDifferentFromOrga($dWAxis, $orgaAxis, $orgaFilters))) {
                    unset($orgaRootAxes[$orgaIndex]);
                    unset($dWRootAxes[$dWIndex]);
                }
            }
        }

        if ((count($classifRootAxes) > 0) || (count($orgaRootAxes) > 0) || (count($dWRootAxes) > 0)) {
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
     * Réinitialise les cubes de DW des Cell d'un projet donné.
     *
     * @param Orga_Model_Organization $organization
     */
    public function resetOrganizationDWCubes(Orga_Model_Organization $organization)
    {
        $entityManagers = Zend_Registry::get('EntityManagers');
        /** @var EntityManager $entityManager */
        $entityManager = $entityManagers['default'];

        foreach ($organization->getGranularities() as $granularity) {
            if ($granularity->getCellsGenerateDWCubes()) {
                $this->resetGranularityDWCubes(Orga_Model_Granularity::load($granularity->getId()));
                $entityManager->flush();
                foreach ($granularity->getCells() as $cell) {
                    $entityManager->clear();
                    $this->resetCellDWCube(Orga_Model_Cell::load($cell->getId()));
                    $entityManager->flush();
                }
            }
        }
    }

    /**
     * Réinitialise le cube de DW d'un Granularity.
     *
     * @param Orga_Model_Granularity $granularity
     */
    public function resetGranularityDWCubes(Orga_Model_Granularity $granularity)
    {
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
        Orga_Service_ETLData::getInstance()->calculateResultsForCellAndChildren($cell);
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
            $entityManagers = Zend_Registry::get('EntityManagers');

            Orga_Service_ETLData::getInstance()->clearDWResultsForCell($cell);
            $entityManagers['default']->flush();

            $this->resetDWCube(
                $cell->getDWCube(),
                $cell->getGranularity()->getOrganization(),
                array(
                    'axes' => $cell->getGranularity()->getAxes(),
                    'members' => $cell->getMembers()
                )
            );

            Orga_Service_ETLData::getInstance()->populateDWResultsForCell($cell);
            $entityManagers['default']->flush();
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
        $entityManagers = Zend_Registry::get('EntityManagers');

        // Problème de proxie;
//        $dWCube->getLabel();

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

        $entityManagers['default']->flush();

        $this->populateDWCubeWithClassifAndOrgaOrganization($dWCube, $orgaOrganization, $orgaFilter);
        $dWCube->save();

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

        $entityManagers['default']->flush();
    }

}