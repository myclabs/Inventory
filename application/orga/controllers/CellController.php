<?php

use Account\Application\Service\OrganizationViewFactory;
use AF\Application\InputFormParser;
use AF\Application\AFViewConfiguration;
use AF\Domain\AF;
use AF\Domain\InputSet\PrimaryInputSet;
use Core\Work\ServiceCall\ServiceCallTask;
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
     * @var InputFormParser
     */
    private $inputFormParser;

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
            $isNarrowerGranularityInventory = (($granularityForInventoryStatus !== null)
                && (($narrowerGranularity === $granularityForInventoryStatus)
                    || ($narrowerGranularity->isNarrowerThan($granularityForInventoryStatus))));
            if ($isNarrowerGranularityInventory) {
                $narrowerGranularityHasACLParent = $narrowerGranularity->getCellsWithACL();
                if (!$narrowerGranularityHasACLParent) {
                    foreach ($narrowerGranularity->getBroaderGranularities() as $broaderInventoryGranularity) {
                        if ($broaderInventoryGranularity->getCellsWithACL()) {
                            foreach ($narrowerGranularity->getAxes() as $narrowerGranularityAxis) {
                                if (!$granularityForInventoryStatus->hasAxis($narrowerGranularityAxis)
                                    && !$broaderInventoryGranularity->hasAxis($narrowerGranularityAxis)) {
                                    continue 2;
                                }
                            }
                            $narrowerGranularityHasACLParent = true;
                            break;
                        }
                    }
                }
                $isNarrowerGranularityInventory = $isNarrowerGranularityInventory && $narrowerGranularityHasACLParent;
            }
            if ($isNarrowerGranularityInventory) {
                $narrowerGranularityHasSubInputGranlarities = false;
                foreach ($narrowerGranularity->getNarrowerGranularities() as $narrowerInventoryGranularity) {
                    if ($narrowerInventoryGranularity->getInputConfigGranularity() !== null) {
                        $narrowerGranularityHasSubInputGranlarities = true;
                        break;
                    }
                }
                $isNarrowerGranularityInventory = $isNarrowerGranularityInventory && $narrowerGranularityHasSubInputGranlarities;
            }
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
                $axisOption = new GenericTag('option', $this->translator->toString($axis->getLabel()));
                $axisOption->setAttribute('value', $axis->getRef());
                $axisChoiceInput->appendContent($axisOption);

                foreach ($axis->getDirectBroaders() as $broaderAxis) {
                    $parentMemberGroup = new GenericTag('div');
                    $parentMemberGroup->addClass('form-group');
                    $parentMemberGroup->addClass('hide');
                    $parentMemberGroup->addClass('broader-axis');
                    $parentMemberGroup->addClass($axis->getRef());
                    $addMemberForm->appendContent($parentMemberGroup);

                    $parentMemberChoiceLabel = new GenericTag('label', $this->translator->toString($broaderAxis->getLabel()));
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
                        $parentMemberOption = new GenericTag('option', $this->translator->toString($parentMember->getLabel()));
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
            'AXIS' => $this->translator->toString($axis->getLabel())
        ]);

        $filter = $cell->getChildMembersForAxes([$axis]);
        /** @var Orga_Model_Member[] $members */
        if ((count($filter) > 0) && isset($filter[$axis->getRef()])) {
            $members = $filter[$axis->getRef()];
        } else {
            $members = $axis->getOrderedMembers()->toArray();
        }
        foreach ($members as $member) {
            $memberOptions[$member->getTag()] = $this->translator->toString($member->getLabel());
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
        $showInventory = $editInventory || ($showInput && (($granularityForInventoryStatus !== null)
            && (($narrowerGranularity === $granularityForInventoryStatus)
                || ($narrowerGranularity->isNarrowerThan($granularityForInventoryStatus)))));

        $showInventoryProgress = $showInventory;
        if ($showInventoryProgress) {
            $narrowerGranularityHasACLParent = $narrowerGranularity->getCellsWithACL();
            if (!$narrowerGranularityHasACLParent) {
                foreach ($narrowerGranularity->getBroaderGranularities() as $broaderInventoryGranularity) {
                    if ($broaderInventoryGranularity->getCellsWithACL()) {
                        foreach ($narrowerGranularity->getAxes() as $narrowerGranularityAxis) {
                            if (!$granularityForInventoryStatus->hasAxis($narrowerGranularityAxis)
                                && !$broaderInventoryGranularity->hasAxis($narrowerGranularityAxis)) {
                                continue 2;
                            }
                        }
                        $narrowerGranularityHasACLParent = true;
                        break;
                    }
                }
            }
            $showInventoryProgress = $showInventoryProgress && $narrowerGranularityHasACLParent;
        }
        if ($showInventoryProgress) {
            $narrowerGranularityHasSubInputGranlarities = false;
            foreach ($narrowerGranularity->getNarrowerGranularities() as $narrowerInventoryGranularity) {
                if ($narrowerInventoryGranularity->getInputConfigGranularity() !== null) {
                    $narrowerGranularityHasSubInputGranlarities = true;
                    break;
                }
            }
            $showInventoryProgress = $showInventoryProgress && $narrowerGranularityHasSubInputGranlarities;
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

        $context = new OrganizationContext($cell->getGranularity()->getOrganization());
        $context->setCell($cell);
        $locale = Core_Locale::loadDefault();

        $events = [];
        foreach ($this->auditTrailRepository->findLatestForOrganizationContext($context, 10) as $entry) {
            $eventText = __('Orga', 'auditTrail', $entry->getEventName(), [
                'INPUT' => '<a href="orga/cell/input/idCell/' . $entry->getContext()->getCell()->getId()
                                . '/fromIdCell/' . $cell->getId() . '/">'
                                . $entry->getContext()->getCell()->getLabel()
                            . '</a>',
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

        /** @var Orga_Model_Cell $cell */
        $cell = Orga_Model_Cell::load($this->getParam('idCell'));

        /** @var Orga_Model_Repository_Cell $cellRepository */
        $cellRepository = $this->entityManager->getRepository(Orga_Model_Cell::class);

        $locale = Core_Locale::loadDefault();

        $comments = [];
        /** @var Orga_Model_Cell_InputComment $comment */
        foreach ($cellRepository->getLatestComments($cell, 10) as $comment) {
            $commentText = __('Social', 'comment', 'by') . ' <b>' . $comment->getAuthor()->getName() . '</b> '
                . __('Orga', 'input', 'aboutInput')
                . ' <a href="orga/cell/input/idCell/' . $comment->getCell()->getId()
                    . '/fromIdCell/' . $cell->getId() . '/tab/comments/">'
                    . $this->translator->toString($comment->getCell()->getLabel())
                . '</a>' . __('UI', 'other', ':')
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
                'AXIS' => $this->translator->toString($axis->getLabel()),
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
                    if (DW_Export_Specific_Pdf::isValid($specificReportsDirectoryPath.$entry)) {
                        $cellReports[] = [
                            'label' => $fileName,
                            'link' => 'orga/cell/view-report-specific/idCell/'.$idCell.'/fromIdCell/'.$fromIdCell.'/report/'.$fileName,
                            'type' => 'specificReport',
                        ];
                    }
                }
            }
        }
        // Copied Reports.
        /** @var Orga_Model_CellReport[] $usersReports */
        $usersReports = [];
        $dWReports = $cell->getDWCube()->getReports();
        usort($dWReports, function (DW_Model_Report $a, DW_Model_Report $b) {
            return strcmp(
                $this->translator->toString($a->getLabel()),
                $this->translator->toString($b->getLabel())
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
                'label' => $this->translator->toString($dWReport->getLabel()),
                'link' => 'orga/cell/view-report/idCell/'.$idCell.'/fromIdCell/'.$fromIdCell.'/idReport/'.$dWReport->getId(),
                'type' => 'copiedReport',
            ];
        }
        // User Reports.
        $otherUsers = [];
        foreach ($usersReports as $cellReport) {
            /** @var DW_Model_Report $dWReport */
            $cellReports[] = [
                'label' => $this->translator->toString($cellReport->getCellDWReport()->getLabel()),
                'link' => 'orga/cell/view-report/idCell/'.$idCell.'/fromIdCell/'.$fromIdCell.'/idReport/'.$cellReport->getCellDWReport()->getId(),
                'type' => 'userReport',
                'owner' => $cellReport->getOwner(),
                'delete' => ($cellReport->getOwner() === $connectedUser)
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
        DW_Model_Report::load($this->getParam('idReport'))->delete();
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
            $report = DW_Model_Report::load($this->getParam('idReport'));
            try {
                $cellReport = Orga_Model_CellReport::loadByCellDWReport($report);
                $reportCanBeUpdated = ($cellReport->getOwner() === $connectedUser);
            } catch (Core_Exception_NotFound $e) {
                // Rapport copié.
            }
        }

        $viewConfiguration = new DW_ViewConfiguration();
        $viewConfiguration->setComplementaryPageTitle(
            ' <small>'.$this->translator->toString($cell->getExtendedLabel()).'</small>'
        );
        $viewConfiguration->setOutputUrl('orga/cell/view/idCell/'.$fromIdCell.'/');
        $viewConfiguration->setSaveURL('orga/cell/view-report/idCell/'.$fromIdCell);
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
        $specificReports = new DW_Export_Specific_Pdf(
            $specificReportsDirectoryPath.$this->getParam('report').'.xml',
            $cell->getDWCube(),
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

        $exportService = new Orga_Service_Export();
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
            'mainActionStatus' => ($inventoryStatus === Orga_Model_Cell::STATUS_ACTIVE) ? Orga_Model_Cell::STATUS_CLOSED : Orga_Model_Cell::STATUS_ACTIVE,
            'mainActionLabel' => ($inventoryStatus === Orga_Model_Cell::STATUS_NOTLAUNCHED) ? ___('Orga', 'view', 'inventoryNotLaunchedMainAction') : (($inventoryStatus == Orga_Model_Cell::STATUS_ACTIVE) ? ___('Orga', 'view', 'inventoryActiveMainAction') : ___('Orga', 'view', 'inventoryClosedMainAction')),
            'otherActionStatus' => ($inventoryStatus === Orga_Model_Cell::STATUS_NOTLAUNCHED) ? Orga_Model_Cell::STATUS_CLOSED : Orga_Model_Cell::STATUS_NOTLAUNCHED,
            'otherActionLabel' => ($inventoryStatus === Orga_Model_Cell::STATUS_NOTLAUNCHED) ? ___('Orga', 'view', 'inventoryNotLaunchedOtherAction') : (($inventoryStatus == Orga_Model_Cell::STATUS_ACTIVE) ? ___('Orga', 'view', 'inventoryActiveOtherAction') : ___('Orga', 'view', 'inventoryClosedOtherAction')),
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
        $idCell = $this->getParam('idCell');
        /** @var Orga_Model_Cell $cell */
        $cell = Orga_Model_Cell::load($idCell);
        $fromIdCell = $this->hasParam('fromIdCell') ? $this->getParam('fromIdCell') : $idCell;

        $isUserAllowedToInputCell = $this->acl->isAllowed(
            $this->_helper->auth(),
            Actions::INPUT,
            $cell
        );

        $aFViewConfiguration = new AFViewConfiguration();
        if ($isUserAllowedToInputCell && ($cell->getInventoryStatus() !== Orga_Model_Cell::STATUS_CLOSED)) {
            $aFViewConfiguration->setMode(AFViewConfiguration::MODE_WRITE);
        } else {
            $aFViewConfiguration->setMode(AFViewConfiguration::MODE_READ);
        }
        $aFViewConfiguration->setPageTitle(
            __('UI', 'name', 'input').' <small>'.$this->translator->toString($cell->getLabel()).'</small>'
        );
        $aFViewConfiguration->addToActionStack('input-save', 'cell', 'orga', ['idCell' => $idCell]);
        $aFViewConfiguration->setResultsPreviewUrl('orga/cell/input-preview');
        $aFViewConfiguration->setExitUrl('orga/cell/view/idCell/' . $fromIdCell . '/');
        $aFViewConfiguration->addUrlParam('idCell', $idCell);
        $aFViewConfiguration->setDisplayConfigurationLink(false);
        $aFViewConfiguration->addBaseTab(AFViewConfiguration::TAB_INPUT);
        if ($cell->getAFInputSetPrimary() !== null) {
            $aFViewConfiguration->setIdInputSet($cell->getAFInputSetPrimary()->getId());
        }

        $tabComments = new Tab('inputComments');
        $tabComments->setTitle(__('Social', 'comment', 'comments'));
        $commentView = new Zend_View();
        $commentView->setScriptPath(__DIR__ . '/../views/scripts');
        $commentView->assign('idCell', $idCell);
        $commentView->assign(
            'isUserAbleToComment',
            $this->acl->isAllowed($this->_helper->auth(), Actions::INPUT, $cell)
        );
        $tabComments->setContent($commentView->render('cell/input-comments.phtml'));
        $aFViewConfiguration->addTab($tabComments);

        $tabDocs = new Tab('inputDocs');
        $tabDocs->setTitle(__('Doc', 'name', 'documents'));
        $tabDocs->setContent('orga/cell/input-docs/idCell/'.$idCell);
        $tabDocs->setAjax(true, true);
        $aFViewConfiguration->addTab($tabDocs);

        $isUserAllowedToViewCellReports = $this->acl->isAllowed(
            $this->_helper->auth(),
            Actions::ANALYZE,
            $cell
        );
        if ($isUserAllowedToViewCellReports) {
            $aFViewConfiguration->addBaseTab(AFViewConfiguration::TAB_RESULT);
            $aFViewConfiguration->addBaseTab(AFViewConfiguration::TAB_CALCULATION_DETAILS);
        }
        $aFViewConfiguration->setResultsPreview($isUserAllowedToViewCellReports);

        $this->setActiveMenuItemOrganization($cell->getOrganization()->getId());

        $this->forward('display', 'af', 'af', [
            'id' => $cell->getInputAFUsed()->getId(),
            'viewConfiguration' => $aFViewConfiguration
        ]);
    }

    /**
     * @see \AF_InputController::resultsPreviewAction
     * @Secure("inputCell")
     */
    public function inputPreviewAction()
    {
        $idCell = $this->getParam('idCell');
        $cell = Orga_Model_Cell::load($idCell);

        $af = AF::load($this->getParam('id'));

        // Form data
        $formContent = json_decode($this->getParam('af' . $af->getId()), true);
        $errorMessages = [];

        // Remplit l'InputSet
        $inputSet = $this->inputFormParser->parseForm($formContent, $af, $errorMessages);
        $this->inputService->updateResults($cell, $inputSet);

        $this->addFormErrors($errorMessages);

        /** @noinspection PhpUndefinedFieldInspection */
        $this->view->inputSet = $inputSet;

        // Moche mais sinon je me petit-suicide
        $this->view->addScriptPath(PACKAGE_PATH . '/src/AF/Application/views/scripts/');
        $data = $this->view->render('af/display-results.phtml');

        // Force le statut en success (sinon les handlers JS ne sont pas exécutés)
        $this->setFormMessage(null, UI_Message::TYPE_SUCCESS);
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
