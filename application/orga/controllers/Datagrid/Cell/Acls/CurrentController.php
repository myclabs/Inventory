<?php
/**
 * @author valentin.claras
 * @package Orga
 * @subpackage Controller
 */

use Core\Annotation\Secure;

/**
 * Controlleur du Datagrid listant les Roles d'une Cellule.
 * @author valentin.claras
 * @package Orga
 * @subpackage Controller
 */
class Orga_Datagrid_Cell_Acls_CurrentController extends UI_Controller_Datagrid
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
        $idCell = $this->getParam('idCell');
        $cellACLResource = User_Model_Resource_Entity::loadByEntity(
            Orga_Model_Cell::loadByOrgaCell(
                Orga_Model_Cell::load($idCell)
            )
        );

        foreach ($cellACLResource->getLinkedSecurityIdentities() as $linkedIdentity) {
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
        $orgaCell = Orga_Model_Cell::load(array('id' => $this->getParam('idCell')));

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
                    $this->setAddElementErrorMessage('userRole', __('Orga', 'role', 'userAlreadyHasRole'));
                } else {
                    $user->addRole($userRole);
                    User_Service_User::getInstance()->sendEmail(
                        $user,
                        __('User', 'email', 'subjectAccessRightsChange'),
                        __('Orga', 'email', 'userRoleAdded', array(
                            'CELL' => $orgaCell->getLabelExtended(),
                            'ROLE' => $userRole->getName()
                        ))
                    );
                    $this->message = __('Orga', 'role', 'roleAddedToExistingUser');
                }
            } else {
                $user = User_Service_User::getInstance()->inviteUser(
                    $userEmail,
                    __('Orga', 'email', 'userRoleGivenAtCreation', array(
                        'CELL' => $orgaCell->getLabelExtended(),
                        'ROLE' => $userRole->getName()
                    ))
                );
                $this->message = __('Orga', 'role', 'userCreatedFromRessource');
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
     *  $this->getParam('nomArgument').
     *
     * Renvoie un message d'information.
     * @Secure("allowCell")
     */
    function deleteelementAction()
    {
        list($userRoleRef, $userId) = explode('#', $this->delete);
        $user = User_Model_User::load(array('id' => $userId));
        $userRole = User_Model_Role::loadByRef($userRoleRef);
        $orgaCell = Orga_Model_Cell::load(array('id' => $this->getParam('idCell')));

        $user->removeRole($userRole);
        User_Service_User::getInstance()->sendEmail(
            $user,
            __('User', 'email', 'subjectAccessRightsChange'),
            __('Orga', 'email', 'userRoleRemoved', array(
                'CELL' => $orgaCell->getLabelExtended(),
                'ROLE' => $userRole->getName()
            ))
        );

        $this->message = __('Orga', 'role', 'userRoleRemovedFromUser');
        $this->send();
    }

}