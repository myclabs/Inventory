<?php

use Core\Annotation\Secure;
use Core\Work\ServiceCall\ServiceCallTask;
use MyCLabs\Work\Dispatcher\SynchronousWorkDispatcher;
use Orga\Domain\Workspace;
use Orga\Domain\Service\OrgaACLManager;
use Orga\Domain\ACL\WorkspaceAdminRole;
use User\Domain\User;

/**
 * @author valentin.claras
 */
class Orga_Datagrid_Workspace_AclController extends UI_Controller_Datagrid
{
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
     * @Secure("allowWorkspace")
     */
    public function getelementsAction()
    {
        $workspaceId = $this->getParam('workspace');
        /** @var Workspace $workspace */
        $workspace = Workspace::load($workspaceId);

        foreach ($workspace->getAdminRoles() as $role) {
            /** @var User $user */
            $user = $role->getSecurityIdentity();
            $data = array();
            $data['index'] = $role->getId();
            $data['firstName'] = $user->getFirstName();
            $data['lastName'] = $user->getLastName();
            $data['email'] = $user->getEmail();
            $this->addLine($data);
        }

        $this->send();
    }

    /**
     * @Secure("allowWorkspace")
     */
    public function addelementAction()
    {
        $workspace = Workspace::load($this->getParam('workspace'));

        $userEmail = $this->getAddElementValue('email');
        if (empty($userEmail)) {
            $this->setAddElementErrorMessage('email', __('UI', 'formValidation', 'emptyRequiredField'));
            $this->send();
            return;
        } elseif (! filter_var($userEmail, FILTER_VALIDATE_EMAIL)) {
            $this->setAddElementErrorMessage('email', __('UI', 'formValidation', 'invalidEmail'));
            $this->send();
            return;
        }

        // Vérifie que l'utilisateur n'a pas déjà le role
        try {
            $user = User::loadByEmail($userEmail);
            foreach ($user->getRoles() as $role) {
                if ($role instanceof WorkspaceAdminRole && $role->getWorkspace() === $workspace) {
                    $this->setAddElementErrorMessage('email', __('Orga', 'role', 'userAlreadyHasRole'));
                    $this->send();
                    return;
                }
            }
        } catch (Core_Exception_NotFound $e) {
        }

        $success = function () {
            $this->message = __('UI', 'message', 'added');
        };
        $timeout = function () {
            $this->message = __('UI', 'message', 'addedLater');
        };
        $error = function (Exception $e) {
            throw $e;
        };

        $taskLabel = __('Orga', 'backgroundTasks', 'addRoleToUser', [
            'ROLE' => WorkspaceAdminRole::getLabel(), 'USER' => $userEmail
        ]);

        $task = new ServiceCallTask(
            OrgaACLManager::class,
            'addWorkspaceAdministrator',
            [$workspace, $userEmail, false],
            $taskLabel
        );
        $this->workDispatcher->runAndWait($task, $this->waitDelay, $success, $timeout, $error);

        $this->send();
    }

    /**
     * @Secure("allowWorkspace")
     */
    public function deleteelementAction()
    {
        $workspace = Workspace::load($this->getParam('workspace'));
        /** @var WorkspaceAdminRole $role */
        $role = $this->entityManager->find(WorkspaceAdminRole::class, $this->delete);

        $success = function () {
            $this->message = __('UI', 'message', 'deleted');
        };
        $timeout = function () {
            $this->message = __('UI', 'message', 'deletedLater');
        };
        $error = function (Exception $e) {
            throw $e;
        };

        $task = new ServiceCallTask(
            OrgaACLManager::class,
            'removeWorkspaceAdministrator',
            [$workspace, $role, false],
            __('Orga', 'backgroundTasks', 'removeRoleFromUser', [
                'ROLE' => $role->getLabel(),
                'USER' => $role->getSecurityIdentity()->getEmail(),
            ])
        );
        $this->workDispatcher->runAndWait($task, $this->waitDelay, $success, $timeout, $error);

        $this->send();
    }
}
