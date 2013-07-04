<?php
/**
 * @author valentin.claras
 * @package Orga
 * @subpackage Controller
 */

use Core\Annotation\Secure;
use DI\Annotation\Inject;

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
     * @var Orga_Service_ACLManager
     */
    private $aclManager;

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
        $organizationAdministratorRole = User_Model_Role::loadByRef('organizationAdministrator_'.$idOrganization);
        $organization = Orga_Model_Organization::load($idOrganization);

        $userEmail = $this->getAddElementValue('userEmail');
        if (empty($userEmail)) {
            $this->setAddElementErrorMessage('userEmail', __('UI', 'formValidation', 'emptyRequiredField'));
        }

        if (empty($this->_addErrorMessages)) {
            $this->entityManager->beginTransaction();

            if (User_Model_User::isEmailUsed($userEmail)) {
                $user = User_Model_User::loadByEmail($userEmail);
                if ($user->hasRole($organizationAdministratorRole)) {
                    $this->setAddElementErrorMessage('userEmail', __('Orga', 'role', 'userAlreadyHasRole'));
                } else {
                    set_time_limit(0);
                    try {
                        $this->entityManager->flush();

                        $this->aclManager->addOrganizationAdministrator(
                            $organization,
                            $user
                        );
                        $this->entityManager->flush();

                        $this->entityManager->commit();
                    } catch (Exception $e) {
                        $this->entityManager->rollback();
                        $this->entityManager->clear();

                        throw $e;
                    }
                    $this->userService->sendEmail(
                        $user,
                        __('User', 'email', 'subjectAccessRightsChange'),
                        __('Orga', 'email', 'userOrganizationAdministratorRoleAdded', array(
                            'ORGANIZATION' => $organization->getLabel(),
                        ))
                    );
                    $this->message = __('Orga', 'role', 'roleAddedToExistingUser');
                }
            } else {
                $user = $this->userService->inviteUser(
                    $userEmail,
                    __('Orga', 'email', 'userOrganizationAdministratorRoleGivenAtCreation', array(
                        'ORGANIZATION' => $organization->getLabel(),
                        'ROLE' => __('Orga', 'role', $organizationAdministratorRole->getName())
                    ))
                );
                $this->message = __('Orga', 'role', 'userCreatedFromRessource');
                $user->addRole(User_Model_Role::loadByRef('user'));

                set_time_limit(0);
                try {
                    $this->entityManager->flush();

                    $this->aclManager->addOrganizationAdministrator(
                        $organization,
                        $user
                    );
                    $this->entityManager->flush();

                    $this->entityManager->commit();
                } catch (Exception $e) {
                    $this->entityManager->rollback();
                    $this->entityManager->clear();

                    throw $e;
                }
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
        $idOrganization = $this->getParam('idOrganization');
        $organization = Orga_Model_Organization::load($idOrganization);
        $user = User_Model_User::load($this->delete);

        set_time_limit(0);
        $this->entityManager->beginTransaction();
        try {
            $this->entityManager->flush();

            $this->aclManager->removeOrganizationAdministrator(
                $organization,
                $user
            );
            $this->entityManager->flush();

            $this->entityManager->commit();
        } catch (Exception $e) {
            $this->entityManager->rollback();
            $this->entityManager->clear();

            throw $e;
        }

        $this->userService->sendEmail(
            $user,
            __('User', 'email', 'subjectAccessRightsChange'),
            __('Orga', 'email', 'userOrganizationAdministratorRoleRemoved', array(
                'ORGANIZATION' => $organization->getLabel(),
            ))
        );

        $this->message = __('Orga', 'role', 'userRoleRemovedFromUser');
        $this->send();
    }

}