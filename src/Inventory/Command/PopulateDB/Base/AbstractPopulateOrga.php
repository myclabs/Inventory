<?php

namespace Inventory\Command\PopulateDB\Base;

use Account\Domain\Account;
use Account\Domain\AccountRepository;
use AF\Domain\AF;
use AF\Domain\AFLibrary;
use AF\Domain\Input\Input;
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
use DW\Domain\Cube;
use DW\Domain\Filter;
use DW\Domain\Report;
use MyCLabs\ACL\ACL;
use Orga\Domain\ACL\CellAdminRole;
use Orga\Domain\ACL\CellManagerRole;
use Orga\Domain\ACL\CellContributorRole;
use Orga\Domain\ACL\CellObserverRole;
use Orga\Domain\ACL\WorkspaceAdminRole;
use Orga\Domain\Axis;
use Orga\Domain\Granularity;
use Orga\Domain\Member;
use Orga\Domain\Workspace;
use Orga\Domain\Service\ETL\ETLDataService;
use Orga\Application\Service\Workspace\WorkspaceService;
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
     * @var \Orga\Application\Service\Workspace\WorkspaceService
     */
    protected $workspaceService;

    /**
     * @Inject
     * @var InputService
     */
    protected $inputService;

    /**
     * @Inject
     * @var \Orga\Domain\Service\ETL\ETLDataService
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

    // Création d'un workspace.
    //  + createWorkspace : -
    // Param : label

    // Création des axes.
    //  + createAxis : -
    // Params : Workspace, ref, label
    // OptionalParams : Axis parent=null

    // Création des membres.
    //  + createMember : -
    // Params : Axis, ref, label
    // OptionalParams : [Member] parents=[]

    // Création des granularités.
    //  + createGranularity : -
    // Params : Workspace, axes[Axis], navigable
    // OptionalParams : orgaTab=false, aCL=true, aFTab=false, dWCubes=false, genericAction=false, contextAction=false, inputDocs=false

    // Paramétrage des cellules.
    // Params : Granularity granularity, [Member] members
    //  + setInventoryStatus : granularityStatus (Cell::STATUS_)
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
    //  + createSimpleGranularityReport : chartType=Report::CHART_PIE, sortType=Report::SORT_VALUE_DECREASING
    //  + createSimbleRatioGranularityReport : chartType=Report::CHART_PIE, sortType=Report::SORT_VALUE_DECREASING
    //  + createDoubleGranularityReport : chartType=Report::CHART_VERTICAL_GROUPED
    //  + createDoubleRatioGranularityReport : chartType=Report::CHART_VERTICAL_GROUPED

    // Création des utilisateurs orga.
    //  + createUser: -
    // Params : email

    // Ajout d'un role d'administrateur de workspace à un utilisateur existant.
    //  + addWorkspaceAdministrator: -
    // Params : email, Workspace

    // Ajout d'un role sur une cellule à un utilisateur existant.
    //  + addCellAdministrator : -
    //  + addCellContributor : -
    //  + addCellObserver : -
    // Params : email, Granularity, [Member]

    abstract public function run(OutputInterface $output);

    /**
     * @param Account $account
     * @param string $label
     * @return Workspace
     */
    protected function createWorkspace(Account $account, $label)
    {
        return $this->workspaceService->create($account, $label);
    }

    /**
     * @param Workspace $workspace
     * @param string $ref
     * @param string $label
     * @param \Orga\Domain\Axis $narrower
     * @param bool $positioning
     * @return Axis
     */
    protected function createAxis(Workspace $workspace, $ref, $label,
        Axis $narrower = null, $positioning = false)
    {
        $axis = new Axis($workspace, $ref, $narrower);
        $axis->getLabel()->set($label, 'fr');
        $axis->setMemberPositioning($positioning);
        $axis->save();
        return $axis;
    }

    /**
     * @param \Orga\Domain\Axis $axis
     * @param string $ref
     * @param string $label
     * @param array $parents
     * @return Member
     */
    protected function createMember(Axis $axis, $ref, $label, array $parents = [])
    {
        $member = new Member($axis, $ref, $parents);
        $member->getLabel()->set($label, 'fr');
        $member->save();
        return $member;
    }

    /**
     * @param Workspace $workspace
     * @param array $axes
     * @param bool $relevance
     * @param bool $monitoring
     * @param bool $dWCubes
     * @param bool $acl
     * @return \Orga\Domain\Granularity
     */
    protected function createGranularity(
        Workspace $workspace,
        array $axes = [],
        $relevance = false,
        $monitoring = false,
        $dWCubes = false,
        $acl = true
    ) {
        try {
            $granularity = $workspace->getGranularityByRef(Granularity::buildRefFromAxes($axes));
        } catch (Core_Exception_NotFound $e) {
            $granularity = new Granularity($workspace, $axes);
        }
        $granularity->setCellsControlRelevance($relevance);
        $granularity->setCellsMonitorInventory($monitoring);
        $granularity->setCellsGenerateDWCubes($dWCubes);
        $granularity->setCellsWithACL($acl);
        $granularity->save();
        return $granularity;
    }

    /**
     * @param \Orga\Domain\Granularity $granularity
     * @param \Orga\Domain\Member[] $members
     * @param $inventoryStatus
     */
    protected function setInventoryStatus(Granularity $granularity, array $members, $inventoryStatus)
    {
        if ($granularity === $granularity->getWorkspace()->getGranularityForInventoryStatus()) {
            $granularity->getCellByMembers($members)->setInventoryStatus($inventoryStatus);
        }
    }

    /**
     * @param \Orga\Domain\Granularity $granularity
     * @param Member[]    $members
     * @param \Orga\Domain\Granularity $inputGranularity
     * @param string                 $afLabel
     */
    protected function setAFForChildCells(
        Granularity $granularity,
        array $members,
        Granularity $inputGranularity,
        $afLabel
    ) {
        $granularity->getCellByMembers($members)->getSubCellsGroupForInputGranularity($inputGranularity)
            ->setAF($this->getAF($afLabel));
    }

    /**
     * @param \Orga\Domain\Granularity $granularity
     * @param Member[] $members
     * @param array $values
     * @param bool $finished
     */
    protected function setInput(Granularity $granularity, array $members, array $values, $finished = false)
    {
        $inputCell = $granularity->getCellByMembers($members);
        $inputConfigGranularity = $granularity->getInputConfigGranularity();
        if ($granularity === $inputConfigGranularity) {
            $aF = $inputCell->getSubCellsGroupForInputGranularity($granularity)->getAF();
        } else {
            $aF = $inputCell->getParentCellForGranularity($inputConfigGranularity)->getSubCellsGroupForInputGranularity(
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

            /** @var Input $input */
            $input = new $inputType($inputSetPrimary, $component);
            $inputSetPrimary->setInputForComponent($component, $input);
            $input->setValue($value);
        }

        $this->inputService->updateResults($inputSetPrimary);
        $inputSetPrimary->markAsFinished($finished);
        $inputSetPrimary->save();

        $inputCell->setAFInputSetPrimary($inputSetPrimary);
        $inputCell->updateInputStatus();
        $this->etlDataService->populateDWCubesWithCellInputResults($inputCell);
    }

    /**
     * @param \DW\Domain\Cube $cube
     * @param string $label
     * @param string $chartType
     * @param string $displayUncertainty
     * @param array $filters
     * @return Report
     */
    private function createReport(Cube $cube, $label, $chartType, $displayUncertainty, $filters = array())
    {
        $report = new Report($cube);
        $report->getLabel()->set($label, 'fr');
        $report->setChartType($chartType);
        $report->setWithUncertainty($displayUncertainty);
        foreach ($filters as $refAxis => $membersFiltered) {
            $axis = $cube->getAxisByRef($refAxis, $cube);
            $filter = new Filter($report, $axis);
            foreach ($membersFiltered as $refMember) {
                $filter->addMember($axis->getMemberByRef($refMember));
            }
        }
        return $report;
    }

    /**
     * @param \Orga\Domain\Granularity $granularity
     * @param string $label
     * @param string $refIndicator
     * @param string $refAxis
     * @param array $filters
     * @param bool $displayUncertainty
     * @param string $chartType
     * @param string $sortType
     */
    protected function createSimpleGranularityReport(
        Granularity $granularity,
        $label,
        $refIndicator,
        $refAxis,
        $filters = array(),
        $displayUncertainty = false,
        $chartType = Report::CHART_PIE,
        $sortType = Report::SORT_VALUE_DECREASING
    ) {
        $report = $this->createReport($granularity->getDWCube(), $label, $chartType, $displayUncertainty, $filters);
        $report->setNumeratorIndicator($granularity->getDWCube()->getIndicatorByRef($refIndicator));
        $report->setNumeratorAxis1($granularity->getDWCube()->getAxisByRef($refAxis));
        $report->setSortType($sortType);
        $report->save();
    }

    /**
     * @param \Orga\Domain\Granularity $granularity
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
        Granularity $granularity,
        $label,
        $refNumeratorIndicator,
        $refNumeratorAxis,
        $refDenominatorIndicator,
        $refDenominatorAxis,
        $filters = array(),
        $displayUncertainty = false,
        $chartType = Report::CHART_PIE,
        $sortType = Report::SORT_VALUE_DECREASING
    ) {
        $report = $this->createReport($granularity->getDWCube(), $label, $chartType, $displayUncertainty, $filters);
        $report->setNumeratorIndicator($granularity->getDWCube()->getIndicatorByRef($refNumeratorIndicator));
        $report->setNumeratorAxis1($granularity->getDWCube()->getAxisByRef($refNumeratorAxis));
        $report->setDenominatorIndicator($granularity->getDWCube()->getIndicatorByRef($refDenominatorIndicator));
        $report->setDenominatorAxis1($granularity->getDWCube()->getAxisByRef($refDenominatorAxis));
        $report->setSortType($sortType);
        $report->save();
    }

    /**
     * @param \Orga\Domain\Granularity $granularity
     * @param string $label
     * @param string $refIndicator
     * @param string $refAxis1
     * @param string $refAxis2
     * @param array $filters
     * @param bool $displayUncertainty
     * @param string $chartType
     */
    protected function createDoubleGranularityReport(
        Granularity $granularity,
        $label,
        $refIndicator,
        $refAxis1,
        $refAxis2,
        $filters = array(),
        $displayUncertainty = false,
        $chartType = Report::CHART_VERTICAL_GROUPED
    ) {
        $report = $this->createReport($granularity->getDWCube(), $label, $chartType, $displayUncertainty, $filters);
        $report->setNumeratorIndicator($granularity->getDWCube()->getIndicatorByRef($refIndicator, $granularity->getDWCube()));
        $report->setNumeratorAxis1($granularity->getDWCube()->getAxisByRef($refAxis1));
        $report->setNumeratorAxis2($granularity->getDWCube()->getAxisByRef($refAxis2));
        $report->setWithUncertainty($displayUncertainty);
        $report->save();
    }

    /**
     * @param \Orga\Domain\Granularity $granularity
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
        Granularity $granularity,
        $label,
        $refNumeratorIndicator,
        $refNumeratorAxis1,
        $refNumeratorAxis2,
        $refDenominatorIndicator,
        $refDenominatorAxis1,
        $refDenominatorAxis2,
        $filters = array(),
        $displayUncertainty = false,
        $chartType = Report::CHART_VERTICAL_GROUPED
    ) {
        $report = $this->createReport($granularity->getDWCube(), $label, $chartType, $displayUncertainty, $filters);
        $report->setNumeratorIndicator($granularity->getDWCube()->getIndicatorByRef($refNumeratorIndicator));
        $report->setNumeratorAxis1($granularity->getDWCube()->getAxisByRef($refNumeratorAxis1));
        $report->setNumeratorAxis2($granularity->getDWCube()->getAxisByRef($refNumeratorAxis2));
        $report->setDenominatorIndicator($granularity->getDWCube()->getIndicatorByRef($refDenominatorIndicator));
        $report->setDenominatorAxis1($granularity->getDWCube()->getAxisByRef($refDenominatorAxis1));
        $report->setDenominatorAxis2($granularity->getDWCube()->getAxisByRef($refDenominatorAxis2));
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
     * @param \Orga\Domain\Workspace $workspace
     */
    protected function addWorkspaceAdministrator($email, Workspace $workspace)
    {
        $user = User::loadByEmail($email);
        $this->acl->grant($user, new WorkspaceAdminRole($user, $workspace));
    }

    /**
     * @param $email
     * @param Granularity $granularity
     * @param array $members
     */
    protected function addCellAdministrator($email, Granularity $granularity, array $members)
    {
        $user = User::loadByEmail($email);
        $this->acl->grant($user, new CellAdminRole($user, $granularity->getCellByMembers($members)));
    }

    /**
     * @param $email
     * @param \Orga\Domain\Granularity $granularity
     * @param array $members
     */
    protected function addCellManager($email, Granularity $granularity, array $members)
    {
        $user = User::loadByEmail($email);
        $this->acl->grant($user, new CellManagerRole($user, $granularity->getCellByMembers($members)));
    }

    /**
     * @param $email
     * @param \Orga\Domain\Granularity $granularity
     * @param array $members
     */
    protected function addCellContributor($email, Granularity $granularity, array $members)
    {
        $user = User::loadByEmail($email);
        $this->acl->grant($user, new CellContributorRole($user, $granularity->getCellByMembers($members)));
    }

    /**
     * @param $email
     * @param \Orga\Domain\Granularity $granularity
     * @param array $members
     */
    protected function addCellObserver($email, Granularity $granularity, array $members)
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
