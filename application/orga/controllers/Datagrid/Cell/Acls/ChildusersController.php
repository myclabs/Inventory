<?php
/**
 * @author valentin.claras
 * @package Orga
 * @subpackage Controller
 */

use Core\Annotation\Secure;
use Core\Work\ServiceCall\ServiceCallTask;
use User\Domain\ACL\Role;
use User\Domain\User;

/**
 * Controlleur du Datagrid listant les utilisateurs d'un ensemble de cellules.
 * @author valentin.claras
 * @package Orga
 * @subpackage Controller
 */
class Orga_Datagrid_Cell_Acls_ChildusersController extends UI_Controller_Datagrid
{
    /**
     * @Secure("allowCell")
     */
    public function getelementsAction()
    {
        $this->request->setCustomParameters($this->request->filter->getConditions());
        $this->request->filter->setConditions([]);

        $idCell = $this->getParam('idCell');
        $cell = Orga_Model_Cell::load($idCell);
        $granularity = Orga_Model_Granularity::load($this->getParam('idGranularity'));

        $this->request->order->addOrder(Orga_Model_Cell::QUERY_MEMBERS_HASHKEY);
        $childCells = $cell->loadChildCellsForGranularity($granularity, $this->request);
        foreach ($childCells as $childCell) {
            $data = [];
            foreach ($childCell->getMembers() as $member) {
                $data[$member->getAxis()->getRef()] = $member->getRef();
            }

            foreach ($cell->getAllRoles() as $role) {
                $data['index'] = $role->getId();
                $data['userFirstName'] = $role->getUser()->getFirstName();
                $data['userLastName'] = $role->getUser()->getLastName();
                $data['userEmail'] = $role->getUser()->getEmail();
                $data['userRole'] = $role->getLabel();
                $this->addLine($data);
            }
        }

        $totalElement = 0;
        foreach ($childCells as $childCell) {
            $totalElement += count($childCell->getAllRoles());
        }
        $this->totalElements = $totalElement;

        $this->send();
    }

    /**
     * @Secure("allowCell")
     */
    public function addelementAction()
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
            $role = Role::loadByRef($baseUserRoleRef.'_'.$granularityCell->getId());
        }
        if (!empty($this->_addErrorMessages)) {
            $this->send();
            return;
        }

        if (User::isEmailUsed($userEmail)) {
            $user = User::loadByEmail($userEmail);
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
            new ServiceCallTask(
                'Orga_Service_ACLManager',
                'addCellUser',
                [$cell, $user, $role, false],
                __('Orga', 'backgroundTasks', 'addRoleToUser', ['ROLE' => $role->getLabel(), 'USER' => $user->getEmail()])
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
    public function deleteelementAction()
    {
        $role = Role::load($this->delete);
        $user = $role->getUser();
        $cell = Orga_Model_Cell::load($this->getParam('idCell'));

        $this->workDispatcher->runBackground(
            new ServiceCallTask(
                'Orga_Service_ACLManager',
                'removeCellUser',
                [$cell, $user, $role, false],
                __('Orga', 'backgroundTasks', 'removeRoleFromUser', ['ROLE' => $role->getLabel(), 'USER' => $user->getEmail()])
            )
        );

        $this->message = __('UI', 'message', 'deletedLater');
        $this->send();
    }
}
