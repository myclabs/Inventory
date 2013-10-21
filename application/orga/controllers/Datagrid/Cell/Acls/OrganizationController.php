<?php
/**
 * @author valentin.claras
 * @package Orga
 * @subpackage Controller
 */

use Core\Annotation\Secure;
use Core\Work\ServiceCall\ServiceCallTask;
use DI\Annotation\Inject;
use MyCLabs\Work\Dispatcher\WorkDispatcher;

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
     * @var User_Service_User
     */
    private $userService;

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
     * Fonction renvoyant la liste des éléments peuplant la Datagrid.
     *
     * Récupération des paramètres de tris et filtres de la manière suivante :
     *  $this->request.
     *
     * Récupération des arguments de la manière suivante :
     *  $this->getParam('nomArgument').
     *
     * Renvoie la liste d'éléments, le nombre total et un message optionnel.
     *
     * @Secure("editOrganization")
     */
    function getelementsAction()
    {
        $idOrganization = $this->getParam('idOrganization');
        $organizationAdministratorRole = User_Model_Role::loadByRef('organizationAdministrator_'.$idOrganization);

        foreach ($organizationAdministratorRole->getUsers() as $user) {
            $data = array();
            $data['index'] = $user->getId();
            $data['userFirstName'] = $user->getFirstName();
            $data['userLastName'] = $user->getLastName();
            $data['userEmail'] = $user->getEmail();
            $this->addLine($data);
        }

        $this->send();
    }

    /**
     * Fonction ajoutant un élément.
     *
     * Renvoie un message d'information.
     *
     * @see getAddElementValue
     * @see setAddElementErrorMessage
     * @Secure("editOrganization")
     */
    function addelementAction()
    {
        $idOrganization = $this->getParam('idOrganization');
        $role = User_Model_Role::loadByRef('organizationAdministrator_'.$idOrganization);
        $organization = Orga_Model_Organization::load($idOrganization);

        $userEmail = $this->getAddElementValue('userEmail');
        if (empty($userEmail)) {
            $this->setAddElementErrorMessage('userEmail', __('UI', 'formValidation', 'emptyRequiredField'));
            $this->send();
            return;
        }

        if (User_Model_User::isEmailUsed($userEmail)) {
            $user = User_Model_User::loadByEmail($userEmail);
            if ($user->hasRole($role)) {
                $this->setAddElementErrorMessage('userEmail', __('Orga', 'role', 'userAlreadyHasRole'));
                $this->send();
                return;
            }
        } else {
            $user = $this->userService->inviteUser(
                $userEmail
            );
            $user->addRole(User_Model_Role::loadByRef('user'));
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

        $task = new ServiceCallTask(
            'Orga_Service_ACLManager',
            'addOrganizationAdministrator',
            [$organization, $user, false],
            __('Orga', 'backgroundTasks', 'addRoleToUser', ['ROLE' => __('Orga', 'role', $role->getName()), 'USER' => $user->getEmail()])
        );
        $this->workDispatcher->runBackground($task, $this->waitDelay, $success, $timeout, $error);

        $this->send();
    }

    /**
     * Fonction supprimant un élément.
     *
     * Récupération de la ligne à supprimer de la manière suivante :
     *  $this->delete.
     *
     * Récupération des arguments de la manière suivante :
     *  $this->getParam('nomArgument').
     *
     * Renvoie un message d'information.
     * @Secure("allowCell")
     */
    function deleteelementAction()
    {
        $idOrganization = $this->getParam('idOrganization');
        $organization = Orga_Model_Organization::load($idOrganization);
        $user = User_Model_User::load($this->delete);
        $role = User_Model_Role::loadByRef('organizationAdministrator_'.$idOrganization);

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
            'Orga_Service_ACLManager',
            'removeOrganizationAdministrator',
            [$organization, $user, false],
            __('Orga', 'backgroundTasks', 'removeRoleFromUser', ['ROLE' => __('Orga', 'role', $role->getName()), 'USER' => $user->getEmail()])
        );
        $this->workDispatcher->runBackground($task, $this->waitDelay, $success, $timeout, $error);

        $this->send();
    }

}