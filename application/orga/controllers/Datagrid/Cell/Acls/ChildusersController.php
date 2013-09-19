<?php
/**
 * @author valentin.claras
 * @package Orga
 * @subpackage Controller
 */

use Core\Annotation\Secure;

/**
 * Controlleur du Datagrid listant les utilisateurs d'un ensemble de cellules.
 * @author valentin.claras
 * @package Orga
 * @subpackage Controller
 */
class Orga_Datagrid_Cell_Acls_ChildusersController extends UI_Controller_Datagrid
{
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
        $this->request->setCustomParameters($this->request->filter->getConditions());
        $this->request->filter->setConditions(array());

        $idCell = $this->getParam('idCell');
        $cell = Orga_Model_Cell::load($idCell);
        $granularity = Orga_Model_Granularity::load($this->getParam('idGranularity'));

        $this->request->order->addOrder(Orga_Model_Cell::QUERY_MEMBERS_HASHKEY);
        foreach ($cell->loadChildCellsForGranularity($granularity, $this->request) as $childCell) {
            $childCellResource = User_Model_Resource_Entity::loadByEntity($childCell);

            $data = array();
            foreach ($childCell->getMembers() as $member) {
                $data[$member->getAxis()->getRef()] = $member->getRef();
            }

            foreach ($childCellResource->getLinkedSecurityIdentities() as $linkedIdentity) {
                if ($linkedIdentity instanceof User_Model_Role) {
                    foreach ($linkedIdentity->getUsers() as $user) {
                        $data['index'] = $linkedIdentity->getRef().'#'.$user->getId();
                        $data['userFirstName'] = $user->getFirstName();
                        $data['userLastName'] = $user->getLastName();
                        $data['userEmail'] = $user->getEmail();
                        $data['userRole'] = $linkedIdentity->getRef();
                        $this->addLine($data);
                    }
                }
            }
        }

        $totalElement = 0;
        foreach ($cell->loadChildCellsForGranularity($granularity, $this->request) as $childCell) {
            $childCellResource = User_Model_Resource_Entity::loadByEntity($childCell);
            foreach ($childCellResource->getLinkedSecurityIdentities() as $linkedIdentity) {
                if ($linkedIdentity instanceof User_Model_Role) {
                    $totalElement += count($linkedIdentity->getUsers());
                }
            }
        }
        $this->totalElements = $totalElement;

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
        $granularity = Orga_Model_Granularity::load($this->getParam('idGranularity'));
        $members = [];
        foreach ($cell->getMembers() as $member) {
            if ($granularity->hasAxis($member->getAxis())) {
                $members[] = $member;
            }
        }
        foreach ($granularity->getAxes() as $axis) {
            if (isset($this->_add[$this->id.'_'.$axis->getRef().'_addForm'])) {
                $members[] = $axis->getMemberByCompleteRef($this->getAddElementValue($axis->getRef()).'#');
            }
        }
        $granularityCell = $granularity->getCellByMembers($members);

        // Validation
        $userEmail = $this->getAddElementValue('userEmail');
        if (empty($userEmail)) {
            $this->setAddElementErrorMessage('userEmail', __('UI', 'formValidation', 'emptyRequiredField'));
        }
        $userRoleRef = $this->getAddElementValue('userRole');
        if (empty($userRoleRef)) {
            $this->setAddElementErrorMessage('userRole', __('UI', 'formValidation', 'emptyRequiredField'));
        } else {
            $baseUserRoleRef = explode('_', $userRoleRef)[0];
            $role = User_Model_Role::loadByRef($baseUserRoleRef.'_'.$granularityCell->getId());
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
                $userEmail
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