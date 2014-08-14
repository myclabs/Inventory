<?php

use Core\Annotation\Secure;
use DI\Annotation\Inject;
use MyCLabs\ACL\ACL;
use MyCLabs\Work\Dispatcher\SynchronousWorkDispatcher;
use Orga\Application\Service\OrgaUserAccessManager;
use Orga\Application\Service\Workspace\WorkspaceService;
use Orga\Domain\Cell;
use Orga\Domain\Member;
use Orga\Domain\Workspace;
use User\Domain\ACL\Actions;
use Core\Work\ServiceCall\ServiceCallTask;
use Orga\Domain\ACL\CellAdminRole;
use User\Domain\User;

/**
 * @author valentin.claras
 */
class Orga_Datagrid_MemberController extends UI_Controller_Datagrid
{
    /**
     * @Inject
     * @var ACL
     */
    private $acl;

    /**
     * @Inject
     * @var OrgaUserAccessManager
     */
    private $orgaUserAccessManager;

    /**
     * @Inject
     * @var SynchronousWorkDispatcher
     */
    private $workDispatcher;

    /**
     * @Inject("work.waitDelay")
     * @var int
     */
    private $waitDelay;

    /**
     * @Secure("editWorkspaceAndCells")
     */
    public function getelementsAction()
    {
        /** @var User $connectedUser */
        $connectedUser = $this->_helper->auth();

        $workspaceId = $this->getParam('workspace');
        /** @var Workspace $workspace */
        $workspace = Workspace::load($workspaceId);
        $axis = $workspace->getAxisByRef($this->getParam('axis'));

        $isUserAllowedToEditWorkspace = $this->acl->isAllowed(
            $connectedUser,
            Actions::EDIT,
            $workspace
        );
        $isUserAllowToEditAllMembers = $isUserAllowedToEditWorkspace || $this->acl->isAllowed(
            $connectedUser,
            Actions::EDIT,
            $workspace->getGranularityByRef('global')->getCellByMembers([])
        );

        if (!$isUserAllowToEditAllMembers) {
            $members = [];
            /** @var Cell[] $topCellsWithEditAccess */
            $topCellsWithEditAccess = $this->orgaUserAccessManager->getTopCellsWithAccessForWorkspace(
                $connectedUser,
                $workspace,
                [CellAdminRole::class]
            )['cells'];
            foreach ($topCellsWithEditAccess as $cell) {
                if (!$axis->isTransverse($cell->getGranularity()->getAxes())) {
                    foreach ($cell->getMembers() as $cellMember) {
                        if ($axis->isBroaderThan($cellMember->getAxis())) {
                            continue 2;
                        }
                    }
                    $members = array_merge($members, $cell->getChildMembersForAxes([$axis])[$axis->getRef()]);
                }
            }
            $members = array_unique($members);
            usort($members, [Member::class, 'orderMembers']);
        } else {
            $this->request->filter->addCondition(Member::QUERY_AXIS, $axis);
            if ($axis->isMemberPositioning()) {
                $this->request->order->addOrder(Member::QUERY_POSITION);
            } else {
                $this->request->order->addTranslatedOrder(Member::QUERY_LABEL);
            }
            /** @var Member[] $members */
            $members = Member::loadList($this->request);
        }

        foreach ($members as $member) {
            $data = [];
            $data['index'] = $member->getId();
            $data['label'] = $this->cellTranslatedText($member->getLabel());
            $data['ref'] = $this->cellText($member->getRef());
            foreach ($member->getDirectParents() as $directParentMember) {
                $data['broader'.$directParentMember->getAxis()->getRef()] = $this->cellList(
                    $directParentMember->getCompleteRef(),
                    $this->translator->get($directParentMember->getLabel())
                );
            }
            $memberPosition = $member->getPosition();
            $data['position'] = $this->cellPosition(
                $memberPosition,
                ($memberPosition > 1),
                ($memberPosition < $member->getLastEligiblePosition())
            );
            $this->addLine($data);
        }

        if ($isUserAllowToEditAllMembers) {
            $this->totalElements = Member::countTotal($this->request);
        }

        $this->send();
    }


