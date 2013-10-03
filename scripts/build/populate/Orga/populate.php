<?php
/**
 * @package Orga
 */


/**
 * Remplissage de la base de données avec des données de test
 * @package Orga
 */
class Orga_Populate extends Core_Script_Action
{
    /**
     * {@inheritdoc}
     */
    public function runEnvironment($environment)
    {
        $entityManagers = Zend_Registry::get('EntityManagers');
        /** @var $entityManager \Doctrine\ORM\EntityManager */
        $entityManager = $entityManagers['default'];


        // Création d'une organisation.
        //  + createOrganization : -
        // Param : label

        // Création des axes.
        //  + createAxis : -
        // Params : Organization, ref, label
        // OptionalParams : Axis parent=null

        // Création des membres.
        //  + createMember : -
        // Params : Axis, ref, label
        // OptionalParams : [Member] parents=[]

        // Création des granularités.
        //  + createGranularity : -
        // Params : Organization, axes[Axis], navigable
        // OptionalParams : orgaTab=false, aCL=true, aFTab=false, dWCubes=false, genericAction=false, contextAction=false, inputDocs=false

        // Paramétrage des cellules.
        // Params : Granularity granularity, [Member] members
        //  + setInventoryStatus : granularityStatus (Orga_Model_Cell::STATUS_)
        //  + setAFForChildCells : Granularity inputGranularity, refAF
        //  + setInput: [refComponent => mixed value]
        // OptionalParams : -
        //  + setInventoryStatus : -
        //  + setAFForChildCells : -
        //  + setInput: finished=false


        $entityManager->flush();


        // Création de rapport personnalisés.
        // Params : Granularity granularity
        //  + createSimpleGranularityReport : refIndicator, refAxis
        //  + createSimbleRatioGranularityReport : refNumeratorIndicator, refNumeratorAxis, refDenominatorIndicator, refDenominatorAxis
        //  + createDoubleGranularityReport : refIndicator, refAxis1, refAxis2
        //  + createDoubleRatioGranularityReport : refNumeratorIndicator, refNumeratorAxis1, refNumeratorAxis2, refDenominatorIndicator, refDenominatorAxis1, refDenominatorAxis2
        // OptionalParams : [refAxis => [refMember]] filters, displayUncertainty=false
        //  + createSimpleGranularityReport : chartType=DW_Model_Report::CHART_PIE, sortType=DW_Model_Report::SORT_VALUE_DECREASING
        //  + createSimbleRatioGranularityReport : chartType=DW_Model_Report::CHART_PIE, sortType=DW_Model_Report::SORT_VALUE_DECREASING
        //  + createDoubleGranularityReport : chartType=DW_Model_Report::CHART_VERTICAL_GROUPED
        //  + createDoubleRatioGranularityReport : chartType=DW_Model_Report::CHART_VERTICAL_GROUPED

        // Création des utilisateurs orga.
        //  + createUser: -
        // Params : email

        // Ajout d'un role d'administrateur d'organisation à un utilisateur existant.
        //  + addOrganizationAdministrator: -
        // Params : email, Organization

        // Ajout d'un role sur une cellule à un utilisateur existant.
        //  + addCellAdministrator : -
        //  + addCellContributor : -
        //  + addCellObserver : -
        // Params : email, Granularity, [Member]


        $entityManager->flush();

        echo "\t\tOrganization created".PHP_EOL;
    }

    /**
     * @param string $label
     * @return Orga_Model_Organization
     */
    protected function createOrganization($label)
    {
        $organization = new Orga_Model_Organization();
        $organization->setLabel($label);
        $organization->save();
        return $organization;
    }

    /**
     * @param Orga_Model_Organization $organization
     * @param string $ref
     * @param string $label
     * @param Orga_Model_Axis $narrower
     * @return Orga_Model_Axis
     */
    protected function createAxis(Orga_Model_Organization $organization, $ref, $label, Orga_Model_Axis $narrower=null)
    {
        $axis = new Orga_Model_Axis($organization);
        $axis->setRef($ref);
        $axis->setLabel($label);
        if ($narrower !== null) {
            $axis->setDirectNarrower($narrower);
        }
        $axis->save();
        return $axis;
    }

    /**
     * @param Orga_Model_Axis $axis
     * @param string $ref
     * @param string $label
     * @param array $parents
     * @return Orga_Model_Member
     */
    protected function createMember(Orga_Model_Axis $axis, $ref, $label, array $parents=[])
    {
        $member = new Orga_Model_Member($axis);
        $member->setRef($ref);
        $member->setLabel($label);
        foreach ($parents as $directParent)
        {
            $member->addDirectParent($directParent);
        }
        $member->save();
        return $member;
    }

