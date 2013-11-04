<?php
/**
 * @author valentin.claras
 * @package Orga
 * @subpackage Controller
 */

use Core\Annotation\Secure;
use Core\Work\ServiceCall\ServiceCallTask;
use MyCLabs\Work\Dispatcher\WorkDispatcher;
use Orga\Model\ACL\Role\OrganizationAdminRole;
use User\Domain\ACL\Role;
use User\Domain\User;

/**
 * Controlleur du Datagrid listant les Roles du projet d'une cellule.
 * @author valentin.claras
 * @package Orga
 * @subpackage Controller
 */
class Orga_Datagrid_Cell_Acls_OrganizationController extends UI_Controller_Datagrid
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
     * @Secure("editOrganization")
     */
    public function getelementsAction()
    {
        $organization = Orga_Model_Organization::load($this->getParam('idOrganization'));

        foreach ($organization->getAdminRoles() as $role) {
            $user = $role->getUser();
            $data = array();
            $data['index'] = $role->getId();
            $data['userFirstName'] = $user->getFirstName();
            $data['userLastName'] = $user->getLastName();
            $data['userEmail'] = $user->getEmail();
            $this->addLine($data);
        }

        $this->send();
    }

    /**
     * @Secure("editOrganization")
     */
    public function addelementAction()
    {
        $organization = Orga_Model_Organization::load($this->getParam('idOrganization'));

        $userEmail = $this->getAddElementValue('userEmail');
        if (empty($userEmail)) {
            $this->setAddElementErrorMessage('userEmail', __('UI', 'formValidation', 'emptyRequiredField'));
            $this->send();
            return;
        }

        if (User::isEmailUsed($userEmail)) {
            $user = User::loadByEmail($userEmail);
            if ($user->hasRole(OrganizationAdminRole::class)) {
                $this->setAddElementErrorMessage('userEmail', __('Orga', 'role', 'userAlreadyHasRole'));
                $this->send();
                return;
            }
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
     * @Secure("allowCell")
     */
    public function deleteelementAction()
    {
        $organization = Orga_Model_Organization::load($this->getParam('idOrganization'));
        $role = Role::load($this->delete);

        //@see http://supervision.myc-sense.com:3000/issues/6582
        //  Sans worker la suppression s'effectue correctement mais échoue avec.

        // sans worker.
//        $user->removeRole(Role::loadByRef('organizationAdministrator_'.$organization->getId()));
//
//        $globalCell = Orga_Model_Granularity::loadByRefAndOrganization('global', $organization)->getCellByMembers([]);
//        $user->removeRole(
//            Role::loadByRef('cellAdministrator_'.$globalCell->getId())
//        );

        // worker.
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
                ['ROLE' => __('Orga', 'role', $role->getLabel()), 'USER' => $role->getUser()->getEmail()]
            )
        );
        $this->workDispatcher->runBackground($task, $this->waitDelay, $success, $timeout, $error);

        $this->send();
    }
}
