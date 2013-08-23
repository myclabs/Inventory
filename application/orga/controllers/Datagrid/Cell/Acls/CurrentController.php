<?php
/**
 * @author valentin.claras
 * @package Orga
 * @subpackage Controller
 */

use Core\Annotation\Secure;
use DI\Annotation\Inject;

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
     * @var User_Service_User
     */
    private $userService;

    /**
     * @Inject
     * @var Core_Work_Dispatcher
     */
    private $workDispatcher;

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
    function getelementsAction()
    {
        $idCell = $this->getParam('idCell');
        $cellACLResource = User_Model_Resource_Entity::loadByEntity(Orga_Model_Cell::load($idCell));

        foreach ($cellACLResource->getLinkedSecurityIdentities() as $linkedIdentity) {
            if ($linkedIdentity instanceof User_Model_Role) {
                foreach ($linkedIdentity->getUsers() as $user) {
                    $data = array();
                    $data['index'] = $linkedIdentity->getRef().'#'.$user->getId();
                    $data['userFirstName'] = $user->getFirstName();
                    $data['userLastName'] = $user->getLastName();
                    $data['userEmail'] = $user->getEmail();
                    $data['userRole'] = $linkedIdentity->getRef();
                    $this->addLine($data);
                }
            }
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
    function addelementAction()
    {
        $cell = Orga_Model_Cell::load($this->getParam('idCell'));

        // Validation
        $userEmail = $this->getAddElementValue('userEmail');
        if (empty($userEmail)) {
            $this->setAddElementErrorMessage('userEmail', __('UI', 'formValidation', 'emptyRequiredField'));
        }
        $userRoleRef = $this->getAddElementValue('userRole');
        if (empty($userRoleRef)) {
            $this->setAddElementErrorMessage('userRole', __('UI', 'formValidation', 'emptyRequiredField'));
        } else {
            $role = User_Model_Role::loadByRef($userRoleRef);
        }
        if (!empty($this->_addErrorMessages)) {
            $this->send();
            return;
        }

        if (User_Model_User::isEmailUsed($userEmail)) {
            $user = User_Model_User::loadByEmail($userEmail);
            if ($user->hasRole($role)) {
                $this->setAddElementErrorMessage('userRole', __('Orga', 'role', 'userAlreadyHasRole'));
                $this->send();
                return;
            }
        } else {
            $user = $this->userService->inviteUser(
                $userEmail,
                __('Orga', 'email', 'userRoleGivenAtCreation', array(
                    'CELL' => $cell->getLabelExtended(),
                    'ROLE' => __('Orga', 'role', $role->getName()),
                ))
            );
            $user->addRole(User_Model_Role::loadByRef('user'));
        }

        $this->workDispatcher->runBackground(
            new Core_Work_ServiceCall_Task(
                'Orga_Service_ACLManager',
                'addCellUser',
                [$cell, $user, $role, false],
                __('Orga', 'backgroundTasks', 'addRoleToUser', ['ROLE' => __('Orga', 'role', $role->getName()), 'USER' => $user->getEmail()])
            )
        );

        $this->message = __('UI', 'message', 'addedLater');
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
        list($userRoleRef, $userId) = explode('#', $this->delete);
        $user = User_Model_User::load($userId);
        $role = User_Model_Role::loadByRef($userRoleRef);
        $cell = Orga_Model_Cell::load($this->getParam('idCell'));

        $this->workDispatcher->runBackground(
            new Core_Work_ServiceCall_Task(
                'Orga_Service_ACLManager',
                'removeCellUser',
                [$cell, $user, $role, false],
                __('Orga', 'backgroundTasks', 'removeRoleFromUser', ['ROLE' => __('Orga', 'role', $role->getName()), 'USER' => $user->getEmail()])
            )
        );

        $this->message = __('UI', 'message', 'deletedLater');
        $this->send();
    }

}