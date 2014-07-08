<?php

use Account\Application\Service\OrganizationViewFactory;
use AF\Application\AFViewConfiguration;
use AF\Architecture\Service\InputSerializer;
use AF\Domain\AF;
use AF\Domain\InputService\InputSetValuesValidator;
use AF\Domain\InputSet\PrimaryInputSet;
use Core\Work\ServiceCall\ServiceCallTask;
use DW\Application\Service\Export\PdfSpecific;
use DW\Application\DWViewConfiguration;
use DW\Application\Service\ReportService;
use DW\Domain\Report;
use MyCLabs\MUIH\GenericTag;
use MyCLabs\MUIH\GenericVoidTag;
use MyCLabs\MUIH\Icon;
use MyCLabs\ACL\ACL;
use MyCLabs\MUIH\Tab;
use MyCLabs\Work\Dispatcher\SynchronousWorkDispatcher;
use User\Domain\ACL\Actions;
use Orga\ViewModel\CellViewModelFactory;
use AuditTrail\Domain\Context\OrganizationContext;
use AuditTrail\Domain\EntryRepository;
use Doctrine\Common\Collections\Criteria;
use User\Domain\User;
use Orga\Model\ACL\AbstractCellRole;
use Orga\Model\ACL\CellAdminRole;
use Orga\Model\ACL\CellManagerRole;
use Orga\Model\ACL\CellContributorRole;
use Orga\Model\ACL\CellObserverRole;

/**
 * @author valentin.claras
 */
class Orga_CellController extends Core_Controller
{
    use UI_Controller_Helper_Form;

    /**
     * @Inject
     * @var ACL
     */
    private $acl;

    /**
     * @Inject
     * @var Orga_Service_ACLManager
     */
    private $orgaACLManager;

    /**
     * @Inject
     * @var SynchronousWorkDispatcher
     */
    private $workDispatcher;

    /**
     * @Inject
     * @var OrganizationViewFactory
     */
    private $organizationVMFactory;

    /**
     * @Inject
     * @var CellViewModelFactory
     */
    private $cellVMFactory;

    /**
     * @Inject("work.waitDelay")
     * @var int
     */
    private $waitDelay;

    /**
     * @Inject
     * @var Orga_Service_InputService
     */
    private $inputService;

    /**
     * @Inject
     * @var InputSerializer
     */
    private $inputSerializer;

    /**
     * @Inject
     * @var ReportService
     */
    private $reportService;

    /**
     * @Inject
     * @var EntryRepository
     */
    private $auditTrailRepository;

    /**
     * Redirection sur la liste.
     * @Secure("loggedIn")
     */
    public function indexAction()
    {
        $this->redirect('orga/organization/');
    }

