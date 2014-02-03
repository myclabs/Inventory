<?php

use AF\Application\InputFormParser;
use AF\Application\AFViewConfiguration;
use AF\Domain\AF;
use AF\Domain\InputSet\PrimaryInputSet;
use Core\Annotation\Secure;
use Core\Work\ServiceCall\ServiceCallTask;
use MyCLabs\Work\Dispatcher\WorkDispatcher;
use Orga\ViewModel\OrganizationViewModelFactory;
use Orga\ViewModel\CellViewModelFactory;
use AuditTrail\Domain\Context\OrganizationContext;
use AuditTrail\Domain\EntryRepository;
use Doctrine\Common\Collections\Criteria;
use Orga\Model\ACL\Action\CellAction;
use User\Domain\User;
use User\Domain\ACL\Action;
use User\Domain\ACL\ACLService;
use User\Domain\ACL\Role\Role;
use Orga\Model\ACL\Role\AbstractCellRole;
use Orga\Model\ACL\Role\CellAdminRole;
use Orga\Model\ACL\Role\CellManagerRole;
use Orga\Model\ACL\Role\CellContributorRole;
use Orga\Model\ACL\Role\CellObserverRole;

/**
 * @author valentin.claras
 */
class Orga_CellController extends Core_Controller
{
    use UI_Controller_Helper_Form;

    /**
     * @Inject
     * @var ACLService
     */
    private $aclService;

    /**
     * @Inject
     * @var Orga_Service_ACLManager
     */
    private $aclManager;

    /**
     * @Inject
     * @var WorkDispatcher
     */
    private $workDispatcher;

    /**
     * @Inject
     * @var OrganizationViewModelFactory
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
     * @Inject
     * @var Social_Service_CommentService
     */
    private $commentService;

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
        $this->view->assign('organization', $this->organizationVMFactory->createOrganizationViewModel($organization, $connectedUser));
        $this->view->assign('currentCell', $this->cellVMFactory->createCellViewModel($cell, $connectedUser, true));

        $isUserAllowedToEditOrganization = $this->aclService->isAllowed(
            $connectedUser,
            Action::EDIT(),
            $organization
        );
        $isUserAllowToEditAllMembers = $isUserAllowedToEditOrganization || $this->aclService->isAllowed(
            $connectedUser,
            Action::EDIT(),
            $organization->getGranularityByRef('global')->getCellByMembers([])
        );
        if (!$isUserAllowToEditAllMembers) {
            $topCellsWithEditAccess = $this->aclManager->getTopCellsWithAccessForOrganization(
                $connectedUser,
                $organization,
                [CellAdminRole::class]
            )['cells'];
        }

        // Cellules enfants.
        $narrowerGranularities = [];
        try {
            $granularityForInventoryStatus = $cell->getGranularity()->getOrganization()->getGranularityForInventoryStatus();
        } catch (Core_Exception_UndefinedAttribute $e) {
            $granularityForInventoryStatus = null;
        }
        $this->view->assign('granularityForInventoryStatus', $granularityForInventoryStatus);
        foreach ($cell->getGranularity()->getNarrowerGranularities() as $narrowerGranularity) {
            $purpose = '';
            // ACL purpose.
            $isNarrowerGranularityACL = ($narrowerGranularity->getCellsWithACL());
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
            $narrowerGranularityHasSubInputGranlarities = false;
            foreach ($narrowerGranularity->getNarrowerGranularities() as $narrowerInventoryGranularity) {
                if ($narrowerInventoryGranularity->getInputConfigGranularity() !== null) {
                    $narrowerGranularityHasSubInputGranlarities = true;
                    break;
                }
            }
            if ($isNarrowerGranularityInventory && $narrowerGranularityHasSubInputGranlarities) {
                $purpose .= __('Orga', 'inventory', 'inventories');
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
            $isNarrowerGranularityAnalyses = ($narrowerGranularity->getCellsGenerateDWCubes());
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
                $narrowerGranularityAxes = array_merge($narrowerGranularityAxes, $narrowerAxis->getAllBroadersFirstOrdered());
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
                $narrowerGranularities[] = [
                    'granularity' => $narrowerGranularity,
                    'purpose' => $purpose,
                    'filterAxes' => $filterAxes,
                    'isAcl' => $isNarrowerGranularityACL,
                    'isInventory' => $isNarrowerGranularityInventory,
                    'isGranularityForInventoryStatus' => ($narrowerGranularity === $granularityForInventoryStatus),
                    'isInput' => $isNarrowerGranularityInput,
                    'isAnalyses' => $isNarrowerGranularityAnalyses,
                ];
            }
        }
        $this->view->assign('narrowerGranularities', $narrowerGranularities);