    /**
     * @param Orga_Model_Organization $organization
     * @param array $axes
     * @param bool $navigable
     * @param bool $orgaTab
     * @param bool $aCL
     * @param bool $aFTab
     * @param bool $dWCubes
     * @param bool $genericAction
     * @param bool $contextAction
     * @param bool $inputDocs
     * @return Orga_Model_Granularity
     */
    protected function createGranularity(Orga_Model_Organization $organization, array $axes=[], $navigable,
        $orgaTab=false, $aCL=true, $aFTab=false, $dWCubes=false, $genericAction=false, $contextAction=false, $inputDocs=false)
    {
        $granularity = new Orga_Model_Granularity($organization, $axes);
        $granularity->setNavigability($navigable);
        $granularity->setCellsWithOrgaTab($orgaTab);
        $granularity->setCellsWithACL($aCL);
        $granularity->setCellsWithAFConfigTab($aFTab);
        $granularity->setCellsGenerateDWCubes($dWCubes);
        $granularity->setCellsWithSocialGenericActions($genericAction);
        $granularity->setCellsWithSocialContextActions($contextAction);
        $granularity->setCellsWithInputDocuments($inputDocs);
        $granularity->save();
        return $granularity;
    }

    /**
     * @param Orga_Model_Granularity $granularity
     * @param Orga_Model_Member[] $members
     * @param $inventoryStatus
     */
    protected function setInventoryStatus(Orga_Model_Granularity $granularity, array $members, $inventoryStatus)
    {
        if ($granularity === $granularity->getOrganization()->getGranularityForInventoryStatus()) {
            $granularity->getCellByMembers($members)->setInventoryStatus($inventoryStatus);
        }
    }

    /**
     * @param Orga_Model_Granularity $granularity
     * @param Orga_Model_Member[] $members
     * @param Orga_Model_Granularity $inputGranularity
     * @param string $refAF
     */
    protected function setAFForChildCells(Orga_Model_Granularity $granularity, array $members, Orga_Model_Granularity $inputGranularity, $refAF)
    {
        $granularity->getCellByMembers($members)->getCellsGroupForInputGranularity($inputGranularity)->setAF(AF_Model_AF::loadByRef($refAF));
    }

    /**
     * @param Orga_Model_Granularity $granularity
     * @param Orga_Model_Member[] $members
     * @param array $values
     * @param bool $finished
     */
    protected function setInput(Orga_Model_Granularity $granularity, array $members, array $values, $finished=false)
    {
        $container = Zend_Registry::get('container');

        $inputCell = $granularity->getCellByMembers($members);
        $inputConfigGranularity = $granularity->getInputConfigGranularity();
        if ($granularity === $inputConfigGranularity) {
            $aF = $inputCell->getCellsGroupForInputGranularity($granularity)->getAF();
        } else {
            $aF = $inputCell->getParentCellForGranularity($inputConfigGranularity)->getCellsGroupForInputGranularity($granularity)->getAF();
        }

        $inputSetPrimary = new AF_Model_InputSet_Primary($aF);

        foreach ($values as $refComponent => $value) {
            $component = AF_Model_Component::loadByRef($refComponent, $aF);
            if (($component instanceof AF_Model_Component_SubAF_NotRepeated)
                || ($component instanceof AF_Model_Component_SubAF_Repeated)
                || ($component instanceof AF_Model_Component_Group)) {
                continue;
            }

            if ($component instanceof AF_Model_Component_Numeric) {
                // Champ numérique
                $inputType = 'AF_Model_Input_Numeric';
            } elseif ($component instanceof AF_Model_Component_Text) {
                // Champ texte
                $inputType = 'AF_Model_Input_Text';
            } elseif ($component instanceof AF_Model_Component_Checkbox) {
                // Champ checkbox
                $inputType = 'AF_Model_Input_Checkbox';
            } elseif ($component instanceof AF_Model_Component_Select_Single) {
                // Champ de sélection simple
                $inputType = 'AF_Model_Input_Select_Single';
            } elseif ($component instanceof AF_Model_Component_Select_Multi) {
                // Champ de sélection multiple
                $inputType = 'AF_Model_Input_Select_Multi';
            }

            $input = new $inputType($inputSetPrimary, $component);
            $input->setValue($value);
        }

        /* @var AF_Service_InputService $inputService */
        $inputService = $container->get('AF_Service_InputService');
        $inputService->updateResults($inputSetPrimary);
        $inputSetPrimary->markAsFinished($finished);
        $inputSetPrimary->save();

        $inputCell->setAFInputSetPrimary($inputSetPrimary);
        /* @var Orga_Service_ETLData $eTLDataService */
        $eTLDataService = $container->get('Orga_Service_ETLData');
        $eTLDataService->populateDWResultsFromCell($inputCell);
    }

