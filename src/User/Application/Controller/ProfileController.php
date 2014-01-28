<?php
/**
 * @package    User
 * @subpackage Controller
 */

use Core\Annotation\Secure;
use User\Application\ForbiddenException;
use User\Domain\ACL\Action;
use User\Domain\ACL\ACLService;
use User\Domain\ACL\Resource\NamedResource;
use User\Domain\User;
use User\Domain\UserService;

/**
 * Contrôleur de gestion des utilisateurs
 * @package    User
 * @subpackage Controller
 */
class User_ProfileController extends Core_Controller
{

    use UI_Controller_Helper_Form;

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
     * Par défaut : redirige vers la liste des utilisateurs
     * @Secure("public")
     */
    public function indexAction()
    {
        $this->redirect('/user/profile/list');
    }

    /**
     * Liste des utilisateurs
     * @Secure("viewAllUsers")
     */
    public function listAction()
    {
        $loggedInUser = $this->_helper->auth();
        $resourceAllUsers = NamedResource::loadByName(User::class);
        $this->view->canCreateUsers = $this->aclService->isAllowed(
            $loggedInUser,
            Action::CREATE(),
            $resourceAllUsers
        );
    }

    /**
     * Informations publiques de l'utilisateur
     */
    public function seeAction()
    {
        $this->view->user = User::load($this->getParam('id'));
        if ($this->getRequest()->isXmlHttpRequest()) {
            $this->_helper->layout->disableLayout();
        }
    }

    /**
     * Action d'edition d'un profil utilisateur
     * @Secure("editUser")
     */
    public function editAction()
    {
        $loggedInUser = $this->_helper->auth();
        if ($this->hasParam('id')) {
            /** @var $user User */
            $user = User::load($this->getParam('id'));
        } else {
            $user = $loggedInUser;
        }

        // Est-ce que l'utilisateur peut modifier le mot de passe
        $this->view->canEditPassword = ($user === $loggedInUser);

        // Est-ce que l'utilisateur peut désactiver le compte
        $this->view->canDisable = $this->aclService->isAllowed(
            $loggedInUser,
            Action::DELETE(),
            $user
        );
        $this->view->canEnable = $this->aclService->isAllowed(
            $loggedInUser,
            Action::UNDELETE(),
            $user
        );

        // Est-ce que l'utilisateur se modifie lui-même
        $this->view->editSelf = ($user === $loggedInUser);

        $this->view->user = $user;
    }

    /**
     * Soumission du formulaire
     * @Secure("editUser")
     */
    public function editSubmitAction()
    {
        /** @var $user User */
        if ($this->hasParam('id')) {
            $user = User::load($this->getParam('id'));
        } else {
            $user = $this->_helper->auth();
        }

        $formData = $this->getFormData('userProfile');
        $user->setFirstName($formData->getValue('firstName'));
        $user->setLastName($formData->getValue('lastName'));
        $user->save();
        $this->entityManager->flush();

        $this->setFormMessage(__('UI', 'message', 'updated'));
        $this->sendFormResponse();
    }

    /**
     * Désactive un utilisateur
     * @Secure("disableUser")
     */
    public function disableAction()
    {
        $container = \Core\ContainerSingleton::getContainer();
        $connectedUser = $this->_helper->auth();

        /** @var $user User */
        $user = User::load($this->getParam('id'));

        // Désactivation de l'utilisateur
        $user->disable();
        $user->save();
        $this->entityManager->flush();

        // Envoi d'un email d'alerte
        $subject = __('User', 'email', 'subjectAccountDeactivated');
        $content = __('User', 'email', 'bodyAccountDeactivated',
                      [ 'APPLICATION_NAME' => $container->get('emails.noreply.name') ]
        );
        $this->userService->sendEmail($user, $subject, $content);

        UI_Message::addMessageStatic(__('User', 'editProfile', 'accountDeactivated') . ' '
                                         . __('User', 'editProfile', 'userInformedByEmail'), UI_Message::TYPE_SUCCESS);

        if ($user === $connectedUser) {
            $this->redirect('user/action/logout');
        } else {
            $this->redirect('user/profile/edit/id/' . $user->getId());
        }
    }

    /**
     * Active un utilisateur
     * @Secure("enableUser")
     */
    public function enableAction()
    {
        $container = \Core\ContainerSingleton::getContainer();
        /** @var $user User */
        $user = User::load($this->getParam('id'));

        // Activation de l'utilisateur
        $user->enable();
        $user->save();
        $this->entityManager->flush();

        // Envoi d'un email d'alerte
        $subject = __('User', 'email', 'subjectAccountActivated');
        $content = __('User', 'email', 'bodyAccountActivated',
                      [ 'APPLICATION_NAME' => $container->get('emails.noreply.name') ]
        );
        $this->userService->sendEmail($user, $subject, $content);

        $message = __('User', 'messages', 'accountActivated') . ' ' . __('User', 'editProfile', 'userInformedByEmail');
        UI_Message::addMessageStatic($message, UI_Message::TYPE_SUCCESS);

        $this->redirect('user/profile/edit/id/' . $user->getId());
    }