        // Formulaire d'ajout des membres enfants.
        $axesCanEdit = $this->aclManager->getAxesCanEdit($connectedUser, $organization);
        $this->view->assign('canAddMembers', (count($axesCanEdit) > 0));
        if (count($axesCanEdit) > 0) {
            $addMembersForm = new UI_Form('addMember');
            $addMembersForm->setAction('orga/cell/add-member/idCell/'.$idCell);
            $selectAxis = new UI_Form_Element_Select('axis');
            $selectAxis->setLabel(__('UI', 'name', 'axis'));
            $selectAxis->getElement()->help = __('Orga', 'view', 'addMembersAxisExplanations');
            $selectAxis->addNullOption('');
            $addMembersForm->addElement($selectAxis);
            foreach ($axesCanEdit as $axis) {
                $axisOption = new UI_Form_Element_Option($axis->getRef(), $axis->getRef(), $axis->getLabel());
                $selectAxis->addOption($axisOption);

                $axisGroup = new UI_Form_Element_Group($axis->getRef().'_group');
                $axisGroup->setLabel('');
                $axisGroup->foldaway = false;
                $axisGroup->getElement()->hidden = true;

                $memberInput = new UI_Form_Element_Text($axis->getRef().'_member');
                $memberInput->setLabel(__('UI', 'name', 'element'));
                $memberInput->setAttrib('placeholder', __('UI', 'name', 'label'));
                $axisGroup->addElement($memberInput);
                foreach ($axis->getDirectBroaders() as $broaderAxis) {
                    $selectParentMember = new UI_Form_Element_Select($axis->getRef().'_parentMember_'.$broaderAxis->getRef());
                    $selectParentMember->setLabel($broaderAxis->getLabel());
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
                        $parentMemberOption = new UI_Form_Element_Option($parentMember->getId(), $parentMember->getId(), $parentMember->getLabel());
                        $selectParentMember->addOption($parentMemberOption);
                    }
                    $axisGroup->addElement($selectParentMember);
                }

                $displayGroupAction = new UI_Form_Action_Show($axis->getRef().'_toggle');
                $displayGroupAction->condition = new UI_Form_Condition_Elementary('', $selectAxis, UI_Form_Condition_Elementary::EQUAL, $axis->getRef());
                $axisGroup->getElement()->addAction($displayGroupAction);

                $addMembersForm->addElement($axisGroup);
            }
            $addMembersForm->addSubmitButton('Ajouter');
            $this->view->assign('addMembersForm', $addMembersForm);
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
        $memberOptions[''] = __('Orga', 'view', 'allMembers', ['AXIS' => $axis->getLabel()]);

