<?php

use Core\Annotation\Secure;
use Core\Work\ServiceCall\ServiceCallTask;
use MyCLabs\Work\Dispatcher\WorkDispatcher;
use Orga\Model\ACL\Role\OrganizationAdminRole;
use User\Domain\ACL\Role\Role;
use User\Domain\User;

/**
 * @author valentin.claras
 */
class Orga_Datagrid_Organization_AclController extends UI_Controller_Datagrid
{
    /**
     * @Inject
     * @var WorkDispatcher
     */
    private $workDispatcher;

    /**
     * @Inject("work.waitDelay")
     * @var int
     */
    private $waitDelay;

    /**
     * @Secure("allowOrganization")
     */
    public function getelementsAction()
    {
        $idOrganization = $this->getParam('idOrganization');
        /** @var Orga_Model_Organization $organization */
        $organization = Orga_Model_Organization::load($idOrganization);

        foreach ($organization->getAdminRoles() as $role) {
            $user = $role->getUser();
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
     * @Secure("allowOrganization")
     */
    public function addelementAction()
    {
        $organization = Orga_Model_Organization::load($this->getParam('idOrganization'));

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
                if ($role instanceof OrganizationAdminRole && $role->getOrganization() === $organization) {
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
            'ROLE' => OrganizationAdminRole::getLabel(), 'USER' => $userEmail
        ]);

        $task = new ServiceCallTask(
            Orga_Service_ACLManager::class,
            'addOrganizationAdministrator',
            [$organization, $userEmail, false],
            $taskLabel
        );
        $this->workDispatcher->runBackground($task, $this->waitDelay, $success, $timeout, $error);

        $this->send();
    }

    /**
     * @Secure("allowOrganization")
     */
    public function deleteelementAction()
    {
        $organization = Orga_Model_Organization::load($this->getParam('idOrganization'));
        $role = Role::load($this->delete);

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
            Orga_Service_ACLManager::class,
            'removeOrganizationAdministrator',
            [$organization, $role, false],
            __(
                'Orga',
                'backgroundTasks',
                'removeRoleFromUser',
                ['ROLE' => $role->getLabel(), 'USER' => $role->getUser()->getEmail()]
            )
        );
        $this->workDispatcher->runBackground($task, $this->waitDelay, $success, $timeout, $error);

        $this->send();
    }
}