    /**
     * @param DW_Model_Cube $cube
     * @param string $label
     * @param string $chartType
     * @param string $displayUncertainty
     * @param array $filters
     * @return DW_Model_Report
     */
    private function createReport(DW_Model_Cube $cube, $label, $chartType, $displayUncertainty, $filters=array())
    {
        $report = new DW_Model_Report($cube);
        $report->setLabel($label);
        $report->setChartType($chartType);
        $report->setWithUncertainty($displayUncertainty);
        foreach ($filters as $refAxis => $membersFiltered) {
            $axis = DW_Model_Axis::loadByRefAndCube($refAxis, $cube);
            $filter = new DW_Model_Filter($report, $axis);
            foreach ($membersFiltered as $refMember) {
                $filter->addMember(DW_Model_Member::loadByRefAndAxis($refMember, $axis));
            }
            $report->addFilter($filter);
        }
        return $report;
    }

    /**
     * @param Orga_Model_Granularity $granularity
     * @param string $label
     * @param string $refIndicator
     * @param string $refAxis
     * @param array $filters
     * @param bool $displayUncertainty
     * @param string $chartType
     * @param string $sortType
     */
    protected function createSimpleGranularityReport(Orga_Model_Granularity $granularity, $label, $refIndicator, $refAxis,
        $filters=array(), $displayUncertainty=false, $chartType=DW_Model_Report::CHART_PIE, $sortType=DW_Model_Report::SORT_VALUE_DECREASING)
    {
        $report = $this->createReport($granularity->getDWCube(), $label, $chartType, $displayUncertainty, $filters);
        $report->setNumerator(DW_Model_Indicator::loadByRefAndCube($refIndicator, $granularity->getDWCube()));
        $report->setNumeratorAxis1(DW_Model_Axis::loadByRefAndCube($refAxis, $granularity->getDWCube()));
        $report->setSortType($sortType);
        $report->save();
    }

    /**
     * @param Orga_Model_Granularity $granularity
     * @param string $label
     * @param string $refNumeratorIndicator
     * @param string $refNumeratorAxis
     * @param string $refDenominatorIndicator
     * @param string $refDenominatorAxis
     * @param array $filters
     * @param bool $displayUncertainty
     * @param string $chartType
     * @param string $sortType
     */
    protected function createSimpleRatioGranularityReport(Orga_Model_Granularity $granularity, $label,
        $refNumeratorIndicator, $refNumeratorAxis,
        $refDenominatorIndicator, $refDenominatorAxis,
        $filters=array(), $displayUncertainty=false, $chartType=DW_Model_Report::CHART_PIE, $sortType=DW_Model_Report::SORT_VALUE_DECREASING)
    {
        $report = $this->createReport($granularity->getDWCube(), $label, $chartType, $displayUncertainty, $filters);
        $report->setNumerator(DW_Model_Indicator::loadByRefAndCube($refNumeratorIndicator, $granularity->getDWCube()));
        $report->setNumeratorAxis1(DW_Model_Axis::loadByRefAndCube($refNumeratorAxis, $granularity->getDWCube()));
        $report->setDenominator(DW_Model_Indicator::loadByRefAndCube($refDenominatorIndicator, $granularity->getDWCube()));
        $report->setDenominatorAxis1(DW_Model_Axis::loadByRefAndCube($refDenominatorAxis, $granularity->getDWCube()));
        $report->setSortType($sortType);
        $report->save();
    }