        $filter = $cell->getChildMembersForAxes([$axis]);
        /** @var Orga_Model_Member[] $members */
        if ((count($filter) > 0) && isset($filter[$axis->getRef()])) {
            $members = $filter[$axis->getRef()];
        } else {
            $members = $axis->getOrderedMembers()->toArray();
        }
        foreach ($members as $member) {
            $memberOptions[$member->getTag()] = $member->getLabel();
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
        $showUsers = $narrowerGranularity->getCellsWithACL() && $this->aclService->isAllowed($connectedUser, Action::ALLOW(), $cell);

        // Reports.
        $showReports = $narrowerGranularity->getCellsGenerateDWCubes() && $this->aclService->isAllowed($connectedUser, CellAction::VIEW_REPORTS(), $cell);

        // Exports
        $showExports = $this->aclService->isAllowed($connectedUser, CellAction::VIEW_REPORTS(), $cell);

        // Inventory.
        try {
            $granularityForInventoryStatus = $cell->getGranularity()->getOrganization()->getGranularityForInventoryStatus();
        } catch (Core_Exception_UndefinedAttribute $e) {
            $granularityForInventoryStatus = null;
        }
        $editInventory = (($narrowerGranularity === $granularityForInventoryStatus)
            && $this->aclService->isAllowed($connectedUser, Action::EDIT(), $cell));
        $isInventory = (($narrowerGranularity === $granularityForInventoryStatus)
                || ($narrowerGranularity->isNarrowerThan($granularityForInventoryStatus)));
        $narrowerGranularityHasSubInputGranlarities = false;
        foreach ($narrowerGranularity->getNarrowerGranularities() as $narrowerInventoryGranularity) {
            if ($narrowerInventoryGranularity->getInputConfigGranularity() !== null) {
                $narrowerGranularityHasSubInputGranlarities = true;
                break;
            }
        }
        $showInventory = $isInventory && $narrowerGranularityHasSubInputGranlarities;

        // Input.
        $showInput = ($narrowerGranularity->getInputConfigGranularity() !== null);

        // Uniquement les sous-cellules pertinentes.
        $relevantCriteria = new Criteria();
        $relevantCriteria->where($relevantCriteria->expr()->eq(Orga_Model_Cell::QUERY_RELEVANT, true));
        $relevantCriteria->andWhere($relevantCriteria->expr()->eq(Orga_Model_Cell::QUERY_ALLPARENTSRELEVANT, true));
        foreach (explode(Orga_Model_Organization::PATH_JOIN, $cell->getTag()) as $pathTag) {
            $relevantCriteria->andWhere($relevantCriteria->expr()->contains('tag', $pathTag));
        }

        $childCells = [];
        /** @var Orga_Model_Cell $childCell */
        foreach ($narrowerGranularity->getCells()->matching($relevantCriteria) as $childCell) {
            $childCells[] = $this->cellVMFactory->createCellViewModel(
                $childCell,
                $connectedUser,
                $showAdministrators,
                $showUsers,
                $showReports,
                $showExports,
                $showInventory,
                $editInventory,
                $showInput
            );
        }

        $relevantQuery = new Core_Model_Query();
        $relevantQuery->filter->addCondition(Orga_Model_Cell::QUERY_RELEVANT, true);
        $relevantQuery->filter->addCondition(Orga_Model_Cell::QUERY_ALLPARENTSRELEVANT, true);
        $totalChildCells = $cell->countTotalChildCellsForGranularity($narrowerGranularity, $relevantQuery);

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
        /** @var Orga_Model_InputComment|Social_Model_Comment $comment */
        foreach ($cellRepository->getLatestComments($cell, 10) as $comment) {
            $commentText = __('Social', 'comment', 'by') . ' <b>' . $comment->getAuthor()->getName() . '</b> '
                . __('Orga', 'input', 'aboutInput')
                . ' <a href="orga/cell/input/idCell/' . $comment->getCell()->getId()
                    . '/fromIdCell/' . $cell->getId() . '/tab/comments/">'
                    . $comment->getCell()->getLabel()
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

        $axisRef = $formData['axis']['value'];
        if (empty($axisRef)) {
            $this->addFormError('axis', __('UI', 'formValidation', 'emptyRequiredField'));
            $this->sendFormResponse();
            return;
        }
        $axis = $cell->getOrganization()->getAxisByRef($axisRef);

        $axisData = $formData[$axis->getRef().'_group']['elements'];
        $parentMembers = [];
        $contextualizingParentMembers = [];
        foreach ($axis->getDirectBroaders() as $broaderAxis) {
            $parentAxisFieldRef = $axis->getRef() . '_parentMember_' . $broaderAxis->getRef();
            $parentMember = Orga_Model_Member::load($axisData[$parentAxisFieldRef]['value']);
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

        $label = $axisData[$axis->getRef() . '_member']['value'];
        if (empty($label)) {
            $this->addFormError($axis->getRef() . '_member', __('UI', 'formValidation', 'emptyRequiredField'));
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
            __('Orga', 'backgroundTasks', 'addMember', ['MEMBER' => $label, 'AXIS' => $axis->getLabel()])
        );
        $this->workDispatcher->runBackground($task, $this->waitDelay, $success, $timeout, $error);
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
            function(AbstractCellRole $a, AbstractCellRole $b) {
                $aUser = $a->getUser();
                $bUser = $b->getUser();
                if (get_class($a) === get_class($b)) {
                    if ($aUser->getFirstName() === $bUser->getFirstName()) {
                        if ($aUser->getLastName() === $bUser->getLastName()) {
                            return strcmp($aUser->getEmail(), $bUser->getEmail());
                        }
                        return strcmp($aUser->getLastName(), $bUser->getLastName());
                    }
                    return strcmp($aUser->getFirstName(), $bUser->getFirstName());
                }
                if ($a instanceof CellAdminRole)
                    return -1;
                if ($b instanceof CellAdminRole)
                    return 1;
                if ($a instanceof CellManagerRole)
                    return -1;
                if ($b instanceof CellManagerRole)
                    return 1;
                if ($a instanceof CellContributorRole)
                    return -1;
                if ($b instanceof CellContributorRole)
                    return 1;
                if ($a instanceof CellObserverRole)
                    return -1;
                if ($b instanceof CellObserverRole)
                    return 1;
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
                return;
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
        $this->workDispatcher->runBackground($task, $this->waitDelay, $success, $timeout, $error);
    }

    /**
     * @Secure("allowCell")
     */
    public function removeUserAction()
    {
        $idCell = $this->getParam('idCell');
        /** @var Orga_Model_Cell $cell */
        $cell = Orga_Model_Cell::load($idCell);

        $idRole = $this->getParam('idRole');
        /** @var AbstractCellRole $role */
        $role = Role::load($idRole);
        $user = $role->getUser();

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
        $this->workDispatcher->runBackground($task, $this->waitDelay, $success, $timeout, $error);
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
        usort($dWReports, function(DW_Model_Report $a, DW_Model_Report $b) { return strcmp($a->getLabel(), $b->getLabel()); });
        foreach ($dWReports as $dWReport) {
            try {
                $usersReports[] = Orga_Model_CellReport::loadByCellDWReport($dWReport);
                continue;
            } catch (Core_Exception_NotFound $e) {
                // Rapport Copié.
            }
            $cellReports[] = [
                'label' => $dWReport->getLabel(),
                'link' => 'orga/cell/view-report/idCell/'.$idCell.'/fromIdCell/'.$fromIdCell.'/idReport/'.$dWReport->getId(),
                'type' => 'copiedReport',
            ];
        }
        // User Reports.
        $otherUsers = [];
        foreach ($usersReports as $cellReport) {
            /** @var DW_Model_Report $dWReport */
            $cellReports[] = [
                'label' => $cellReport->getCellDWReport()->getLabel(),
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
        $viewConfiguration->setComplementaryPageTitle(' <small>'.$cell->getExtendedLabel().'</small>');
        $viewConfiguration->setOutputUrl('orga/cell/view/idCell/'.$fromIdCell.'/');
        $viewConfiguration->setSaveURL('orga/cell/view-report/idCell/'.$fromIdCell);
        $viewConfiguration->setCanBeUpdated($reportCanBeUpdated);
        $viewConfiguration->setCanBeSavedAs(true);

        if ($this->hasParam('idReport')) {
            $this->forward('details', 'report', 'dw',
                [
                    'viewConfiguration' => $viewConfiguration
                ]
            );
        } else {
            $this->forward('details', 'report', 'dw',
                [
                    'idCube' => $cell->getDWCube()->getId(),
                    'viewConfiguration' => $viewConfiguration
                ]
            );
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

        $displayOrgaExport = $this->aclService->isAllowed(
            $connectedUser,
            Action::EDIT(),
            $cell
        );
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
            $displayACLExport = $this->aclService->isAllowed(
                $connectedUser,
                Action::ALLOW(),
                $cell
            );;
        } else {
            foreach ($cell->getGranularity()->getNarrowerGranularities() as $narrowerGranularity) {
                if ($narrowerGranularity->getCellsWithACL()) {
                    $displayACLExport = $this->aclService->isAllowed(
                        $connectedUser,
                        Action::ALLOW(),
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
            $displayInputsExport = $this->aclService->isAllowed(
                $connectedUser,
                CellAction::VIEW_REPORTS(),
                $cell
            );
        } else {
            foreach ($cell->getGranularity()->getNarrowerGranularities() as $narrowerGranularity) {
                if ($narrowerGranularity->getInputConfigGranularity() !== null) {
                    $displayInputsExport = $this->aclService->isAllowed(
                        $connectedUser,
                        CellAction::VIEW_REPORTS(),
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
            $displayOutputsExport = $this->aclService->isAllowed(
                $connectedUser,
                CellAction::VIEW_REPORTS(),
                $cell
            );
        } else {
            foreach ($cell->getGranularity()->getNarrowerGranularities() as $narrowerGranularity) {
                if ($narrowerGranularity->getInputConfigGranularity() !== null) {
                    $displayOutputsExport = $this->aclService->isAllowed(
                        $connectedUser,
                        CellAction::VIEW_REPORTS(),
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
        $filename = $date.'_'.$baseFilename.'.'.$format;

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
     * @Secure("editCell")
     */
    public function editInventoryStatusAction()
    {
        $idCell = $this->getParam('idCell');
        /** @var Orga_Model_Cell $cell */
        $cell = Orga_Model_Cell::load($idCell);

        $inventoryStatus = $this->getParam('inventoryStatus');

        $cell->setInventoryStatus($inventoryStatus);

        $this->sendJsonResponse(
            [
                'status' => $inventoryStatus,
                'label' => $this->cellVMFactory->inventoryStatusList[$inventoryStatus],
                'mainActionStatus' => ($inventoryStatus === Orga_Model_Cell::STATUS_ACTIVE) ? Orga_Model_Cell::STATUS_CLOSED : Orga_Model_Cell::STATUS_ACTIVE,
                'mainActionLabel' => ($inventoryStatus === Orga_Model_Cell::STATUS_NOTLAUNCHED) ? ___('Orga', 'view', 'inventoryNotLaunchedMainAction') : (($inventoryStatus == Orga_Model_Cell::STATUS_ACTIVE) ? ___('Orga', 'view', 'inventoryActiveMainAction') : ___('Orga', 'view', 'inventoryClosedMainAction')),
                'otherActionStatus' => ($inventoryStatus === Orga_Model_Cell::STATUS_NOTLAUNCHED) ? Orga_Model_Cell::STATUS_CLOSED : Orga_Model_Cell::STATUS_NOTLAUNCHED,
                'otherActionLabel' => ($inventoryStatus === Orga_Model_Cell::STATUS_NOTLAUNCHED) ? ___('Orga', 'view', 'inventoryNotLaunchedOtherAction') : (($inventoryStatus == Orga_Model_Cell::STATUS_ACTIVE) ? ___('Orga', 'view', 'inventoryActiveOtherAction') : ___('Orga', 'view', 'inventoryClosedOtherAction')),
            ]
        );
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
                $users[] = $adminRole->getUser()->getEmail();
            }
            foreach ($inventoryCell->getManagerRoles() as $managerRole) {
                $users[] = $managerRole->getUser()->getEmail();
            }
            foreach ($inventoryCell->getContributorRoles() as $contributorRole) {
                $users[] = $contributorRole->getUser()->getEmail();
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

        $isUserAllowedToInputCell = $this->aclService->isAllowed(
            $this->_helper->auth(),
            CellAction::INPUT(),
            $cell
        );

        $aFViewConfiguration = new AFViewConfiguration();
        if ($isUserAllowedToInputCell && ($cell->getInventoryStatus() !== Orga_Model_Cell::STATUS_CLOSED)) {
            $aFViewConfiguration->setMode(AFViewConfiguration::MODE_WRITE);
        } else {
            $aFViewConfiguration->setMode(AFViewConfiguration::MODE_READ);
        }
        $aFViewConfiguration->setPageTitle(__('UI', 'name', 'input').' <small>'.$cell->getLabel().'</small>');
        $aFViewConfiguration->addToActionStack('input-save', 'cell', 'orga', ['idCell' => $idCell]);
        $aFViewConfiguration->setResultsPreviewUrl('orga/cell/input-preview');
        $aFViewConfiguration->setExitUrl('orga/cell/view/idCell/' . $fromIdCell . '/');
        $aFViewConfiguration->addUrlParam('idCell', $idCell);
        $aFViewConfiguration->setDisplayConfigurationLink(false);
        $aFViewConfiguration->addBaseTab(AFViewConfiguration::TAB_INPUT);
        if ($cell->getAFInputSetPrimary() !== null) {
            $aFViewConfiguration->setIdInputSet($cell->getAFInputSetPrimary()->getId());
        }

        $tabComments = new UI_Tab('inputComments');
        $tabComments->label = __('Social', 'comment', 'comments');
        $tabComments->dataSource = 'orga/cell/input-comments/idCell/'.$idCell;
        $tabComments->useCache = true;
        $aFViewConfiguration->addTab($tabComments);

        $tabDocs = new UI_Tab('inputDocs');
        $tabDocs->label = __('Doc', 'name', 'documents');
        $tabDocs->dataSource = 'orga/cell/input-docs/idCell/'.$idCell;
        $tabDocs->useCache = true;
        $aFViewConfiguration->addTab($tabDocs);

        $isUserAllowedToViewCellReports = $this->aclService->isAllowed(
            $this->_helper->auth(),
            CellAction::VIEW_REPORTS(),
            $cell
        );
        if ($isUserAllowedToViewCellReports) {
            $aFViewConfiguration->addBaseTab(AFViewConfiguration::TAB_RESULT);
            $aFViewConfiguration->addBaseTab(AFViewConfiguration::TAB_CALCULATION_DETAILS);
        }
        $aFViewConfiguration->setResultsPreview($isUserAllowedToViewCellReports);

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
        /** @var Orga_Model_Cell $cell */
        $cell = Orga_Model_Cell::load($idCell);

        /** @var $af AF */
        $af = AF::load($idCell);

        // Form data
        $formContent = json_decode($this->getParam($af->getRef()), true);
        $errorMessages = [];

        // Remplit l'InputSet
        $inputSet = $this->inputFormParser->parseForm($formContent, $af, $errorMessages);
        $this->inputService->updateResults($cell, $inputSet);

        $this->addFormErrors($errorMessages);

        /** @noinspection PhpUndefinedFieldInspection */
        $this->view->inputSet = $inputSet;

        // Moche mais sinon je me petit-suicide
        $this->view->addScriptPath(APPLICATION_PATH . '/af/views/scripts/');
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
        /** @var $newInputSet \AF\Domain\InputSet\PrimaryInputSet */
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
    public function inputCommentsAction()
    {
        /** @var User $connectedUser */
        $connectedUser = $this->_helper->auth();

        $idCell = $this->getParam('idCell');
        /** @var Orga_Model_Cell $cell */
        $cell = Orga_Model_Cell::load($idCell);

        $this->view->assign('idCell', $idCell);

        $this->view->assign('idCell', $idCell);
        $this->view->assign('comments', $cell->getSocialCommentsForInputSetPrimary());
        $this->view->assign('currentUser', $connectedUser);
        $this->view->assign(
            'isUserAbleToComment',
            $this->aclService->isAllowed($connectedUser, CellAction::INPUT(), $cell)
        );

        // Désactivation du layout.
        $this->_helper->layout()->disableLayout();
    }

    /**
     * @Secure("inputCell")
     */
    public function inputCommentAddAction()
    {
        /** @var User $connectedUser */
        $connectedUser = $this->_helper->auth();

        $idCell = $this->getParam('idCell');
        /** @var Orga_Model_Cell $cell */
        $cell = Orga_Model_Cell::load($idCell);

        $this->view->assign('idCell', $idCell);

        $formData = $this->getFormData('addCommentForm');

        $content = $formData->getValue('addContent');
        if (empty($content)) {
            $this->addFormError('addContent', __('UI', 'formValidation', 'emptyRequiredField'));
        }
        if (!$this->hasFormError()) {
            // Ajoute le commentaire
            $comment = $this->commentService->addComment($connectedUser, $content);
            $cell->addSocialCommentForInputSetPrimary($comment);
            $cell->save();
            $this->entityManager->flush();

            // Retourne la vue du commentaire
            $this->forward('comment-added', 'comment', 'social',
                [
                    'comment' => $comment,
                    'currentUser' => $connectedUser
                ]
            );
            return;
        }

        // Désactivation du layout.
        $this->_helper->layout()->disableLayout();

        $this->sendFormResponse();
    }

    /**
     * @Secure("deleteComment")
     */
    public function inputCommentDeleteAction()
    {
        $idCell = $this->getParam('idCell');
        /** @var Orga_Model_Cell $cell */
        $cell = Orga_Model_Cell::load($idCell);

        $comment = Social_Model_Comment::load($this->getParam('id'));

        $cell->removeSocialCommentForInputSetPrimary($comment);
        $this->commentService->deleteComment($comment->getId());

        $this->sendFormResponse();
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
