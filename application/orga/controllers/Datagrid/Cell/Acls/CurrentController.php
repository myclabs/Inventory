<?php
/**
 * @author valentin.claras
 * @package Orga
 * @subpackage Controller
 */

use Core\Annotation\Secure;
use Core\Work\ServiceCall\ServiceCallTask;
use MyCLabs\Work\Dispatcher\WorkDispatcher;
use User\Domain\ACL\Role;
use User\Domain\User;

/**
 * Controlleur du Datagrid listant les Roles d'une Cellule.
 * @author valentin.claras
 * @package Orga
 * @subpackage Controller
 */
class Orga_Datagrid_Cell_Acls_CurrentController extends UI_Controller_Datagrid
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
     * @Secure("allowCell")
     */
    public function getelementsAction()
    {
        $idCell = $this->getParam('idCell');
        $cell = Orga_Model_Cell::load($idCell);

        foreach ($cell->getAllRoles() as $role) {
            $data = array();
            $data['index'] = $role->getId();
            $data['userFirstName'] = $role->getUser()->getFirstName();
            $data['userLastName'] = $role->getUser()->getLastName();
            $data['userEmail'] = $role->getUser()->getEmail();
            $data['userRole'] = $role->getLabel();
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
     * @Secure("allowCell")
     */
    public function addelementAction()
    {
        $cell = Orga_Model_Cell::load($this->getParam('idCell'));

        // Validation
        $userEmail = $this->getAddElementValue('userEmail');
        if (empty($userEmail)) {
            $this->setAddElementErrorMessage('userEmail', __('UI', 'formValidation', 'emptyRequiredField'));
            $this->send();
            return;
        }
        $roleType = $this->getAddElementValue('userRole');
        if (empty($userRoleRef)) {
            $this->setAddElementErrorMessage('userRole', __('UI', 'formValidation', 'emptyRequiredField'));
            $this->send();
            return;
        }

        if (User::isEmailUsed($userEmail)) {
            $user = User::loadByEmail($userEmail);
            if ($user->hasRole($roleType)) {
                $this->setAddElementErrorMessage('userRole', __('Orga', 'role', 'userAlreadyHasRole'));
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

        // TODO
        $roleLabel = '';

        $task = new ServiceCallTask(
            Orga_Service_ACLManager::class,
            'addCellRole',
            [$cell, $userEmail, $roleType, false],
            __('Orga', 'backgroundTasks', 'addRoleToUser', ['ROLE' => $roleLabel, 'USER' => $userEmail])
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
    public function deleteelementAction()
    {
        $role = Role::load($this->delete);
        $user = $role->getUser();
        $cell = Orga_Model_Cell::load($this->getParam('idCell'));

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
            'removeCellUser',
            [$cell, $user, $role, false],
            __(
                'Orga',
                'backgroundTasks',
                'removeRoleFromUser',
                ['ROLE' => __('Orga', 'role', $role->getLabel()), 'USER' => $user->getEmail()]
            )
        );
        $this->workDispatcher->runBackground($task, $this->waitDelay, $success, $timeout, $error);

        $this->send();
    }
}