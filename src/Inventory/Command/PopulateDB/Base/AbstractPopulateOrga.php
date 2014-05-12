<?php

namespace Inventory\Command\PopulateDB\Base;

use Account\Domain\Account;
use Account\Domain\AccountRepository;
use AF\Domain\AF;
use AF\Domain\AFLibrary;
use AF\Domain\InputService;
use AF\Domain\Component\Component;
use AF\Domain\Component\Group;
use AF\Domain\Component\TextField;
use AF\Domain\Component\NumericField;
use AF\Domain\Component\Checkbox;
use AF\Domain\Component\Select\SelectSingle;
use AF\Domain\Component\Select\SelectMulti;
use AF\Domain\Component\SubAF\RepeatedSubAF;
use AF\Domain\Component\SubAF\NotRepeatedSubAF;
use AF\Domain\Input\TextFieldInput;
use AF\Domain\Input\NumericFieldInput;
use AF\Domain\Input\CheckboxInput;
use AF\Domain\Input\Select\SelectSingleInput;
use AF\Domain\Input\Select\SelectMultiInput;
use AF\Domain\InputSet\PrimaryInputSet;
use Core_Exception_NotFound;
use Doctrine\ORM\EntityManager;
use DW_Model_Axis;
use DW_Model_Cube;
use DW_Model_Filter;
use DW_Model_Indicator;
use DW_Model_Member;
use DW_Model_Report;
use MyCLabs\ACL\ACL;
use Orga\Model\ACL\CellAdminRole;
use Orga\Model\ACL\CellManagerRole;
use Orga\Model\ACL\CellContributorRole;
use Orga\Model\ACL\CellObserverRole;
use Orga\Model\ACL\OrganizationAdminRole;
use Orga_Model_Axis;
use Orga_Model_Granularity;
use Orga_Model_Member;
use Orga_Model_Organization;
use Orga_Service_ETLData;
use Orga_Service_OrganizationService;
use Symfony\Component\Console\Output\OutputInterface;
use User\Domain\User;
use User\Domain\UserService;

/**
 * Remplissage de la base de données avec des données de test
 */
abstract class AbstractPopulateOrga
{
    /**
     * @Inject
     * @var EntityManager
     */
    protected $entityManager;

    /**
     * @Inject
     * @var ACL
     */
    protected $acl;

    /**
     * @Inject
     * @var Orga_Service_OrganizationService
     */
    protected $organizationService;

    /**
     * @Inject
     * @var InputService
     */
    protected $inputService;

    /**
     * @Inject
     * @var Orga_Service_ETLData
     */
    protected $etlDataService;

    /**
     * @Inject
     * @var UserService
     */
    protected $userService;

    /**
     * @Inject
     * @var AccountRepository
     */
    protected $accountRepository;

    /**
     * @Inject("account.myc-sense")
     * @var Account
     */
    protected $publicAccount;

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

    abstract public function run(OutputInterface $output);

    /**
     * @param Account $account
     * @param string $label
     * @return Orga_Model_Organization
     */
    protected function createOrganization(Account $account, $label)
    {
        return $this->organizationService->createOrganization($account, $label);
    }

