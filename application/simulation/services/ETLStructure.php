<?php
/**
 * @package Simulation
 * @subpackage Service
 */

/**
 * Classe permettant de construire DW
 * @author valentin.claras
 * @package Simulation
 * @subpackage Service
 *
 */
class Simulation_Service_ETLStructure extends Core_Service
{
    /**
     * Renvoie l'instance Singleton de la classe.
     *
     * @return Simulation_Service_ETLStructure
     */
    public static function getInstance()
    {
        return parent::getInstance();
    }


    /**
     * Peuple le cube de DW avec les données issues de Classif.
     *
     * @param DW_Model_Cube $dWCube
     */
    protected function populateDWCubeWithClassifService($dWCube)
    {
        foreach (Classif_Model_Indicator::loadList() as $classifIndicator) {
            $this->copyIndicatorFromClassifToDWCube($classifIndicator, $dWCube);
        }

        $queryRootAxes = new Core_Model_Query();
        $queryRootAxes->filter->addCondition(
            Classif_Model_Axis::QUERY_NARROWER,
            null,
            Core_Model_Filter::OPERATOR_NULL
        );
        foreach (Classif_Model_Axis::loadList($queryRootAxes) as $classifAxis) {
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
        $dWIndicator->setRef($classifIndicator->getRef());
        $dWIndicator->setUnit($classifIndicator->getUnit());
        $dWIndicator->setRatioUnit($classifIndicator->getRatioUnit());
    }

    /*
     * Copie un axe de Classif dans un cube DW.
     *
     * @param Classif_Model_Axis $classifAxis
     * @param DW_Model_Cube $dWCube
     * @param array &$associationArray
     */
    protected function copyAxisAndMembersFromClassifToDW($classifAxis, $dwCube, & $associationArray=array())
    {
        $dWAxis = new DW_Model_Axis($dwCube);
        $dWAxis->setLabel($classifAxis->getLabel());
        $dWAxis->setRef($classifAxis->getRef());
        $associationArray['axes'][$classifAxis->getKey()['id']] = $dWAxis;
        $classifNarrowerAxis = $classifAxis->getDirectNarrower();
        if ($classifNarrowerAxis !== null) {
            $dWAxis->setDirectNarrower($associationArray['axes'][$classifNarrowerAxis->getKey()['id']]);
        }

        foreach ($classifAxis->getMembers() as $classifMember) {
            $dWMember = new DW_Model_Member($dWAxis);
            $dWMember->setLabel($classifMember->getLabel());
            $dWMember->setRef($classifMember->getRef());
            $dWMember->setPosition($classifMember->getPosition());
            $associationArray['members'][$classifMember->getKey()['id']] = $dWMember;
            foreach ($classifMember->getDirectChildren() as $classifNarrowerMember) {
                $dWMember->addDirectChild($associationArray['members'][$classifNarrowerMember->getKey()['id']]);
            }
        }

        foreach ($classifAxis->getDirectBroaders() as $classifBroaderAxis) {
            $this->copyAxisAndMembersFromClassifToDW($classifBroaderAxis, $dwCube, $associationArray);
        }
    }

    /**
     * Indique si un cube de DW donné est à jour vis à vis de données de Classif.
     *
     * @param Simulation_Model_Set $set
     *
     * @return bool
     */
    protected function isSetDWCubeUpToDateService($set)
    {
        return $this->isDWCubeUpToDate($set->getDWCube());
    }


    /**
     * Indique les différences entre un cube de DW donné el les données de Classif.
     *
     * @param DW_Model_Cube $dWCube
     *
     * @return bool
     */
    protected function isDWCubeUpToDate($dWCube)
    {
        return !($this->areDWIndicatorsDifferentFromClassif($dWCube) || $this->areDWAxesDifferentFromClassif($dWCube));
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
        if (($classifIndicator->getRef() !== $dWIndicator->getRef())
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
     *
     * @return bool
     */
    protected function areDWAxesDifferentFromClassif($dWCube)
    {
        $queryClassifRootAxes = new Core_Model_Query();
        $queryClassifRootAxes->filter->addCondition(
            Classif_Model_Axis::QUERY_NARROWER,
            null,
            Core_Model_Filter::OPERATOR_NULL
        );
        $classifRootAxes = Classif_Model_Axis::loadList($queryClassifRootAxes);
        $dWRootAxes = $dWCube->getRootAxes();

        foreach ($dWCube->getRootAxes() as $dWIndex => $dWAxis) {
            if ($dWAxis->getRef() !== 'set') {
                foreach (Classif_Model_Axis::loadList($queryClassifRootAxes) as $classifIndex => $classifAxis) {
                    if (!($this->isDWAxisDifferentFromClassif($dWAxis, $classifAxis))) {
                        unset($classifRootAxes[$classifIndex]);
                        unset($dWRootAxes[$dWIndex]);
                    }
                }
            }
        }

        if ((count($classifRootAxes) > 0) || (count($dWRootAxes) > 1)) {
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
        if (($classifAxis->getRef() !== $dWAxis->getRef())
            || ($classifAxis->getLabel() !== $dWAxis->getLabel())
            || ((($classifAxis->getDirectNarrower() !== null) || ($dWAxis->getDirectNarrower() !== null))
                && (($classifAxis->getDirectNarrower() === null) || ($dWAxis->getDirectNarrower() === null)
                    || ($classifAxis->getDirectNarrower()->getRef() !== $dWAxis->getDirectNarrower()->getRef())))
            || ($this->areDWMembersDifferentFromClassif($dWAxis, $classifAxis))
        ) {
            return true;
        } else {
            $classifAxisBroaders = $classifAxis->getDirectBroaders();
            $dWAxisBroaders = $dWAxis->getDirectBroaders();

            foreach ($dWAxis->getDirectBroaders() as $dWIndex => $dWBroaderAxis) {
                foreach ($classifAxis->getDirectBroaders() as $classifIndex => $classifBroaderAxis) {
                    if (!($this->isDWAxisDifferentFromClassif($dWBroaderAxis, $classifBroaderAxis))) {
                        unset($classifAxisBroaders[$classifIndex]);
                        unset($dWAxisBroaders[$dWIndex]);
                    }
                }
            }

            if ((count($classifAxisBroaders) > 0) || (count($dWAxisBroaders) > 0)) {
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

        foreach ($dWAxis->getMembers() as $dWIndex => $dWMember) {
            foreach ($classifAxis->getMembers() as $classifIndex => $classifMember) {
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
            || ($classifMember->getLabel() !== $dWMember->getLabel())
        ) {
            return true;
        } else {
            $classifMemberParents = $classifMember->getDirectParents();
            $dWMemberParents = $dWMember->getDirectParents();

            foreach ($dWMember->getDirectParents() as $dWIndex => $dWParentMember) {
                foreach ($classifMember->getDirectParents() as $classifIndex => $classifParentMember) {
                    if ($classifParentMember->getRef() === $dWParentMember->getRef()) {
                        unset($classifMemberParents[$classifIndex]);
                        unset($dWMemberParents[$dWIndex]);
                    }
                }
            }

            if ((count($classifMemberParents) > 0) || (count($dWMemberParents) > 0)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Réinitialise le cube de DW d'un Set donné.
     *
     * @param Simulation_Model_Set $set
     */
    protected function resetSetDWCubeService($set)
    {
        $scenarios = $set->getScenarios();

        foreach ($scenarios as $scenario) {
            Simulation_Service_ETLData::getInstance()->clearDWResultsFromScenario($scenario);
        }

        $this->resetDWCube($set->getDWCube());

        foreach ($scenarios as $scenario) {
            Simulation_Service_ETLData::getInstance()->populateDWResultsFromScenario($scenario);
        }
    }

    /**
     * Réinitialise un cube de DW donné.
     *
     * @param DW_Model_Cube $dWCube
     */
    protected function resetDWCube($dWCube)
    {
        $queryCube = new Core_Model_Query();
        $queryCube->filter->addCondition(DW_Model_Report::QUERY_CUBE, $dWCube);
        // Suppression des résultats.
        foreach (DW_Model_Result::loadList($queryCube) as $dWResult) {
            $dWResult->delete();
        }

        // Préparation à la copie des Reports.
        $dWReportsAsString = array();
        foreach (DW_Model_Report::loadList($queryCube) as $dWReport) {
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
            if ($dWRootAxis->getRef() !== 'set') {
                $dWRootAxis->delete();
            }
        }
        $entityManagers = Zend_Registry::get('EntityManagers');
        $entityManagers['default']->flush();

        $this->populateDWCubeWithClassifService($dWCube);
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