    /**
     * Ajoute un nouvel element.
     * @Secure("editWorkspaceAndCells")
     */
    public function addelementAction()
    {
        $workspaceId = $this->getParam('workspace');
        /** @var Workspace $workspace */
        $workspace = Workspace::load($workspaceId);
        $axis = $workspace->getAxisByRef($this->getParam('axis'));

        $label = $this->getAddElementValue('label');
        $ref = $this->getAddElementValue('ref');

        try {
            Core_Tools::checkRef($ref);
        } catch (Core_Exception_User $e) {
            $this->setAddElementErrorMessage('ref', $e->getMessage());
        }

        $parentMembers = [];
        $contextualizingMembers = [];
        foreach ($axis->getDirectBroaders() as $directBroaderAxis) {
            $formFieldRef = 'broader'.$directBroaderAxis->getRef();
            $broaderMemberRef = $this->getAddElementValue($formFieldRef);
            if (empty($broaderMemberRef)) {
                $this->setAddElementErrorMessage($formFieldRef, __('Core', 'exception', 'emptyRequiredField'));
            } else {
                try {
                    $broaderMember = $directBroaderAxis->getMemberByCompleteRef($broaderMemberRef);
                    $parentMembers[] = $broaderMember;
                } catch (Core_Exception_NotFound $e) {
                    $this->setAddElementErrorMessage($formFieldRef, __('Core', 'exception', 'applicationError'));
                    continue;
                }
                if ($broaderMember->getAxis()->isContextualizing()) {
                    $contextualizingMembers[] = $broaderMember;
                }
                $contextualizingMembers = array_merge(
                    $contextualizingMembers,
                    $broaderMember->getContextualizingParents()
                );
            }
        }

        if (empty($this->_addErrorMessages)) {
            try {
                $axis->getMemberByCompleteRef($ref . '#' . Member::buildParentMembersHashKey($contextualizingMembers));
                $this->setAddElementErrorMessage('ref', __('UI', 'formValidation', 'alreadyUsedIdentifier'));
            } catch (Core_Exception_NotFound $e) {
                $success = function () {
                    $this->message = __('UI', 'message', 'added');
                };
                $timeout = function () {
                    $this->message = __('UI', 'message', 'addedLater');
                };
                $error = function (Exception $e) {
                    throw $e;
                };

                // Lance la tache en arrière plan
                $task = new ServiceCallTask(
                    WorkspaceService::class,
                    'addMember',
                    [$axis, $ref, $label, $parentMembers],
                    __('Orga', 'backgroundTasks', 'addMember', [
                        'MEMBER' => $label,
                        'AXIS' => $this->translator->get($axis->getLabel()),
                    ])
                );
                $this->workDispatcher->runAndWait($task, $this->waitDelay, $success, $timeout, $error);
            }
        }

        $this->send();
    }

    /**
     * Supprime un element.
     * @Secure("editWorkspaceAndCells")
     */
    public function deleteelementAction()
    {
        $member = Member::load($this->delete);

        if ($member->hasDirectChildren()) {
            throw new Core_Exception_User('Orga', 'member', 'memberHasChild');
        }

        foreach ($member->getCells() as $memberCell) {
            if (count($memberCell->getAllRoles()) > 0) {
                throw new Core_Exception_User('Orga', 'member', 'deleteMemberWithUsersToCells');
            }
        }

        $success = function () {
            $this->message = __('UI', 'message', 'deleted');
        };
        $timeout = function () {
            $this->message = __('UI', 'message', 'deletedLater');
        };
        $error = function (Exception $e) {
            throw $e;
        };

        // Lance la tache en arrière plan
        $task = new ServiceCallTask(
            WorkspaceService::class,
            'deleteMember',
            [$member],
            __('Orga', 'backgroundTasks', 'deleteMember', [
                'MEMBER' => $this->translator->get($member->getLabel()),
                'AXIS' => $this->translator->get($member->getAxis()->getLabel()),
            ])
        );
        $this->workDispatcher->runAndWait($task, $this->waitDelay, $success, $timeout, $error);

        $this->send();
    }