    /**
     * @Secure("viewCell")
     */
    public function viewAction()
    {
        /** @var User $connectedUser */
        $connectedUser = $this->_helper->auth();

        $idCell = $this->getParam('idCell');
        /** @var Orga_Model_Cell $cell */
        $cell = Orga_Model_Cell::load($idCell);
        $granularity = $cell->getGranularity();
        $organization = $granularity->getOrganization();

        $this->view->assign('cellVWFactory', $this->cellVMFactory);
        $this->view->assign('organization', $this->organizationVMFactory->createOrganizationView($organization, $connectedUser));
        $this->view->assign('currentCell', $this->cellVMFactory->createCellViewModel($cell, $connectedUser, true));
        $currentCellPurpose = '';
        if ($this->view->currentCell->showUsers) {
            if ($currentCellPurpose !== '') {
                $currentCellPurpose .= __('Orga', 'view', 'separator');
            }
            $currentCellPurpose .= __('User', 'user', 'users');
        }
        if ($this->view->currentCell->canEditInventory) {
            if ($currentCellPurpose !== '') {
                $currentCellPurpose .= __('Orga', 'view', 'separator');
            }
            $currentCellPurpose .= __('Orga', 'inventory', 'editInventories');
        } else if ($this->view->currentCell->showInventory) {
            if ($currentCellPurpose !== '') {
                $currentCellPurpose .= __('Orga', 'view', 'separator');
            }
            $currentCellPurpose .= __('Orga', 'inventory', 'showInventories');
        }
        if ($this->view->currentCell->showInput) {
            if ($currentCellPurpose !== '') {
                $currentCellPurpose .= __('Orga', 'view', 'separator');
            }
            $currentCellPurpose .= __('UI', 'name', 'inputs');
        }
        if ($this->view->currentCell->showReports) {
            if ($currentCellPurpose !== '') {
                $currentCellPurpose .= __('Orga', 'view', 'separator');
            }
            $currentCellPurpose .= __('DW', 'name', 'analyses');
        }
        $this->view->assign('currentCellPurpose', $currentCellPurpose);
        $currentAxes = $granularity->getAxes();
        $this->view->assign(
            'refAxes',
            array_map(function ($axis) { return $axis->getRef(); }, $currentAxes)
        );
        $this->setActiveMenuItemOrganization($organization->getId());

        $isUserAllowedToEditOrganization = $this->acl->isAllowed(
            $connectedUser,
            Actions::EDIT,
            $organization
        );
        $isUserAllowToEditAllMembers = $isUserAllowedToEditOrganization || $this->acl->isAllowed(
            $connectedUser,
            Actions::EDIT,
            $organization->getGranularityByRef('global')->getCellByMembers([])
        );
        if (!$isUserAllowToEditAllMembers) {
            $topCellsWithEditAccess = $this->orgaACLManager->getTopCellsWithAccessForOrganization(
                $connectedUser,
                $organization,
                [CellAdminRole::class]
            )['cells'];
        }

        // Cellules enfants.
        $narrowerGranularities = [];
        try {
            $granularityForInventoryStatus = $cell->getGranularity()->getOrganization()
                ->getGranularityForInventoryStatus();
        } catch (Core_Exception_UndefinedAttribute $e) {
            $granularityForInventoryStatus = null;
        }
        $this->view->assign('granularityForInventoryStatus', $granularityForInventoryStatus);
        foreach ($cell->getGranularity()->getNarrowerGranularities() as $narrowerGranularity) {
            $purpose = '';
            // ACL purpose.
            $isNarrowerGranularityACL = ($narrowerGranularity->getCellsWithACL())
                && ($this->acl->isAllowed($connectedUser, Actions::ALLOW, $cell));
            if ($isNarrowerGranularityACL) {
                if ($purpose !== '') {
                    $purpose .= __('Orga', 'view', 'separator');
                }
                $purpose .= __('User', 'user', 'users');
            }
            // Inventory purpose.
            $isNarrowerGranularityInventory = ($narrowerGranularity->getCellsMonitorInventory()
                    || ($narrowerGranularity === $granularityForInventoryStatus))
                && ($this->acl->isAllowed($connectedUser, Actions::ANALYZE, $cell));
            if ($isNarrowerGranularityInventory) {
                if ($purpose !== '') {
                    $purpose .= __('Orga', 'view', 'separator');
                }
                if ($narrowerGranularity === $granularityForInventoryStatus) {
                    $purpose .= __('Orga', 'inventory', 'editInventories');
                } else {
                    $purpose .= __('Orga', 'inventory', 'viewInventories');
                }
            }
            // Input purpose.
            $isNarrowerGranularityInput = ($narrowerGranularity->getInputConfigGranularity() !== null);
            if ($isNarrowerGranularityInput) {
                if ($purpose !== '') {
                    $purpose .= __('Orga', 'view', 'separator');
                }
                $purpose .= __('UI', 'name', 'inputs');
            }
            // Reports purpose.
            $isNarrowerGranularityAnalyses = ($narrowerGranularity->getCellsGenerateDWCubes())
                && ($this->acl->isAllowed($connectedUser, Actions::ANALYZE, $cell));
            if ($isNarrowerGranularityAnalyses) {
                if ($purpose !== '') {
                    $purpose .= __('Orga', 'view', 'separator');
                }
                $purpose .= __('DW', 'name', 'analyses');
            }
            // Filter Axes.
            $filterAxes = [];
            $granularityAxes = $granularity->getAxes();
            $narrowerGranularityAxes = $narrowerGranularity->getAxes();
            foreach ($narrowerGranularity->getAxes() as $narrowerAxis) {
                $narrowerGranularityAxes = array_merge(
                    $narrowerGranularityAxes,
                    $narrowerAxis->getAllBroadersFirstOrdered()
                );
            }
            usort($narrowerGranularityAxes, [Orga_Model_Axis::class, 'lastOrderAxes']);
            foreach ($narrowerGranularityAxes as $narrowerAxis) {
                if ($narrowerAxis->isTransverse($granularityAxes)) {
                    $filterAxes[$narrowerAxis->getRef()] = $this->getFilterAxisOptions($narrowerAxis, $cell);
                } else {
                    foreach ($granularity->getAxes() as $granularityAxis) {
                        if (($narrowerAxis->isBroaderThan($granularityAxis)) || ($narrowerAxis === $granularityAxis)) {
                            continue 2;
                        }
                    }
                    $filterAxes[$narrowerAxis->getRef()] = $this->getFilterAxisOptions($narrowerAxis, $cell);
                }
            }
            if ($purpose !== '') {
                $relevantQuery = new Core_Model_Query();
                $relevantQuery->filter->addCondition(Orga_Model_Cell::QUERY_RELEVANT, true);
                $relevantQuery->filter->addCondition(Orga_Model_Cell::QUERY_ALLPARENTSRELEVANT, true);
                $totalChildCells = $cell->countTotalChildCellsForGranularity($narrowerGranularity, $relevantQuery);

                $showInventoryFilter = $isNarrowerGranularityInput
                    && ($granularityForInventoryStatus !== null)
                    && ($narrowerGranularity->isNarrowerThan($granularityForInventoryStatus));

                $narrowerGranularities[] = [
                    'granularity' => $narrowerGranularity,
                    'purpose' => $purpose,
                    'filterAxes' => $filterAxes,
                    'isAcl' => $isNarrowerGranularityACL,
                    'isInventory' => $isNarrowerGranularityInventory,
                    'showInventory' => $showInventoryFilter,
                    'isGranularityForInventoryStatus' => ($narrowerGranularity === $granularityForInventoryStatus),
                    'isInput' => $isNarrowerGranularityInput,
                    'isAnalyses' => $isNarrowerGranularityAnalyses,
                    'totalCells' => $totalChildCells
                ];
            }
        }
        $this->view->assign('narrowerGranularities', $narrowerGranularities);

        // Formulaire d'ajout des membres enfants.
        $axesCanEdit = $this->orgaACLManager->getAxesCanEdit($connectedUser, $organization);
        $this->view->assign('canAddMembers', (count($axesCanEdit) > 0));
        if (count($axesCanEdit) > 0) {
            $addMemberForm = new GenericTag('form');
            $addMemberForm->setAttribute('action', 'orga/cell/add-member/idCell/'.$idCell);
            $addMemberForm->setAttribute('method', 'POST');
            $addMemberForm->setAttribute('id', 'addMember');
            $addMemberForm->addClass('form-horizontal');

            $axisChoiceLabel = new GenericTag('label', __('UI', 'name', 'axis'));
            $axisChoiceLabel->setAttribute('for', 'addMember_axis');
            $axisChoiceLabel->addClass('control-label');
            $axisChoiceLabel->addClass('col-xs-2');
            $axisChoiceLabel->addClass('withTooltip');
            $axisChoiceLabel->setAttribute('title', ___('Orga', 'view', 'addMembersAxisExplanations'));
            $axisChoiceLabel->setAttribute('data-html', 'true');
            $axisChoiceHelp = new Icon('question-circle');
            $axisChoiceLabel->appendContent(' ');
            $axisChoiceLabel->appendContent($axisChoiceHelp);
            $axisChoiceInput = new GenericTag('select');
            $axisChoiceInput->setAttribute('name', 'axis');
            $axisChoiceInput->setAttribute('id', 'addMember_axis');
            $axisChoiceInput->addClass('form-control');
            $axisChoiceWrapper = new GenericTag('div', $axisChoiceInput);
            $axisChoiceWrapper->addClass('col-xs-10');
            $axisChoiceGroup = new GenericTag('div');
            $axisChoiceGroup->addClass('form-group');
            $axisChoiceGroup->appendContent($axisChoiceLabel);
            $axisChoiceGroup->appendContent($axisChoiceWrapper);
            $addMemberForm->appendContent($axisChoiceGroup);

            $axisChoiceNullOption = new GenericTag('option');
            $axisChoiceNullOption->setAttribute('value', '');
            $axisChoiceInput->appendContent($axisChoiceNullOption);
            foreach ($axesCanEdit as $axis) {
                $axisOption = new GenericTag('option', $this->translator->get($axis->getLabel()));
                $axisOption->setAttribute('value', $axis->getRef());
                $axisChoiceInput->appendContent($axisOption);

                foreach ($axis->getDirectBroaders() as $broaderAxis) {
                    $parentMemberGroup = new GenericTag('div');
                    $parentMemberGroup->addClass('form-group');
                    $parentMemberGroup->addClass('hide');
                    $parentMemberGroup->addClass('broader-axis');
                    $parentMemberGroup->addClass($axis->getRef());
                    $addMemberForm->appendContent($parentMemberGroup);

                    $parentMemberChoiceLabel = new GenericTag('label', $this->translator->get($broaderAxis->getLabel()));
                    $parentMemberChoiceLabel->setAttribute('for', 'addMember_axis_'.$broaderAxis->getId());
                    $parentMemberChoiceLabel->addClass('control-label');
                    $parentMemberChoiceLabel->addClass('col-xs-2');
                    $parentMemberChoiceInput = new GenericTag('select');
                    $parentMemberChoiceInput->setAttribute('name', $axis->getRef().'_parentMember_'.$broaderAxis->getRef());
                    $parentMemberChoiceInput->setAttribute('id', 'addMember_axis_'.$broaderAxis->getId());
                    $parentMemberChoiceInput->addClass('form-control');
                    $parentMemberChoiceWrapper = new GenericTag('div', $parentMemberChoiceInput);
                    $parentMemberChoiceWrapper->addClass('col-xs-10');
                    $parentMemberGroup->appendContent($parentMemberChoiceLabel);
                    $parentMemberGroup->appendContent($parentMemberChoiceWrapper);

                    if (!$isUserAllowToEditAllMembers) {
                        $members = [];
                        foreach ($topCellsWithEditAccess as $cell) {
                            if (!$broaderAxis->isTransverse($cell->getGranularity()->getAxes())) {
                                foreach ($cell->getMembers() as $cellMember) {
                                    if ($broaderAxis->isBroaderThan($cellMember->getAxis())) {
                                        continue 2;
                                    }
                                }
                                $members = array_merge(
                                    $members,
                                    $cell->getChildMembersForAxes([$broaderAxis])[$broaderAxis->getRef()]
                                );
                            }
                        }
                        $members = array_unique($members);
                        usort($members, [Orga_Model_Member::class, 'orderMembers']);
                    } else {
                        $members = $broaderAxis->getMembers();
                    }
                    foreach ($members as $parentMember) {
                        $parentMemberOption = new GenericTag('option', $this->translator->get($parentMember->getLabel()));
                        $parentMemberOption->setAttribute('value', $parentMember->getId());
                        $parentMemberChoiceInput->appendContent($parentMemberOption);
                    }
                }
            }

            $memberLabelLabel = new GenericTag('label', __('UI', 'name', 'element'));
            $memberLabelLabel->addClass('control-label');
            $memberLabelLabel->addClass('col-xs-2');
            $memberLabelInput = new GenericVoidTag('input');
            $memberLabelInput->setAttribute('name', 'label');
            $memberLabelInput->setAttribute('type', 'text');
            $memberLabelInput->setAttribute('placeholder', __('UI', 'name', 'label'));
            $memberLabelInput->addClass('form-control');
            $memberLabelWrapper = new GenericTag('div', $memberLabelInput);
            $memberLabelWrapper->addClass('col-xs-10');
            $memberLabelGroup = new GenericTag('div');
            $memberLabelGroup->addClass('form-group');
            $memberLabelGroup->addClass('hide');
            $memberLabelGroup->appendContent($memberLabelLabel);
            $memberLabelGroup->appendContent($memberLabelWrapper);
            $addMemberForm->appendContent($memberLabelGroup);

            $addMemberSubmitButton = new GenericVoidTag('input');
            $addMemberSubmitButton->setAttribute('type', 'submit');
            $addMemberSubmitButton->addClass('btn');
            $addMemberSubmitButton->addClass('btn-primary');
            $addMemberSubmitButton->addClass('pull-right');
            $addMemberForm->appendContent($addMemberSubmitButton);

            $this->view->assign('addMemberForm', $addMemberForm);
        }
    }