    /**
     * @param Orga_Model_Organization $organization
     * @param string $ref
     * @param string $label
     * @param Orga_Model_Axis $narrower
     * @return Orga_Model_Axis
     */
    protected function createAxis(Orga_Model_Organization $organization, $ref, $label, Orga_Model_Axis $narrower = null)
    {
        $axis = new Orga_Model_Axis($organization, $ref, $narrower);
        $axis->getLabel()->set($label, 'fr');
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
    protected function createMember(Orga_Model_Axis $axis, $ref, $label, array $parents = [])
    {
        $member = new Orga_Model_Member($axis, $ref, $parents);
        $member->getLabel()->set($label, 'fr');
        $member->save();
        return $member;
    }

    /**
     * @param Orga_Model_Organization $organization
     * @param array $axes
     * @param bool $relevance
     * @param bool $dWCubes
     * @param bool $acl
     * @return Orga_Model_Granularity
     */
    protected function createGranularity(
        Orga_Model_Organization $organization,
        array $axes = [],
        $relevance = false,
        $dWCubes = false,
        $acl = true
    ) {
        try {
            $granularity = $organization->getGranularityByRef(Orga_Model_Granularity::buildRefFromAxes($axes));
        } catch (Core_Exception_NotFound $e) {
            $granularity = new Orga_Model_Granularity($organization, $axes);
        }
        $granularity->setCellsControlRelevance($relevance);
        $granularity->setCellsGenerateDWCubes($dWCubes);
        $granularity->setCellsWithACL($acl);
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
     * @param Orga_Model_Member[]    $members
     * @param Orga_Model_Granularity $inputGranularity
     * @param string                 $afLabel
     */
    protected function setAFForChildCells(
        Orga_Model_Granularity $granularity,
        array $members,
        Orga_Model_Granularity $inputGranularity,
        $afLabel
    ) {
        $granularity->getCellByMembers($members)->getCellsGroupForInputGranularity($inputGranularity)
            ->setAF($this->getAF($afLabel));
    }

    /**
     * @param Orga_Model_Granularity $granularity
     * @param Orga_Model_Member[] $members
     * @param array $values
     * @param bool $finished
     */
    protected function setInput(Orga_Model_Granularity $granularity, array $members, array $values, $finished = false)
    {
        $inputCell = $granularity->getCellByMembers($members);
        $inputConfigGranularity = $granularity->getInputConfigGranularity();
        if ($granularity === $inputConfigGranularity) {
            $aF = $inputCell->getCellsGroupForInputGranularity($granularity)->getAF();
        } else {
            $aF = $inputCell->getParentCellForGranularity($inputConfigGranularity)->getCellsGroupForInputGranularity(
                $granularity
            )->getAF();
        }

        $inputSetPrimary = new PrimaryInputSet($aF);

        foreach ($values as $refComponent => $value) {
            $component = Component::loadByRef($refComponent, $aF);
            if (($component instanceof NotRepeatedSubAF)
                || ($component instanceof RepeatedSubAF)
                || ($component instanceof Group)) {
                continue;
            }

            if ($component instanceof NumericField) {
                // Champ numérique
                $inputType = NumericFieldInput::class;
            } elseif ($component instanceof TextField) {
                // Champ texte
                $inputType = TextFieldInput::class;
            } elseif ($component instanceof Checkbox) {
                // Champ checkbox
                $inputType = CheckboxInput::class;
            } elseif ($component instanceof SelectSingle) {
                // Champ de sélection simple
                $inputType = SelectSingleInput::class;
            } elseif ($component instanceof SelectMulti) {
                // Champ de sélection multiple
                $inputType = SelectMultiInput::class;
            }

            $input = new $inputType($inputSetPrimary, $component);
            $input->setValue($value);
        }

        $this->inputService->updateResults($inputSetPrimary);
        $inputSetPrimary->markAsFinished($finished);
        $inputSetPrimary->save();

        $inputCell->setAFInputSetPrimary($inputSetPrimary);
        $this->etlDataService->populateDWResultsFromCell($inputCell);
    }

    /**
     * @param DW_Model_Cube $cube
     * @param string $label
     * @param string $chartType
     * @param string $displayUncertainty
     * @param array $filters
     * @return DW_Model_Report
     */
    private function createReport(DW_Model_Cube $cube, $label, $chartType, $displayUncertainty, $filters = array())
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
    protected function createSimpleGranularityReport(
        Orga_Model_Granularity $granularity,
        $label,
        $refIndicator,
        $refAxis,
        $filters = array(),
        $displayUncertainty = false,
        $chartType = DW_Model_Report::CHART_PIE,
        $sortType = DW_Model_Report::SORT_VALUE_DECREASING
    ) {
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
    protected function createSimpleRatioGranularityReport(
        Orga_Model_Granularity $granularity,
        $label,
        $refNumeratorIndicator,
        $refNumeratorAxis,
        $refDenominatorIndicator,
        $refDenominatorAxis,
        $filters = array(),
        $displayUncertainty = false,
        $chartType = DW_Model_Report::CHART_PIE,
        $sortType = DW_Model_Report::SORT_VALUE_DECREASING
    ) {
        $report = $this->createReport($granularity->getDWCube(), $label, $chartType, $displayUncertainty, $filters);
        $report->setNumerator(DW_Model_Indicator::loadByRefAndCube($refNumeratorIndicator, $granularity->getDWCube()));
        $report->setNumeratorAxis1(DW_Model_Axis::loadByRefAndCube($refNumeratorAxis, $granularity->getDWCube()));
        $report->setDenominator(
            DW_Model_Indicator::loadByRefAndCube($refDenominatorIndicator, $granularity->getDWCube())
        );
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
    protected function createDoubleGranularityReport(
        Orga_Model_Granularity $granularity,
        $label,
        $refIndicator,
        $refAxis1,
        $refAxis2,
        $filters = array(),
        $displayUncertainty = false,
        $chartType = DW_Model_Report::CHART_VERTICAL_GROUPED
    ) {
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
    protected function createDoubleRatioGranularityReport(
        Orga_Model_Granularity $granularity,
        $label,
        $refNumeratorIndicator,
        $refNumeratorAxis1,
        $refNumeratorAxis2,
        $refDenominatorIndicator,
        $refDenominatorAxis1,
        $refDenominatorAxis2,
        $filters = array(),
        $displayUncertainty = false,
        $chartType = DW_Model_Report::CHART_VERTICAL_GROUPED
    ) {
        $report = $this->createReport($granularity->getDWCube(), $label, $chartType, $displayUncertainty, $filters);
        $report->setNumerator(DW_Model_Indicator::loadByRefAndCube($refNumeratorIndicator, $granularity->getDWCube()));
        $report->setNumeratorAxis1(DW_Model_Axis::loadByRefAndCube($refNumeratorAxis1, $granularity->getDWCube()));
        $report->setNumeratorAxis2(DW_Model_Axis::loadByRefAndCube($refNumeratorAxis2, $granularity->getDWCube()));
        $report->setDenominator(
            DW_Model_Indicator::loadByRefAndCube($refDenominatorIndicator, $granularity->getDWCube())
        );
        $report->setDenominatorAxis1(DW_Model_Axis::loadByRefAndCube($refDenominatorAxis1, $granularity->getDWCube()));
        $report->setDenominatorAxis2(DW_Model_Axis::loadByRefAndCube($refDenominatorAxis2, $granularity->getDWCube()));
        $report->save();
    }

    /**
     * @param $email
     */
    protected function createUser($email)
    {
        $this->userService->createUser($email, $email);
    }

    /**
     * @param $email
     * @param Orga_Model_Organization $organization
     */
    protected function addOrganizationAdministrator($email, Orga_Model_Organization $organization)
    {
        $user = User::loadByEmail($email);
        $this->acl->grant($user, new OrganizationAdminRole($user, $organization));
    }

    /**
     * @param $email
     * @param Orga_Model_Granularity $granularity
     * @param array $members
     */
    protected function addCellAdministrator($email, Orga_Model_Granularity $granularity, array $members)
    {
        $user = User::loadByEmail($email);
        $this->acl->grant($user, new CellAdminRole($user, $granularity->getCellByMembers($members)));
    }

    /**
     * @param $email
     * @param Orga_Model_Granularity $granularity
     * @param array $members
     */
    protected function addCellManager($email, Orga_Model_Granularity $granularity, array $members)
    {
        $user = User::loadByEmail($email);
        $this->acl->grant($user, new CellManagerRole($user, $granularity->getCellByMembers($members)));
    }

    /**
     * @param $email
     * @param Orga_Model_Granularity $granularity
     * @param array $members
     */
    protected function addCellContributor($email, Orga_Model_Granularity $granularity, array $members)
    {
        $user = User::loadByEmail($email);
        $this->acl->grant($user, new CellContributorRole($user, $granularity->getCellByMembers($members)));
    }

    /**
     * @param $email
     * @param Orga_Model_Granularity $granularity
     * @param array $members
     */
    protected function addCellObserver($email, Orga_Model_Granularity $granularity, array $members)
    {
        $user = User::loadByEmail($email);
        $this->acl->grant($user, new CellObserverRole($user, $granularity->getCellByMembers($members)));
    }

    /**
     * @param string $label
     * @return AF
     */
    protected function getAF($label)
    {
        // Moche : par du principe qu'il y'a 1 seule bibliothèque
        $afLibrary = AFLibrary::loadByAccount($this->publicAccount)[0];

        $query = new \Core_Model_Query();
        $query->filter->addCondition('library', $afLibrary);
        $query->filter->addCondition('label.fr', $label);

        return AF::loadList($query)[0];
    }
}
