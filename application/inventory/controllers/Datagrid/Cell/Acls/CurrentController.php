<?php
/**
 * @author valentin.claras
 * @package Inventory
 * @subpackage Controller
 */

use Core\Annotation\Secure;

/**
 * Controlleur du Datagrid listant les Roles d'une Cellule.
 * @author valentin.claras
 * @package Inventory
 * @subpackage Controller
 */
class Inventory_Datagrid_Cell_Acls_CurrentController extends UI_Controller_Datagrid
{
    /**
     * Fonction renvoyant la liste des éléments peuplant la Datagrid.
     *
     * Récupération des paramètres de tris et filtres de la manière suivante :
     *  $this->request.
     *
     * Récupération des arguments de la manière suivante :
     *  $this->_getParam('nomArgument').
     *
     * Renvoie la liste d'éléments, le nombre total et un message optionnel.
     *
     * @Secure("allowCell")
     */
    function getelementsAction()
    {
        $idCell = $this->_getParam('idCell');
        $cellDataProviderACLResource = User_Model_Resource_Entity::loadByEntity(
            Inventory_Model_CellDataProvider::loadByOrgaCell(
                Orga_Model_Cell::load($idCell)
            )
        );

        foreach ($cellDataProviderACLResource->getLinkedSecurityIdentities() as $linkedIdentity) {
            if ($linkedIdentity instanceof User_Model_Role) {
                foreach ($linkedIdentity->getUsers() as $user) {
                    $data = array();
                    $data['index'] = $linkedIdentity->getRef().'#'.$user->getKey()['id'];
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
        $orgaCell = Orga_Model_Cell::load(array('id' => $this->_getParam('idCell')));

        $userEmail = $this->getAddElementValue('userEmail');
        if (empty($userEmail)) {
            $this->setAddElementErrorMessage('userEmail', __('UI', 'formValidation', 'emptyRequiredField'));
        }

        $userRoleRef = $this->getAddElementValue('userRole');
        if (empty($userRoleRef)) {
            $this->setAddElementErrorMessage('userRole', __('UI', 'formValidation', 'emptyRequiredField'));
        } else {
            $userRole = User_Model_Role::loadByRef($userRoleRef);
        }

        if (empty($this->_addErrorMessages)) {
            if (User_Model_User::isEmailUsed($userEmail)) {
                $user = User_Model_User::loadByEmail($userEmail);
                if ($user->hasRole($userRole)) {
                    $this->setAddElementErrorMessage('userRole', __('Inventory', 'role', 'userAlreadyHasRole'));
                } else {
                    $user->addRole($userRole);
                    User_Service_User::getInstance()->sendEmail(
                        $user,
                        __('User', 'email', 'subjectAccessRightsChange'),
                        __('Inventory', 'email', 'userRoleAdded', array(
                            'CELL' => $orgaCell->getLabelExtended(),
                            'ROLE' => $userRole->getName()
                        ))
                    );
                    $this->message = __('Inventory', 'role', 'roleAddedToExistingUser');
                }
            } else {
                $user = User_Service_User::getInstance()->inviteUser(
                    $userEmail,
                    __('Inventory', 'email', 'userRoleGivenAtCreation', array(
                        'CELL' => $orgaCell->getLabelExtended(),
                        'ROLE' => $userRole->getName()
                    ))
                );
                $this->message = __('Inventory', 'role', 'userCreatedFromRessource');
                $user->addRole(User_Model_Role::loadByRef('user'));
                $user->addRole($userRole);
            }
        }

        $this->send();
    }

    /**
     * Fonction supprimant un élément.
     *
     * Récupération de la ligne à supprimer de la manière suivante :
     *  $this->delete.
     *
     * Récupération des arguments de la manière suivante :
     *  $this->_getParam('nomArgument').
     *
     * Renvoie un message d'information.
     * @Secure("allowCell")
     */
    function deleteelementAction()
    {
        list($userRoleRef, $userId) = explode('#', $this->delete);
        $user = User_Model_User::load(array('id' => $userId));
        $userRole = User_Model_Role::loadByRef($userRoleRef);
        $orgaCell = Orga_Model_Cell::load(array('id' => $this->_getParam('idCell')));

        $user->removeRole($userRole);
        User_Service_User::getInstance()->sendEmail(
            $user,
            __('User', 'email', 'subjectAccessRightsChange'),
            __('Inventory', 'email', 'userRoleRemoved', array(
                'CELL' => $orgaCell->getLabelExtended(),
                'ROLE' => $userRole->getName()
            ))
        );

        $this->message = __('Inventory', 'role', 'userRoleRemovedFromUser');
        $this->send();
    }

}