    /**
     * @param Orga_Model_Axis $axis
     * @param Orga_Model_Cell $cell
     *
     * @return array
     */
    protected function getFilterAxisOptions(Orga_Model_Axis $axis, Orga_Model_Cell $cell)
    {
        $memberOptions = [];
        $memberOptions[''] = __('Orga', 'view', 'allMembers', [
            'AXIS' => $this->translator->get($axis->getLabel())
        ]);

        $filter = $cell->getChildMembersForAxes([$axis]);
        /** @var Orga_Model_Member[] $members */
        if ((count($filter) > 0) && isset($filter[$axis->getRef()])) {
            $members = $filter[$axis->getRef()];
        } else {
            $members = $axis->getOrderedMembers()->toArray();
        }
        foreach ($members as $member) {
            $memberOptions[$member->getTag()] = $this->translator->get($member->getLabel());
        }

        return $memberOptions;
    }

    /**
     * @Secure("viewCell")
     */
    public function viewChildrenAction()
    {
        session_write_close();

        /** @var User $connectedUser */
        $connectedUser = $this->_helper->auth();

        $idCell = $this->getParam('idCell');
        /** @var Orga_Model_Cell $cell */
        $cell = Orga_Model_Cell::load($idCell);
        $idNarrowerGranularity = $this->getParam('idGranularity');
        /** @var Orga_Model_Granularity $narrowerGranularity */
        $narrowerGranularity = Orga_Model_Granularity::load($idNarrowerGranularity);

        $showAdministrators = false;

        // ACL.
        $showUsers = $narrowerGranularity->getCellsWithACL()
            && $this->acl->isAllowed($connectedUser, Actions::ALLOW, $cell);

        // Reports.
        $showReports = $narrowerGranularity->getCellsGenerateDWCubes()
            && $this->acl->isAllowed($connectedUser, Actions::ANALYZE, $cell);

        // Exports
        $showExports = $this->acl->isAllowed($connectedUser, Actions::ANALYZE, $cell);

        // Input.
        $showInput = ($narrowerGranularity->getInputConfigGranularity() !== null);

        // Inventory.
        try {
            $granularityForInventoryStatus = $cell->getGranularity()->getOrganization()
                ->getGranularityForInventoryStatus();
        } catch (Core_Exception_UndefinedAttribute $e) {
            $granularityForInventoryStatus = null;
        }
        $editInventory = (($narrowerGranularity === $granularityForInventoryStatus)
            && $this->acl->isAllowed($connectedUser, Actions::ANALYZE, $cell)
            && $this->acl->isAllowed($connectedUser, Actions::INPUT, $cell));
        $showInventory = (($narrowerGranularity === $granularityForInventoryStatus)
                || $narrowerGranularity->getCellsMonitorInventory() || $editInventory)
            && ($this->acl->isAllowed($connectedUser, Actions::ANALYZE, $cell));;
        $showInventoryProgress = false;
        if ($showInventory) {
            foreach ($narrowerGranularity->getNarrowerGranularities() as $narrowerInputGranularity) {
                if ($narrowerInputGranularity->isInput()) {
                    $showInventoryProgress = true;
                    break;
                }
            }
        }

        // Uniquement les sous-cellules pertinentes.
        $childCellsCriteria = new Criteria();
        $childCellsCriteria->where($childCellsCriteria->expr()->eq(Orga_Model_Cell::QUERY_RELEVANT, true));
        $childCellsCriteria->andWhere($childCellsCriteria->expr()->eq(Orga_Model_Cell::QUERY_ALLPARENTSRELEVANT, true));
        foreach (explode(Orga_Model_Organization::PATH_JOIN, $cell->getTag()) as $pathTag) {
            $childCellsCriteria->andWhere($childCellsCriteria->expr()->contains('tag', $pathTag));
        }
        $childCellsCriteria->setFirstResult($this->getParam('firstCell'));
        $childCellsCriteria->setMaxResults($this->getParam('showCells'));
        $childCellsCriteria->orderBy(['tag' => Criteria::ASC]);
        if ($this->hasParam('filters')) {
            $filters = $this->getParam('filters');
            $filterPrefix = 'granularity' . $idNarrowerGranularity . '_';
            foreach ($filters as $filter) {
                if (!empty($filter['value'])) {
                    if ($filter['name'] === ($filterPrefix . 'inventoryStatus')) {
                        $childCellsCriteria->andWhere($childCellsCriteria->expr()->contains('inventoryStatus', $filter['value']));
                    } else if ($filter['name'] === ($filterPrefix . 'inputStatus')) {
                        $childCellsCriteria->andWhere($childCellsCriteria->expr()->contains('inputStatus', $filter['value']));
                    } else if ($filter['name'] === ($filterPrefix . 'inputInconsistencies')) {
                        if ($filter['value'] === 'without') {
                            $childCellsCriteria->andWhere($childCellsCriteria->expr()->lte('numberOfInconsistenciesInInputSet', 0));
                        } else {
                            $childCellsCriteria->andWhere($childCellsCriteria->expr()->gt('numberOfInconsistenciesInInputSet', 0));
                        }
                    } else {
                        $childCellsCriteria->andWhere($childCellsCriteria->expr()->contains('tag', $filter['value']));
                    }
                }
            }
        }

        $childCells = [];
        /** @var Orga_Model_Cell $childCell */
        foreach ($narrowerGranularity->getCells()->matching($childCellsCriteria) as $childCell) {
            $childCells[] = $this->cellVMFactory->createCellViewModel(
                $childCell,
                $connectedUser,
                $showAdministrators,
                $showUsers,
                $showReports,
                $showExports,
                $showInventory,
                $showInventoryProgress,
                $editInventory,
                $showInput
            );
        }

        $childCellsQuery = new Core_Model_Query();
        $childCellsQuery->filter->addCondition(Orga_Model_Cell::QUERY_RELEVANT, true);
        $childCellsQuery->filter->addCondition(Orga_Model_Cell::QUERY_ALLPARENTSRELEVANT, true);
        if ($this->hasParam('filters')) {
            foreach ($filters as $filter) {
                if (!empty($filter['value'])) {
                    if ($filter['name'] === ($filterPrefix . 'inventoryStatus')) {
                        $childCellsQuery->filter->addCondition('inventoryStatus', $filter['value']);
                    } else if ($filter['name'] === ($filterPrefix . 'inputStatus')) {
                        $childCellsQuery->filter->addCondition('inputStatus', $filter['value']);
                    } else if ($filter['name'] === ($filterPrefix . 'inputInconsistencies')) {
                        if ($filter['value'] === 'without') {
                            $childCellsQuery->filter->addCondition('numberOfInconsistenciesInInputSet', 0, Core_Model_Filter::OPERATOR_EQUAL);
                        } else {
                            $childCellsQuery->filter->addCondition('numberOfInconsistenciesInInputSet', 0, Core_Model_Filter::OPERATOR_HIGHER);
                        }
                    } else {
                        $childCellsQuery->filter->addCondition('tag', $filter['value'], Core_Model_Filter::OPERATOR_CONTAINS);
                    }
                }
            }
        }
        $totalChildCells = $cell->countTotalChildCellsForGranularity($narrowerGranularity, $childCellsQuery);

        $this->sendJsonResponse(['childCells' => $childCells, 'totalCells' => $totalChildCells]);
    }