    /**
     * Modifie les valeurs d'un element.
     * @Secure("editWorkspaceAndCells")
     */
    public function updateelementAction()
    {
        $workspaceId = $this->getParam('workspace');
        /** @var Workspace $workspace */
        $workspace = Workspace::load($workspaceId);

        /** @var Member $member */
        $member = Member::load($this->update['index']);

        $changes = [];
        switch ($this->update['column']) {
            case 'label':
                $changes['label'] = $this->update['value'];
                break;
            case 'ref':
                Core_Tools::checkRef($this->update['value']);
                try {
                    $axis = $workspace->getAxisByRef($this->getParam('axis'));
                    $completeRef = Member::buildParentMembersHashKey($member->getContextualizingParents());
                    $completeRef = $this->update['value'] . '#' . $completeRef;
                    if ($axis->getMemberByCompleteRef($completeRef) !== $member) {
                        throw new Core_Exception_User('UI', 'formValidation', 'alreadyUsedIdentifier');
                    }
                } catch (Core_Exception_NotFound $e) {
                    $changes['ref'] = $this->update['value'];
                }
                break;
            case 'position':
                $changes['position'] = $this->update['value'];
                break;
            default:
                $changes['parents'] = [ substr($this->update['column'], 7) => $this->update['value']];
                break;
        }

        $success = function () {
            $this->message = __('UI', 'message', 'updated');
        };
        $timeout = function () {
            $this->message = __('UI', 'message', 'updatedLater');
        };
        $error = function (Exception $e) {
            throw $e;
        };
        // Lance la tache en arrière plan
        $task = new ServiceCallTask(
            WorkspaceService::class,
            'editMember',
            [$member, $changes],
            __('Orga', 'backgroundTasks', 'updateMember', [
                    'MEMBER' => $this->translator->get($member->getExtendedLabel()),
                ])
        );
        $this->workDispatcher->runAndWait($task, $this->waitDelay, $success, $timeout, $error);

        $this->data = $this->update['value'];
        $this->send();
    }

    /**
     * Renvoie la liste des parents éligibles pour un membre.
     * @Secure("editWorkspaceAndCells")
     */
    public function getParentsAction()
    {
        /** @var User $connectedUser */
        $connectedUser = $this->_helper->auth();

        $workspaceId = $this->getParam('workspace');
        /** @var Workspace $workspace */
        $workspace = Workspace::load($workspaceId);
        $broaderAxis = $workspace->getAxisByRef($this->getParam('parentAxis'));

        $isUserAllowedToEditWorkspace = $this->acl->isAllowed(
            $connectedUser,
            Actions::EDIT,
            $workspace
        );
        $isUserAllowedToEditGlobalCell = $isUserAllowedToEditWorkspace
            || $this->acl->isAllowed(
                $connectedUser,
                Actions::EDIT,
                $workspace->getGranularityByRef('global')->getCellByMembers([])
            );
        $isUserAllowToEditAllMembers = $isUserAllowedToEditWorkspace || $isUserAllowedToEditGlobalCell;

        if (!$isUserAllowToEditAllMembers) {
            /** @var Member[] $members */
            $members = [];
            /** @var Cell[] $topCellsWithEditAccess */
            $topCellsWithEditAccess = $this->orgaUserAccessManager->getTopCellsWithAccessForWorkspace(
                $connectedUser,
                $workspace,
                [CellAdminRole::class]
            )['cells'];
            $isTransverseToAll = true;
            foreach ($topCellsWithEditAccess as $cell) {
                if (!$broaderAxis->isTransverse($cell->getGranularity()->getAxes())) {
                    $isTransverseToAll = false;
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
            if (!$isTransverseToAll) {
                $members = array_unique($members);
                usort($members, [Member::class, 'orderMembers']);
            } else {
                $members = $broaderAxis->getOrderedMembers()->toArray();
            }
        } else {
            $members = $broaderAxis->getOrderedMembers()->toArray();
        }

        $query = $this->getParam('q');
        if (!empty($query)) {
            foreach ($members as $indexMember => $member) {
                if (strpos($this->translator->get($member->getLabel()), $query) === false) {
                    unset($members[$indexMember]);
                }
            }
        }

        foreach ($members as $eligibleParentMember) {
            $this->addElementAutocompleteList(
                $eligibleParentMember->getCompleteRef(),
                $this->translator->get($eligibleParentMember->getLabel())
            );
        }

        $this->send();
    }
}