    /**
     * @param Orga_Model_Granularity $granularity
     * @param string $label
     * @param string $refIndicator
     * @param string $refAxis1
     * @param string $refAxis2
     * @param array $filters
     * @param bool $displayUncertainty
     * @param string $chartType
     */
    protected function createDoubleGranularityReport(Orga_Model_Granularity $granularity, $label,
        $refIndicator, $refAxis1, $refAxis2,
        $filters=array(), $displayUncertainty=false, $chartType=DW_Model_Report::CHART_VERTICAL_GROUPED)
    {
        $report = $this->createReport($granularity->getDWCube(), $label, $chartType, $displayUncertainty, $filters);
        $report->setNumerator(DW_Model_Indicator::loadByRefAndCube($refIndicator, $granularity->getDWCube()));
        $report->setNumeratorAxis1(DW_Model_Axis::loadByRefAndCube($refAxis1, $granularity->getDWCube()));
        $report->setNumeratorAxis2(DW_Model_Axis::loadByRefAndCube($refAxis2, $granularity->getDWCube()));
        $report->setWithUncertainty($displayUncertainty);
        $report->save();
    }

    /**
     * @param Orga_Model_Granularity $granularity
     * @param string $label
     * @param string $refNumeratorIndicator
     * @param string $refNumeratorAxis1
     * @param string $refNumeratorAxis2
     * @param string $refDenominatorIndicator
     * @param string $refDenominatorAxis1
     * @param string $refDenominatorAxis2
     * @param array $filters
     * @param bool $displayUncertainty
     * @param string $chartType
     */
    protected function createDoubleRatioGranularityReport(Orga_Model_Granularity $granularity, $label,
        $refNumeratorIndicator, $refNumeratorAxis1, $refNumeratorAxis2,
        $refDenominatorIndicator, $refDenominatorAxis1, $refDenominatorAxis2,
        $filters=array(), $displayUncertainty=false, $chartType=DW_Model_Report::CHART_VERTICAL_GROUPED)
    {
        $report = $this->createReport($granularity->getDWCube(), $label, $chartType, $displayUncertainty, $filters);
        $report->setNumerator(DW_Model_Indicator::loadByRefAndCube($refNumeratorIndicator, $granularity->getDWCube()));
        $report->setNumeratorAxis1(DW_Model_Axis::loadByRefAndCube($refNumeratorAxis1, $granularity->getDWCube()));
        $report->setNumeratorAxis2(DW_Model_Axis::loadByRefAndCube($refNumeratorAxis2, $granularity->getDWCube()));
        $report->setDenominator(DW_Model_Indicator::loadByRefAndCube($refDenominatorIndicator, $granularity->getDWCube()));
        $report->setDenominatorAxis1(DW_Model_Axis::loadByRefAndCube($refDenominatorAxis1, $granularity->getDWCube()));
        $report->setDenominatorAxis2(DW_Model_Axis::loadByRefAndCube($refDenominatorAxis2, $granularity->getDWCube()));
        $report->save();
    }

    /**
     * @param $email
     */
    protected function createUser($email)
    {
        /** @var DI\Container $container */
        $container = Zend_Registry::get('container');
        $container->get('User_Service_User')->createUser($email, $email);
    }

    /**
     * @param $email
     * @param Orga_Model_Organization $organization
     */
    protected function addOrganizationAdministrator($email, Orga_Model_Organization $organization)
    {
        $user = User_Model_User::loadByEmail($email);
        /** @var DI\Container $container */
        $container = Zend_Registry::get('container');
        $container->get('Orga_Service_ACLManager')->addOrganizationAdministrator($organization, $user, false);
    }

    /**
     * @param $email
     * @param Orga_Model_Granularity $granularity
     * @param array $members
     */
    protected function addCellAdministrator($email, Orga_Model_Granularity $granularity, array $members)
    {
        $this->addUserToCell('administrator', $email, $granularity, $members);
    }

    /**
     * @param $email
     * @param Orga_Model_Granularity $granularity
     * @param array $members
     */
    protected function addCellContributor($email, Orga_Model_Granularity $granularity, array $members)
    {
        $this->addUserToCell('contributor', $email, $granularity, $members);
    }

    /**
     * @param $email
     * @param Orga_Model_Granularity $granularity
     * @param array $members
     */
    protected function addCellObserver($email, Orga_Model_Granularity $granularity, array $members)
    {
        $this->addUserToCell('observer', $email, $granularity, $members);
    }

    /**
     * @param $role
     * @param $email
     * @param Orga_Model_Granularity $granularity
     * @param array $members
     */
    protected function addUserToCell($role, $email, Orga_Model_Granularity $granularity, array $members)
    {
        $cell = $granularity->getCellByMembers($members);

        $user = User_Model_User::loadByEmail($email);
        $user->addRole(User_Model_Role::loadByRef('cell'.ucfirst(strtolower($role)).'_'.$cell->getId()));
    }

}