    /**
     * @Secure("viewCell")
     */
    public function viewHistoryAction()
    {
        session_write_close();

        $idCell = $this->getParam('idCell');
        /** @var Orga_Model_Cell $cell */
        $cell = Orga_Model_Cell::load($idCell);

        $locale = Core_Locale::loadDefault();

        $events = [];

        $context = new OrganizationContext($cell->getGranularity()->getOrganization());
        $context->setCell($cell);
        foreach ($this->auditTrailRepository->findLatestForOrganizationContext($context, 10) as $entry) {
            $contextCell = $entry->getContext()->getCell();
            $cellLink = '<a href="orga/cell/input/idCell/' . $contextCell->getId()
                . '/fromIdCell/' . $cell->getId() . '/">'
                . $this->translator->get($contextCell->getLabel())
                . '</a>';
            $eventText = __('Orga', 'auditTrail', $entry->getEventName(), [
                'INPUT' => $cellLink,
                'USER' => '<b>'.$entry->getUser()->getName().'</b>'
            ]);

            $date = $locale->formatDate($entry->getDate());
            $time = $locale->formatTime($entry->getDate());
            if (!isset($events[$date])) {
                $events[$date] = ['date' => $date, 'events' => []];
            }
            $events[$date]['events'][] = ['time' => $time, 'event' => $eventText];
        }

        $this->sendJsonResponse(array_values($events));
    }

    /**
     * @Secure("viewCell")
     */
    public function viewCommentsAction()
    {
        session_write_close();

        $idCell = $this->getParam('idCell');
        /** @var Orga_Model_Cell $cell */
        $cell = Orga_Model_Cell::load($idCell);

        $locale = Core_Locale::loadDefault();

        $comments = [];

        /** @var Orga_Model_Repository_Cell $cellRepository */
        $cellRepository = $this->entityManager->getRepository(Orga_Model_Cell::class);
        /** @var Orga_Model_Cell_InputComment $comment */
        foreach ($cellRepository->getLatestComments($cell, 10) as $comment) {
            $contextCell = $comment->getCell();
            $cellLink = ' <a href="orga/cell/input/idCell/' . $contextCell->getId()
                . '/fromIdCell/' . $cell->getId() . '/tab/comments/">'
                . $this->translator->get($contextCell->getLabel())
                . '</a>';
            $commentText = __('Orga', 'comment', 'by') . ' <b>' . $comment->getAuthor()->getName() . '</b> '
                . __('Orga', 'input', 'aboutInput')
                . $cellLink . __('UI', 'other', ':')
                . '« '
                . Core_Tools::truncateString(Core_Tools::removeTextileMarkUp($comment->getText()), 150)
                . ' ».';

            $date = $locale->formatDate($comment->getCreationDate());
            $time = $locale->formatTime($comment->getCreationDate());
            if (!isset($comments[$date])) {
                $comments[$date] = ['date' => $date, 'comments' => []];
            }
            $comments[$date]['comments'][] = ['time' => $time, 'comment' => $commentText];
        }

        $this->sendJsonResponse(array_values($comments));
    }

    /**
     * @Secure("viewCell")
     */
    public function viewActivityAction()
    {
        session_write_close();

        $idCell = $this->getParam('idCell');
        $this->view->assign('idCell', $idCell);

        $from = new DateTime();
        $this->view->assign('fromDate', $from->getTimestamp());
    }

    /**
     * @Secure("viewCell")
     */
    public function viewMoreActivityAction()
    {
        session_write_close();

        $idCell = $this->getParam('idCell');
        /** @var Orga_Model_Cell $cell */
        $cell = Orga_Model_Cell::load($idCell);

        $from = new DateTime('@'.$this->getParam('from'));
        $upTo = clone $from;
        $upTo = $upTo->sub(new DateInterval('P1M'));

        $activity = [];
        $hasHistory = true;
        $hasComments = true;
        while ((count($activity) == 0) && ($hasHistory || $hasComments)) {
            $activity = $this->findActivity($cell, $from, $upTo);

            $from = $from->sub(new DateInterval('P1M'));
            $upTo = $upTo->sub(new DateInterval('P1M'));

            $context = new OrganizationContext($cell->getGranularity()->getOrganization());
            $context->setCell($cell);
            $hasHistory = $this->auditTrailRepository->hasFromForOrganizationContext($context, $from);

            /** @var Orga_Model_Repository_Cell $cellRepository */
            $cellRepository = $this->entityManager->getRepository(Orga_Model_Cell::class);
            $hasComments = $cellRepository->hasFromComments($cell, $from);
        }

        if (count($activity) == 0) {
            $upTo = new DateTime('@'.$this->getParam('from'));
        }
        $this->sendJsonResponse([
            'activity' => array_values($activity),
            'dateFrom' => $upTo->getTimestamp()
        ]);
    }

    protected function findActivity(Orga_Model_Cell $cell, DateTime $from, DateTime $upTo)
    {
        $activity = [];

        $locale = Core_Locale::loadDefault();

        $context = new OrganizationContext($cell->getGranularity()->getOrganization());
        $context->setCell($cell);
        foreach ($this->auditTrailRepository->findUpToForOrganizationContext($context, $upTo, $from) as $entry) {
            $contextCell = $entry->getContext()->getCell();
            $cellLink = '<a href="orga/cell/input/idCell/' . $contextCell->getId()
                . '/fromIdCell/' . $cell->getId() . '/">'
                . $this->translator->get($contextCell->getLabel())
                . '</a>';
            $eventText = __('Orga', 'auditTrail', $entry->getEventName(), [
                    'INPUT' => '',
                    'USER' => '<b>'.$entry->getUser()->getName().'</b>'
                ]);

            $dateTime = $locale->formatShortDateTime($entry->getDate());
            $activity[] = [
                'type' => 'history',
                'dateTime' => $dateTime,
                'author' => $entry->getUser()->getName(),
                'cell' => $cellLink,
                'content' => $eventText
            ];
        }

        /** @var Orga_Model_Repository_Cell $cellRepository */
        $cellRepository = $this->entityManager->getRepository(Orga_Model_Cell::class);
        /** @var Orga_Model_Cell_InputComment $comment */
        foreach ($cellRepository->getUpToComments($cell, $upTo, $from) as $comment) {
            $contextCell = $comment->getCell();
            $cellLink = ' <a href="orga/cell/input/idCell/' . $contextCell->getId()
                . '/fromIdCell/' . $cell->getId() . '/tab/comments/">'
                . $this->translator->get($contextCell->getLabel())
                . '</a>';
            $commentText = '<blockquote>'
                . '« '
                . Core_Tools::removeTextileMarkUp($comment->getText())
                . ' ».'
                . '<footer>'
                . __('Orga', 'comment', 'by') .
                ' <b>' . $comment->getAuthor()->getName() . '</b> '
                . __('Orga', 'input', 'aboutInput')
                . '</footer>'
                .'</blockquote>';

            $dateTime = $locale->formatShortDateTime($comment->getCreationDate());
            $activity[] = [
                'type' => 'comment',
                'dateTime' => $dateTime,
                'author' => $comment->getAuthor()->getName(),
                'cell' => $cellLink,
                'content' => $commentText
            ];
        }

        uasort($activity, function ($a, $b) {
            return strcmp($b['dateTime'], $a['dateTime']);
        });

        return $activity;
    }

