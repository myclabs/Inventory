<?php
/**
 * @package    User
 * @subpackage Controller
 */

use Core\Annotation\Secure;
use User\Domain\ACL\Action;
use User\Domain\ACL\Role\Role;
use User\Domain\ACL\ACLService;
use User\Domain\User;
use User\Domain\UserService;

/**
 * @package    User
 * @subpackage Controller
 */
class User_Datagrid_UserListController extends UI_Controller_Datagrid
{
    /**
     * @Inject
     * @var UserService
     */
    private $userService;

    /**
     * @Inject
     * @var ACLService
     */
    private $aclService;

    /**
     * @Secure("viewAllUsers")
     */
    public function getelementsAction()
    {
        /** @var $users User[] */
        $users = User::loadList($this->request);
        $this->totalElements = User::countTotal($this->request);

        /** @var $loggedInUser User */
        $loggedInUser = $this->_helper->auth();

        foreach ($users as $user) {
            $action = Action::EDIT();

            $data = [];
            $data['index'] = $user->getId();
            $data['prenom'] = $user->getFirstName();
            $data['nom'] = $user->getLastName();
            $data['email'] = $user->getEmail();
            $data['creationDate'] = $this->cellDate($user->getCreationDate());
            $data['emailValidated'] = $user->isEmailValidated();
            $data['enabled'] = $user->isEnabled();
            // Roles
            $data['roles'] = [];
            foreach ($user->getRoles() as $role) {
                $data['roles'][] = $role->getLabel();
            }
            $data['roles'] = implode(', ', $data['roles']);

            // Edit
            if (($loggedInUser && $this->aclService->isAllowed($loggedInUser, $action, $user))) {
                $data['detailsUser'] = $this->cellLink('user/profile/edit/id/' . $user->getId(),
                                                       __('UI', 'verb', 'edit'), 'pencil');
            }

            $this->addLine($data);
        }
        $this->send();
    }

    /**
     * @Secure("createUser")
     */
    public function addelementAction()
    {
        $email = $this->getAddElementValue('email');
        if (!$email) {
            $this->setAddElementErrorMessage('email', __('UI', 'formValidation', 'emptyRequiredField'));
        }

        if (empty($this->_addErrorMessages)) {

            // Crée l'utilisateur
            try {
                $user = $this->userService->inviteUser($email);
            } catch (Core_Exception_Duplicate $e) {
                $this->setAddElementErrorMessage('email', __('User', 'list', 'emailAlreadyUsed'));
                $this->send();
                return;
            } catch (Core_Exception_InvalidArgument $e) {
                $this->setAddElementErrorMessage('email', __('UI', 'formValidation', 'invalidEmail'));
                $this->send();
                return;
            }

            $user->setFirstName($this->getAddElementValue('prenom'));
            $user->setLastName($this->getAddElementValue('nom'));

            $this->entityManager->flush();

            $this->message = __('User', 'list', 'accountCreated');
        }

        $this->send();
    }
}
