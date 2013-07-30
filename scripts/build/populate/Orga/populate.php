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
        //  + setAFForChildCells : Granularity inputGranularity, AF aF
        // OptionalParams : -
        //  + setInventoryStatus : -
        //  + setAFForChildCells : -


        $entityManager->flush();


        // Création de rapport personnalisés.
        // Params : Granularity granularity
        //  + createSimpleGranularityReport : refIndicator, refAxis
        //  + createSimbleRatioGranularityReport : refNumeratorIndicator, refNumeratorAxis, refDenominatorIndicator, refDenominatorAxis
        //  + createDoubleGranularityReport : refIndicator, refAxis1, refAxis2
        //  + createDoubleRatioGranularityReport : refNumeratorIndicator, refNumeratorAxis1, refNumeratorAxis2, refDenominatorIndicator, refDenominatorAxis1, refDenominatorAxis2
        // OptionalParams : displayUncertainty=false
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
     * @param AF_Model_AF $aF
     */
    protected function setAFForChildCells(Orga_Model_Granularity $granularity, array $members, Orga_Model_Granularity $inputGranularity, AF_Model_AF $aF)
    {
        $granularity->getCellByMembers($members)->getCellsGroupForInputGranularity($inputGranularity)->getAF($aF);
    }

    /**
     * @param Orga_Model_Granularity $granularity
     * @param string $refIndicator
     * @param string $refAxis
     * @param bool $displayUncertainty
     * @param string $chartType
     * @param string $sortType
     */
    protected function createSimpleGranularityReport(Orga_Model_Granularity $granularity, $refIndicator, $refAxis,
        $displayUncertainty=false, $chartType=DW_Model_Report::CHART_PIE, $sortType=DW_Model_Report::SORT_VALUE_DECREASING)
    {
        $report = new DW_Model_Report($granularity->getDWCube());
        $report->setNumerator(DW_Model_Indicator::loadByRefAndCube($refIndicator, $granularity->getDWCube()));
        $report->setNumeratorAxis1(DW_Model_Axis::loadByRefAndCube($refAxis, $granularity->getDWCube()));
        $report->setWithUncertainty($displayUncertainty);
        $report->setChartType($chartType);
        $report->setSortType($sortType);
        $report->save();
    }

    /**
     * @param Orga_Model_Granularity $granularity
     * @param string $refNumeratorIndicator
     * @param string $refNumeratorAxis
     * @param string $refDenominatorIndicator
     * @param string $refDenominatorAxis
     * @param bool $displayUncertainty
     * @param string $chartType
     * @param string $sortType
     */
    protected function createSimpleRatioGranularityReport(Orga_Model_Granularity $granularity,
        $refNumeratorIndicator, $refNumeratorAxis,
        $refDenominatorIndicator, $refDenominatorAxis,
        $displayUncertainty=false, $chartType=DW_Model_Report::CHART_PIE, $sortType=DW_Model_Report::SORT_VALUE_DECREASING)
    {
        $report = new DW_Model_Report($granularity->getDWCube());
        $report->setNumerator(DW_Model_Indicator::loadByRefAndCube($refNumeratorIndicator, $granularity->getDWCube()));
        $report->setNumeratorAxis1(DW_Model_Axis::loadByRefAndCube($refNumeratorAxis, $granularity->getDWCube()));
        $report->setDenominator(DW_Model_Indicator::loadByRefAndCube($refDenominatorIndicator, $granularity->getDWCube()));
        $report->setDenominatorAxis1(DW_Model_Axis::loadByRefAndCube($refDenominatorAxis, $granularity->getDWCube()));
        $report->setWithUncertainty($displayUncertainty);
        $report->setChartType($chartType);
        $report->setSortType($sortType);
        $report->save();
    }

    /**
     * @param Orga_Model_Granularity $granularity
     * @param string $refIndicator
     * @param string $refAxis1
     * @param string $refAxis2
     * @param bool $displayUncertainty
     * @param string $chartType
     */
    protected function createDoubleGranularityReport(Orga_Model_Granularity $granularity, $refIndicator, $refAxis1, $refAxis2,
        $displayUncertainty=false, $chartType=DW_Model_Report::CHART_VERTICAL_GROUPED)
    {
        $report = new DW_Model_Report($granularity->getDWCube());
        $report->setNumerator(DW_Model_Indicator::loadByRefAndCube($refIndicator, $granularity->getDWCube()));
        $report->setNumeratorAxis1(DW_Model_Axis::loadByRefAndCube($refAxis1, $granularity->getDWCube()));
        $report->setNumeratorAxis2(DW_Model_Axis::loadByRefAndCube($refAxis2, $granularity->getDWCube()));
        $report->setWithUncertainty($displayUncertainty);
        $report->setChartType($chartType);
        $report->save();
    }

    /**
     * @param Orga_Model_Granularity $granularity
     * @param string $refNumeratorIndicator
     * @param string $refNumeratorAxis1
     * @param string $refNumeratorAxis2
     * @param string $refDenominatorIndicator
     * @param string $refDenominatorAxis1
     * @param string $refDenominatorAxis2
     * @param bool $displayUncertainty
     * @param string $chartType
     */
    protected function createDoubleRatioGranularityReport(Orga_Model_Granularity $granularity,
        $refNumeratorIndicator, $refNumeratorAxis1, $refNumeratorAxis2,
        $refDenominatorIndicator, $refDenominatorAxis1, $refDenominatorAxis2,
        $displayUncertainty=false, $chartType=DW_Model_Report::CHART_VERTICAL_GROUPED)
    {
        $report = new DW_Model_Report($granularity->getDWCube());
        $report->setNumerator(DW_Model_Indicator::loadByRefAndCube($refNumeratorIndicator, $granularity->getDWCube()));
        $report->setNumeratorAxis1(DW_Model_Axis::loadByRefAndCube($refNumeratorAxis1, $granularity->getDWCube()));
        $report->setNumeratorAxis2(DW_Model_Axis::loadByRefAndCube($refNumeratorAxis2, $granularity->getDWCube()));
        $report->setDenominator(DW_Model_Indicator::loadByRefAndCube($refDenominatorIndicator, $granularity->getDWCube()));
        $report->setDenominatorAxis1(DW_Model_Axis::loadByRefAndCube($refDenominatorAxis1, $granularity->getDWCube()));
        $report->setDenominatorAxis2(DW_Model_Axis::loadByRefAndCube($refDenominatorAxis2, $granularity->getDWCube()));
        $report->setWithUncertainty($displayUncertainty);
        $report->setChartType($chartType);
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
        $container->get('Orga_Service_ACLManager')->addOrganizationAdministrator($organization, $user);
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