    /**
     * @Secure("editCell")
     */
    public function addMemberAction()
    {
        $idCell = $this->getParam('idCell');
        /** @var Orga_Model_Cell $cell */
        $cell = Orga_Model_Cell::load($idCell);

        $formData = json_decode($this->getRequest()->getParam('addMember'), true);

        $axisRef = $this->getParam('axis');
        if (empty($axisRef)) {
            $this->addFormError('axis', __('UI', 'formValidation', 'emptyRequiredField'));
            $this->sendFormResponse();
            return;
        }
        $axis = $cell->getOrganization()->getAxisByRef($axisRef);

        $parentMembers = [];
        $contextualizingParentMembers = [];
        foreach ($axis->getDirectBroaders() as $broaderAxis) {
            $parentAxisFieldRef = $axis->getRef() . '_parentMember_' . $broaderAxis->getRef();
            $parentMember = Orga_Model_Member::load($this->getParam($parentAxisFieldRef));
            $parentMembers[] = $parentMember;
            if ($parentMember->getAxis()->isContextualizing()) {
                $contextualizingParentMembers[] = $parentMember;
            }
            $contextualizingParentMembers = array_merge(
                $contextualizingParentMembers,
                $parentMember->getContextualizingParents()
            );
        }
        $parentMembersHashkey = Orga_Model_Member::buildParentMembersHashKey($contextualizingParentMembers);

        $label = $this->getParam('label');
        if (empty($label)) {
            $this->addFormError('label', __('UI', 'formValidation', 'emptyRequiredField'));
            $this->sendFormResponse();
            return;
        }
        $ref = Core_Tools::refactor($label);

        $refIsUnique = false;
        $i = 1;
        while (!$refIsUnique) {
            try {
                $axis->getMemberByCompleteRef($ref . (($i > 1) ? '_'.$i : '') . '#' . $parentMembersHashkey);
                $i++;
            } catch (Core_Exception_NotFound $e) {
                $refIsUnique = true;
                if ($i > 1) {
                    $ref .= '_' . $i;
                    $label .= ' (' . $i . ')';
                }
            }
        }

        $success = function () {
            $this->sendJsonResponse(['message' => __('UI', 'message', 'added'), 'type' => 'success']);
        };
        $timeout = function () {
            $this->sendJsonResponse(['message' => __('UI', 'message', 'addedLater'), 'type' => 'success']);
        };
        $error = function (Exception $e) {
            throw $e;
        };

        // Lance la tache en arrière plan
        $task = new ServiceCallTask(
            'Orga_Service_OrganizationService',
            'addMember',
            [$axis, $ref, $label, $parentMembers],
            __('Orga', 'backgroundTasks', 'addMember', [
                'MEMBER' => $label,
                'AXIS' => $this->translator->get($axis->getLabel()),
            ])
        );
        $this->workDispatcher->runAndWait($task, $this->waitDelay, $success, $timeout, $error);
    }

    /**
     * @Secure("allowCell")
     */
    public function viewUsersAction()
    {
        $idCell = $this->getParam('idCell');
        /** @var Orga_Model_Cell $cell */
        $cell = Orga_Model_Cell::load($idCell);

        $this->view->assign('idCell', $idCell);
        $usersLinked = $cell->getAllRoles();
        usort(
            $usersLinked,
            function (AbstractCellRole $a, AbstractCellRole $b) {
                /** @var User $aUser */
                $aUser = $a->getSecurityIdentity();
                /** @var User $bUser */
                $bUser = $b->getSecurityIdentity();
                if (get_class($a) === get_class($b)) {
                    if ($aUser->getFirstName() === $bUser->getFirstName()) {
                        if ($aUser->getLastName() === $bUser->getLastName()) {
                            return strcmp($aUser->getEmail(), $bUser->getEmail());
                        }
                        return strcmp($aUser->getLastName(), $bUser->getLastName());
                    }
                    return strcmp($aUser->getFirstName(), $bUser->getFirstName());
                }
                if ($a instanceof CellAdminRole) {
                    return -1;
                }
                if ($b instanceof CellAdminRole) {
                    return 1;
                }
                if ($a instanceof CellManagerRole) {
                    return -1;
                }
                if ($b instanceof CellManagerRole) {
                    return 1;
                }
                if ($a instanceof CellContributorRole) {
                    return -1;
                }
                if ($b instanceof CellContributorRole) {
                    return 1;
                }
                if ($a instanceof CellObserverRole) {
                    return -1;
                }
                if ($b instanceof CellObserverRole) {
                    return 1;
                }
                return 0;
            }
        );
        $this->view->assign('cellUsers', $usersLinked);

        // Désactivation du layout.
        $this->_helper->layout()->disableLayout();
    }

    /**
     * @Secure("allowCell")
     */
    public function addUserAction()
    {
        $idCell = $this->getParam('idCell');
        /** @var Orga_Model_Cell $cell */
        $cell = Orga_Model_Cell::load($idCell);

        // Validation
        $userEmail = $this->getParam('userEmail');
        if (empty($userEmail)) {
            throw new Core_Exception_User('UI', 'formValidation', 'emptyRequiredField');
        } elseif (! filter_var($userEmail, FILTER_VALIDATE_EMAIL)) {
            throw new Core_Exception_User('UI', 'formValidation', 'invalidEmail');
        }
        $role = $this->getParam('userRole');
        switch ($role) {
            case 'CellAdminRole':
                $role = CellAdminRole::class;
                break;
            case 'CellManagerRole':
                $role = CellManagerRole::class;
                break;
            case 'CellContributorRole':
                $role = CellContributorRole::class;
                break;
            case 'CellObserverRole':
                $role = CellObserverRole::class;
                break;
            default:
                throw new Core_Exception_User('UI', 'formValidation', 'emptyRequiredField');
        }

        // Vérifie que l'utilisateur n'a pas déjà le role
        try {
            $user = User::loadByEmail($userEmail);
            foreach ($user->getRoles() as $userRole) {
                if ($userRole instanceof $role && $userRole->getCell() === $cell) {
                    throw new Core_Exception_User('Orga', 'role', 'userAlreadyHasRole');
                }
            }
        } catch (Core_Exception_NotFound $e) {
        }

        $success = function () {
            $this->sendJsonResponse(__('UI', 'message', 'added'));
        };
        $timeout = function () {
            $this->sendJsonResponse(__('UI', 'message', 'addedLater'));
        };
        $error = function (Exception $e) {
            throw $e;
        };

        $task = new ServiceCallTask(
            Orga_Service_ACLManager::class,
            'addCellRole',
            [$cell, $role, $userEmail, false],
            __('Orga', 'backgroundTasks', 'addRoleToUser', ['ROLE' => $role::getLabel(), 'USER' => $userEmail])
        );
        $this->workDispatcher->runAndWait($task, $this->waitDelay, $success, $timeout, $error);
    }