    /**
     * Formulaire de modification de l'email de l'utilisateur
     * @Secure("editUser")
     */
    public function editEmailAction()
    {
        $container = \Core\ContainerSingleton::getContainer();
        $loggedInUser = $this->_helper->auth();
        /** @var $user User */
        $user = User::load($this->getParam('id'));

        $editSelfEmail = ($user === $loggedInUser);

        if ($this->getRequest()->isPost()) {
            $formData = $this->getFormData('editEmail');
            $oldEmail = $formData->getValue('oldEmail');
            $email = $formData->getValue('email');
            $email2 = $formData->getValue('email2');
            $password = $formData->getValue('password');

            // Validation
            if (empty($email)) {
                $this->addFormError('email', __('UI', 'formValidation', 'emptyRequiredField'));
            }
            if (empty($email2)) {
                $this->addFormError('email2', __('UI', 'formValidation', 'emptyRequiredField'));
            }
            if ($email && ($email != $email2)) {
                $this->addFormError('email2', __('User', 'editEmail', 'emailsAreNotIdentical'));
            }
            if ($editSelfEmail) {
                if (empty($password)) {
                    $this->addFormError('password', __('UI', 'formValidation', 'emptyRequiredField'));
                } elseif (! $user->testPassword($password)) {
                    $this->addFormError('password', __('User', 'login', 'invalidPassword'));
                }
            }
            if ($email && User::isEmailUsed($email)) {
                $this->addFormError('email', __('User', 'list', 'emailAlreadyUsed'));
            }

            if (!$this->hasFormError()) {
                $subject = __('User', 'email', 'subjectEmailModified');
                if ($user === $this->_helper->auth()) {
                    $content = __('User', 'email', 'bodyEmailModifiedByUser',
                                  array(
                                       'OLD_EMAIL_ADDRESS' => $oldEmail,
                                       'NEW_EMAIL_ADDRESS' => $email,
                                       'APPLICATION_NAME'       => $container->get('emails.noreply.name')
                                  ));
                } else {
                    $content = __('User', 'email', 'bodyEmailModifiedByAdmin',
                                  array(
                                       'OLD_EMAIL_ADDRESS' => $oldEmail,
                                       'NEW_EMAIL_ADDRESS' => $email,
                                       'APPLICATION_NAME'  => $container->get('emails.noreply.name')
                                  ));
                }

                // Envoi de l'email à l'ancienne adresse
                $this->userService->sendEmail($user, $subject, $content);

                $user->setEmail($email);
                $this->entityManager->flush();

                // Envoi de l'email à la nouvelle adresse
                $this->userService->sendEmail($user, $subject, $content);

                if ($user === $this->_helper->auth()) {
                    $message = __('UI', 'message', 'updated');
                } else {
                    $message = __('UI', 'message', 'updated') . __('User', 'editEmail', 'userInformedByEmail');
                }
                UI_Message::addMessageStatic($message, UI_Message::TYPE_SUCCESS);
            }
            $this->sendFormResponse();
        }
        $this->view->editSelfEmail = $editSelfEmail;
        $this->view->user = $user;
    }

    /**
     * Formulaire de modification du mot de passe de l'utilisateur
     * @Secure("editUser")
     */
    public function editPasswordAction()
    {
        /** @var $user User */
        $user = User::load($this->getParam('id'));
        $loggedInUser = $this->_helper->auth();

        // Est-ce que l'utilisateur peut modifier le mot de passe
        if ($user !== $loggedInUser) {
            throw new ForbiddenException();
        }

        if ($this->getRequest()->isPost()) {
            $formData = $this->getFormData('editPassword');
            $oldPassword = $formData->getValue('oldPassword');
            $password = $formData->getValue('password');
            $password2 = $formData->getValue('password2');

            // Validation
            if (empty($oldPassword)) {
                $this->addFormError('oldPassword', __('UI', 'formValidation', 'emptyRequiredField'));
            }
            if (empty($password)) {
                $this->addFormError('password', __('UI', 'formValidation', 'emptyRequiredField'));
            }
            if (empty($password2)) {
                $this->addFormError('password2', __('UI', 'formValidation', 'emptyRequiredField'));
            }
            if ($password && ($password != $password2)) {
                $this->addFormError('password2', __('User', 'editPassword', 'passwordsAreNotIdentical'));
            }
            if ($oldPassword && !$user->testPassword($oldPassword)) {
                $this->addFormError('oldPassword', __('User', 'login', 'invalidPassword'));
            }

            if (! $this->hasFormError()) {
                $user->setPassword($password);
                $this->entityManager->flush();
                UI_Message::addMessageStatic(__('UI', 'message', 'updated'), UI_Message::TYPE_SUCCESS);
            }

            $this->sendFormResponse();
        }
        $this->view->user = $user;
    }

    /**
     * Passe un tutoriel (ou tous si tutorial = all)
     * @Secure("editUser")
     */
    public function dismissTutorialAction()
    {
        $tutorial = $this->getParam('tutorial');

        /** @var User $loggedInUser */
        $loggedInUser = $this->_helper->auth();
        $loggedInUser->dismissTutorial($tutorial);

        $this->sendJsonResponse([]);
    }
}