    /**
     * @Secure("allowCell")
     */
    public function removeUserAction()
    {
        /** @var AbstractCellRole $role */
        $role = $this->entityManager->find(AbstractCellRole::class, $this->getParam('idRole'));
        $user = $role->getSecurityIdentity();

        $success = function () {
            $this->sendJsonResponse(__('UI', 'message', 'deleted'));
        };
        $timeout = function () {
            $this->sendJsonResponse(__('UI', 'message', 'deletedLater'));
        };
        $error = function (Exception $e) {
            throw $e;
        };

        $task = new ServiceCallTask(
            Orga_Service_ACLManager::class,
            'removeCellRole',
            [$user, $role, false],
            __(
                'Orga',
                'backgroundTasks',
                'removeRoleFromUser',
                ['ROLE' => $role->getLabel(), 'USER' => $user->getEmail()]
            )
        );
        $this->workDispatcher->runAndWait($task, $this->waitDelay, $success, $timeout, $error);
    }

    /**
     * @Secure("analyseCell")
     */
    public function viewReportsAction()
    {
        /** @var User $connectedUser */
        $connectedUser = $this->_helper->auth();

        $idCell = $this->getParam('idCell');
        /** @var Orga_Model_Cell $cell */
        $cell = Orga_Model_Cell::load($idCell);
        $fromIdCell = $this->hasParam('fromIdCell') ? $this->getParam('fromIdCell') : $idCell;

        $this->view->assign('idCell', $idCell);
        $cellReports = [];
        // Specific Reports.
        $specificReportsDirectoryPath = PACKAGE_PATH.'/data/specificReports/'.
            $cell->getGranularity()->getOrganization()->getId().'/'.
            str_replace('|', '_', $cell->getGranularity()->getRef()).'/';
        if (is_dir($specificReportsDirectoryPath)) {
            $specificReportsDirectory = dir($specificReportsDirectoryPath);
            while (false !== ($entry = $specificReportsDirectory->read())) {
                if ((is_file($specificReportsDirectoryPath.$entry)) && (preg_match('#\.xml$#', $entry))) {
                    $fileName = substr($entry, null, -4);
                    if (PdfSpecific::isValid($specificReportsDirectoryPath.$entry)) {
                        $cellReports[] = [
                            'label' => $fileName,
                            'link' => 'orga/cell/view-report-specific/idCell/'.$idCell.'/fromIdCell/'.$fromIdCell.'/report/'.$fileName,
                            'type' => 'specificReport',
                        ];
                    }
                }
            }
        }
        usort($cellReports, function ($a, $b) {
            return strcmp($a['label'], $b['label']);
        });

        // Copied Reports.
        /** @var Orga_Model_CellReport[] $usersReports */
        $usersReports = [];
        $dWReports = $cell->getDWCube()->getReports();
        usort($dWReports, function (Report $a, Report $b) {
            return strcmp(
                $this->translator->get($a->getLabel()),
                $this->translator->get($b->getLabel())
            );
        });
        foreach ($dWReports as $dWReport) {
            try {
                $usersReports[] = Orga_Model_CellReport::loadByCellDWReport($dWReport);
                continue;
            } catch (Core_Exception_NotFound $e) {
                // Rapport Copié.
            }
            $cellReports[] = [
                'label' => $this->translator->get($dWReport->getLabel()),
                'link' => 'orga/cell/view-report/idCell/'.$idCell.'/fromIdCell/'.$fromIdCell.'/idReport/'.$dWReport->getId(),
                'type' => 'copiedReport',
            ];
        }
        // User Reports.
        $otherUsers = [];
        foreach ($usersReports as $cellReport) {
            /** @var Report $dWReport */
            $cellReports[] = [
                'label' => $this->translator->get($cellReport->getCellDWReport()->getLabel()),
                'link' => 'orga/cell/view-report/idCell/'.$idCell.'/fromIdCell/'.$fromIdCell.'/idReport/'.$cellReport->getCellDWReport()->getId(),
                'type' => 'userReport',
                'owner' => $cellReport->getOwner(),
                'delete' => ($cellReport->getOwner() === $connectedUser),
                'idReport' => $cellReport->getCellDWReport()->getId()
            ];
            if ($cellReport->getOwner() !== $connectedUser) {
                $otherUsers[$cellReport->getOwner()->getId()] = $cellReport->getOwner()->getName();
            }
        }
        $this->view->assign('cellReports', $cellReports);
        $this->view->assign('idConnectedUser', $connectedUser->getId());
        $this->view->assign('otherUsers', $otherUsers);

        // Désactivation du layout.
        $this->_helper->layout()->disableLayout();
    }

    /**
     * @Secure("deleteReport")
     */
    public function removeReportAction()
    {
        Report::load($this->getParam('idReport'))->delete();
        $this->sendJsonResponse(__('UI', 'message', 'deleted'));
    }

    /**
     * @Secure("analyseCell")
     */
    public function viewReportAction()
    {
        /** @var User $connectedUser */
        $connectedUser = $this->_helper->auth();

        $idCell = $this->getParam('idCell');
        /** @var Orga_Model_Cell $cell */
        $cell = Orga_Model_Cell::load($idCell);
        $fromIdCell = $this->hasParam('fromIdCell') ? $this->getParam('fromIdCell') : $idCell;

        $reportCanBeUpdated = false;
        if ($this->hasParam('idReport')) {
            $report = Report::load($this->getParam('idReport'));
            try {
                $cellReport = Orga_Model_CellReport::loadByCellDWReport($report);
                $reportCanBeUpdated = ($cellReport->getOwner() === $connectedUser);
            } catch (Core_Exception_NotFound $e) {
                // Rapport copié.
            }
        }

        $viewConfiguration = new DWViewConfiguration();
        $viewConfiguration->setComplementaryPageTitle(
            ' <small>'.$this->translator->get($cell->getExtendedLabel()).'</small>'
        );
        $viewConfiguration->setOutputUrl('orga/cell/view/idCell/'.$fromIdCell.'/');
        $viewConfiguration->setSaveURL('orga/cell/view-report/idCell/'.$idCell.'/fromIdCell/'.$fromIdCell);
        $viewConfiguration->setCanBeUpdated($reportCanBeUpdated);
        $viewConfiguration->setCanBeSavedAs(true);

        if ($this->hasParam('idReport')) {
            $this->forward('details', 'report', 'dw', [
                'viewConfiguration' => $viewConfiguration
            ]);
        } else {
            $this->forward('details', 'report', 'dw', [
                'idCube' => $cell->getDWCube()->getId(),
                'viewConfiguration' => $viewConfiguration
            ]);
        }
    }

    /**
     * @Secure("analyseCell")
     */
    public function viewReportSpecificAction()
    {
        $idCell = $this->getParam('idCell');
        /** @var Orga_Model_Cell $cell */
        $cell = Orga_Model_Cell::load($idCell);
        $fromIdCell = $this->hasParam('fromIdCell') ? $this->getParam('fromIdCell') : $idCell;

        if (!($this->hasParam('display') && ($this->getParam('display') == true))) {
            $exportUrl = 'orga/cell/view-report-specific/'.
                'idCell/'.$idCell.'/fromIdCell/'.$fromIdCell.'/report/'.$this->getParam('report').'/display/true';
        } else {
            $exportUrl = null;
        }

        $specificReportsDirectoryPath = PACKAGE_PATH.'/data/specificReports/'.
            $cell->getGranularity()->getOrganization()->getId().'/'.
            str_replace('|', '_', $cell->getGranularity()->getRef()).'/';
        $specificReports = new PdfSpecific(
            $specificReportsDirectoryPath.$this->getParam('report').'.xml',
            $cell->getDWCube(),
            $this->reportService,
            $this->translator,
            $exportUrl
        );

        if ($exportUrl !== null) {
            $this->view->assign('html', $specificReports->html);
        } else {
            Zend_Layout::getMvcInstance()->disableLayout();
            $specificReports->display();
        }
    }

    /**
     * @Secure("analyseCell")
     */
    public function viewExportsAction()
    {
        /** @var User $connectedUser */
        $connectedUser = $this->_helper->auth();

        $idCell = $this->getParam('idCell');
        /** @var Orga_Model_Cell $cell */
        $cell = Orga_Model_Cell::load($idCell);

        $this->view->assign('idCell', $idCell);

        // Formats d'exports.
        $this->view->defaultFormat = 'xls';
        $this->view->formats = [
            'xls' => __('UI', 'export', 'xls'),
//            'xlsx' => __('UI', 'export', 'xlsx'),
//            'ods' => __('UI', 'export', 'ods'),
        ];

        // Liste des exports.
        $exports = [];

        $displayOrgaExport = (count($cell->getGranularity()->getNarrowerGranularities()) > 0)
            && $this->acl->isAllowed($connectedUser, Actions::EDIT, $cell);
        if ($displayOrgaExport) {
            if (!$cell->getGranularity()->hasAxes()) {
                // Orga Structure.
                $exports['Organization'] = [
                    'label' => __('Orga', 'organization', 'organizationalStructure'),
                ];
            } else {
                // Orga Cell.
                $exports['Cell'] = [
                    'label' => __('Orga', 'organization', 'organizationalStructure'),
                ];
            }
        }

        // Orga ACL.
        $displayACLExport = false;
        if ($cell->getGranularity()->getCellsWithACL()) {
            $displayACLExport = $this->acl->isAllowed($connectedUser, Actions::ALLOW, $cell);
        } else {
            foreach ($cell->getGranularity()->getNarrowerGranularities() as $narrowerGranularity) {
                if ($narrowerGranularity->getCellsWithACL()) {
                    $displayACLExport = $this->acl->isAllowed(
                        $connectedUser,
                        Actions::ALLOW,
                        $cell
                    );
                    break;
                }
            }
        }
        if ($displayACLExport) {
            $exports['Users'] = [
                'label' => __('User', 'role', 'roles'),
            ];
        }

        // Orga Inputs (droit d'analyser nécessaire).
        $displayInputsExport = false;
        if ($cell->getGranularity()->getInputConfigGranularity() !== null) {
            $displayInputsExport = $this->acl->isAllowed(
                $connectedUser,
                Actions::ANALYZE,
                $cell
            );
        } else {
            foreach ($cell->getGranularity()->getNarrowerGranularities() as $narrowerGranularity) {
                if ($narrowerGranularity->getInputConfigGranularity() !== null) {
                    $displayInputsExport = $this->acl->isAllowed(
                        $connectedUser,
                        Actions::ANALYZE,
                        $cell
                    );
                    break;
                }
            }
        }
        if ($displayInputsExport) {
            $exports['Inputs'] = [
                'label' => __('UI', 'name', 'inputs'),
            ];
        }

        // Orga Outputs.
        $displayOutputsExport = false;
        if ($cell->getGranularity()->getInputConfigGranularity() !== null) {
            $displayOutputsExport = $this->acl->isAllowed(
                $connectedUser,
                Actions::ANALYZE,
                $cell
            );
        } else {
            foreach ($cell->getGranularity()->getNarrowerGranularities() as $narrowerGranularity) {
                if ($narrowerGranularity->getInputConfigGranularity() !== null) {
                    $displayOutputsExport = $this->acl->isAllowed(
                        $connectedUser,
                        Actions::ANALYZE,
                        $cell
                    );
                    break;
                }
            }
        }
        if ($displayOutputsExport) {
            // Orga Outputs.
            $exports['Outputs'] = [
                'label' => __('UI', 'name', 'results'),
            ];
        }

        $this->view->assign('exports', $exports);

        // Désactivation du layout.
        $this->_helper->layout()->disableLayout();
    }

    /**
     * @Secure("analyseCell")
     */
    public function exportAction()
    {
        session_write_close();
        set_time_limit(0);
        PHPExcel_Settings::setCacheStorageMethod(PHPExcel_CachedObjectStorageFactory::cache_in_memory_gzip);

        $idCell = $this->getParam('idCell');
        /** @var Orga_Model_Cell $cell */
        $cell = Orga_Model_Cell::load($idCell);

        $format = $this->getParam('format');
        switch ($this->getParam('export')) {
            case 'Organization':
                $streamFunction = 'streamOrganization';
                $baseFilename = __('Orga', 'organization', 'structure');
                break;
            case 'Cell':
                $streamFunction = 'streamCell';
                $baseFilename = __('Orga', 'organization', 'structure');
                break;
            case 'Users':
                $streamFunction = 'streamUsers';
                $baseFilename = __('User', 'role', 'roles');
                break;
            case 'Inputs':
                $streamFunction = 'streamInputs';
                $baseFilename = __('UI', 'name', 'inputs');
                break;
            case 'Outputs':
                $streamFunction = 'streamOutputs';
                $baseFilename = __('UI', 'name', 'results');
                break;
            default:
                UI_Message::addMessageStatic(__('Orga', 'export', 'notFound'), UI_Message::TYPE_ERROR);
                $this->redirect('orga/cell/view/idCell/'.$idCell.'/');
                break;
        }

        $date = date(str_replace('&nbsp;', '', __('DW', 'export', 'dateFormat')));
        $filename = $date . '_' . $baseFilename . '.' . $format;

        switch ($format) {
            case 'xlsx':
                $contentType = "Content-type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet";
                break;
            case 'xls':
                $contentType = "Content-type: application/vnd.ms-excel";
                break;
            case 'ods':
                $contentType = "Content-type: application/vnd.oasis.opendocument.spreadsheet";
                break;
        }
        header($contentType);
        header('Content-Disposition:attachement;filename='.$filename);
        header('Cache-Control: max-age=0');

        Zend_Layout::getMvcInstance()->disableLayout();
        Zend_Controller_Front::getInstance()->setParam('noViewRenderer', true);

        $exportService = new Orga_Service_Export($this->translator);
        $exportService->$streamFunction($format, $cell);
    }

    /**
     * @Secure("editInventoryStatus")
     */
    public function editInventoryStatusAction()
    {
        /** @var Orga_Model_Cell $cell */
        $cell = Orga_Model_Cell::load($this->getParam('idCell'));

        $inventoryStatus = $this->getParam('inventoryStatus');

        $cell->setInventoryStatus($inventoryStatus);

        $this->sendJsonResponse([
            'status' => $inventoryStatus,
            'label' => $this->cellVMFactory->inventoryStatusList[$inventoryStatus],
            'mainActionStatus' => ($inventoryStatus === Orga_Model_Cell::INVENTORY_STATUS_ACTIVE) ? Orga_Model_Cell::INVENTORY_STATUS_CLOSED : Orga_Model_Cell::INVENTORY_STATUS_ACTIVE,
            'mainActionLabel' => ($inventoryStatus === Orga_Model_Cell::INVENTORY_STATUS_NOTLAUNCHED) ? ___('Orga', 'view', 'inventoryNotLaunchedMainAction') : (($inventoryStatus == Orga_Model_Cell::INVENTORY_STATUS_ACTIVE) ? ___('Orga', 'view', 'inventoryActiveMainAction') : ___('Orga', 'view', 'inventoryClosedMainAction')),
            'otherActionStatus' => ($inventoryStatus === Orga_Model_Cell::INVENTORY_STATUS_NOTLAUNCHED) ? Orga_Model_Cell::INVENTORY_STATUS_CLOSED : Orga_Model_Cell::INVENTORY_STATUS_NOTLAUNCHED,
            'otherActionLabel' => ($inventoryStatus === Orga_Model_Cell::INVENTORY_STATUS_NOTLAUNCHED) ? ___('Orga', 'view', 'inventoryNotLaunchedOtherAction') : (($inventoryStatus == Orga_Model_Cell::INVENTORY_STATUS_ACTIVE) ? ___('Orga', 'view', 'inventoryActiveOtherAction') : ___('Orga', 'view', 'inventoryClosedOtherAction')),
        ]);
    }

    /**
     * @Secure("viewCell")
     */
    public function viewInventoryUsersAction()
    {
        $idCell = $this->getParam('idCell');
        /** @var Orga_Model_Cell $cell */
        $cell = Orga_Model_Cell::load($idCell);

        $users = [];

        /** @var Orga_Model_Cell[] $inventoryCells */
        $inventoryCells = array_merge([$cell], $cell->getParentCells(), $cell->getChildCells());
        foreach ($inventoryCells as $inventoryCell) {
            foreach ($inventoryCell->getAdminRoles() as $adminRole) {
                $users[] = $adminRole->getSecurityIdentity()->getEmail();
            }
            foreach ($inventoryCell->getManagerRoles() as $managerRole) {
                $users[] = $managerRole->getSecurityIdentity()->getEmail();
            }
            foreach ($inventoryCell->getContributorRoles() as $contributorRole) {
                $users[] = $contributorRole->getSecurityIdentity()->getEmail();
            }
        }

        $users = array_unique($users);
        asort($users);

        $this->view->assign('users', $users);

        // Désactivation du layout.
        $this->_helper->layout()->disableLayout();
    }

    /**
     * @Secure("viewCell")
     */
    public function inputAction()
    {
        /** @var Orga_Model_Cell $cell */
        $cell = Orga_Model_Cell::load($this->getParam('idCell'));
        $af = $cell->getInputAFUsed();
        $organization = $cell->getOrganization();
        $fromIdCell = $this->hasParam('fromIdCell') ? $this->getParam('fromIdCell') : $cell->getId();

        $isUserAllowedToInputCell = $this->acl->isAllowed(
            $this->_helper->auth(),
            Actions::INPUT,
            $cell
        );

        $viewConfiguration = new AFViewConfiguration();
        if ($isUserAllowedToInputCell && ($cell->getInventoryStatus() !== Orga_Model_Cell::INVENTORY_STATUS_CLOSED)) {
            $viewConfiguration->setMode(AFViewConfiguration::MODE_WRITE);
        } else {
            $viewConfiguration->setMode(AFViewConfiguration::MODE_READ);
        }
        $viewConfiguration->setPageTitle(
            __('UI', 'name', 'input').' <small>'.$this->translator->get($cell->getLabel()).'</small>'
        );
        $viewConfiguration->addToActionStack('input-save', 'cell', 'orga', ['idCell' => $cell->getId()]);
        $viewConfiguration->setInputValidationUrl(
            'orga/cell/input-validation?id=' . $af->getId() . '&idCell=' . $cell->getId()
        );
        $viewConfiguration->setResultsPreviewUrl(
            'orga/cell/input-preview?id=' . $af->getId() . '&idCell=' . $cell->getId()
        );
        $viewConfiguration->setExitUrl('orga/cell/view/idCell/' . $fromIdCell . '/');
        $viewConfiguration->addUrlParam('idCell', $cell->getId());
        $viewConfiguration->setDisplayConfigurationLink(false);
        $viewConfiguration->addBaseTab(AFViewConfiguration::TAB_INPUT);
        $viewConfiguration->setInputSet($cell->getAFInputSetPrimary());

        // Saisie de l'année précédente
        $timeAxis = $organization->getTimeAxis();
        if ($timeAxis && $cell->getGranularity()->hasAxis($timeAxis)) {
            $previousCell = $cell->getPreviousCellForAxis($timeAxis);
            if ($previousCell) {
                $previousInput = $previousCell->getAFInputSetPrimary();
                $label = $this->translator->get($previousCell->getLabel());
                $viewConfiguration->setPreviousInputSet($label, $previousInput);
            }
        }

        $tabComments = new Tab('inputComments');
        $tabComments->setTitle(__('Orga', 'comment', 'comments'));
        $commentView = new Zend_View();
        $commentView->setScriptPath(__DIR__ . '/../views/scripts');
        $commentView->assign('idCell', $cell->getId());
        $commentView->assign(
            'isUserAbleToComment',
            $this->acl->isAllowed($this->_helper->auth(), Actions::INPUT, $cell)
        );
        $tabComments->setContent($commentView->render('cell/input-comments.phtml'));
        $viewConfiguration->addTab($tabComments);

        $tabDocs = new Tab('inputDocs');
        $tabDocs->setTitle(__('Doc', 'name', 'documents'));
        $tabDocs->setContent('orga/cell/input-docs/idCell/' . $cell->getId());
        $tabDocs->setAjax(true, true);
        $viewConfiguration->addTab($tabDocs);

        $isUserAllowedToViewCellReports = $this->acl->isAllowed(
            $this->_helper->auth(),
            Actions::ANALYZE,
            $cell
        );
        if ($isUserAllowedToViewCellReports) {
            $viewConfiguration->addBaseTab(AFViewConfiguration::TAB_RESULT);
            $viewConfiguration->addBaseTab(AFViewConfiguration::TAB_CALCULATION_DETAILS);
        }
        $viewConfiguration->setResultsPreview($isUserAllowedToViewCellReports);

        $this->setActiveMenuItemOrganization($cell->getOrganization()->getId());

        $this->forward('display', 'af', 'af', [
            'id' => $af->getId(),
            'viewConfiguration' => $viewConfiguration,
        ]);
    }

    /**
     * @see \AF_InputController::inputValidationAction
     * @Secure("inputCell")
     */
    public function inputValidationAction()
    {
        $cell = Orga_Model_Cell::load($this->getParam('idCell'));

        /** @var $af AF */
        $af = AF::load($this->getParam('id'));

        $inputSet = $this->inputSerializer->unserialize($this->getParam('input'), $af);

        $validator = new InputSetValuesValidator($inputSet);
        $validator->validate();

        $this->inputService->updateInconsistentInputSetFromPreviousValue($cell, $inputSet);

        $data = ['input' => $this->inputSerializer->serialize($inputSet)];
        $this->sendJsonResponse($data);

        $this->entityManager->clear();
    }

    /**
     * @see \AF_InputController::resultsPreviewAction
     * @Secure("inputCell")
     */
    public function inputPreviewAction()
    {
        $cell = Orga_Model_Cell::load($this->getParam('idCell'));

        $af = AF::load($this->getParam('id'));

        $inputSet = $this->inputSerializer->unserialize($this->getParam('input'), $af);

        $this->inputService->updateResults($cell, $inputSet);

        $this->view->assign('inputSet', $inputSet);
        // Moche mais sinon je me petit-suicide
        $this->view->addScriptPath(PACKAGE_PATH . '/src/AF/Application/views/scripts/');
        $data = $this->view->render('af/display-results.phtml');
        $this->sendFormResponse($data);
    }

    /**
     * @Secure("inputCell")
     */
    public function inputSaveAction()
    {
        $idCell = $this->getParam('idCell');
        /** @var Orga_Model_Cell $cell */
        $cell = Orga_Model_Cell::load($idCell);

        $inputSetContainer = $this->getParam('inputSetContainer');
        /** @var $newInputSet PrimaryInputSet */
        $newInputSet = $inputSetContainer->inputSet;

        $this->inputService->editInput($cell, $newInputSet);

        $this->entityManager->flush();

        // Remplace l'input set temporaire par celui de la cellule
        $inputSetContainer->inputSet = $cell->getAFInputSetPrimary();

        $this->_helper->viewRenderer->setNoRender(true);
    }

    /**
     * @Secure("viewCell")
     */
    public function inputDocsAction()
    {
        $idCell = $this->getParam('idCell');
        /** @var Orga_Model_Cell $cell */
        $cell = Orga_Model_Cell::load($idCell);

        $this->view->assign('idCell', $idCell);
        $this->view->assign('documentLibrary', $cell->getDocLibraryForAFInputSetPrimary());

        // Désactivation du layout.
        $this->_helper->layout()->disableLayout();
    }
}
