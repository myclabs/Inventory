<?php

use Core\Annotation\Secure;
use MyCLabs\ACL\ACL;
use User\Domain\ACL\Actions;
use MyCLabs\ACL\Model\ClassResource;
use User\Application\ForbiddenException;
use User\Domain\User;
use User\Domain\UserService;

/**
 * Contrôleur de gestion des utilisateurs
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
     * @var ACL
     */
    private $acl;

    /**
     * @Inject("emails.noreply.name")
     * @var string
     */
    private $emailNoReplyName;

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
        $this->view->canCreateUsers = $this->acl->isAllowed(
            $loggedInUser,
            Actions::CREATE,
            new ClassResource(User::class)
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
        $this->view->canDisable = $this->acl->isAllowed(
            $loggedInUser,
            Actions::DELETE,
            $user
        );
        $this->view->canEnable = $this->acl->isAllowed(
            $loggedInUser,
            Actions::UNDELETE,
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

        $user->setFirstName($this->getParam('firstName'));
        $user->setLastName($this->getParam('lastName'));
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
        $connectedUser = $this->_helper->auth();

        /** @var $user User */
        $user = User::load($this->getParam('id'));

        if ($user === $connectedUser) {
            UI_Message::addMessageStatic(
                __('User', 'editProfile', 'ownAccountDeactivationProhibited'),
                UI_Message::TYPE_ERROR
            );
            $this->redirect('user/profile/edit/id/' . $user->getId());
        }
        else {
            // Désactivation de l'utilisateur
            $user->disable();
            $user->save();
            $this->entityManager->flush();

            // Envoi d'un email d'alerte
            $subject = __('User', 'email', 'subjectAccountDeactivated');
            $content = __('User', 'email', 'bodyAccountDeactivated', [ 'APPLICATION_NAME' => $this->emailNoReplyName ]);
            $this->userService->sendEmail($user, $subject, $content);

            UI_Message::addMessageStatic(
                __('User', 'editProfile', 'accountDeactivated') . ' ' . __('User', 'editProfile', 'userInformedByEmail'),
                UI_Message::TYPE_SUCCESS
            );

            $this->redirect('user/profile/edit/id/' . $user->getId());
        }
    }

    /**
     * Active un utilisateur
     * @Secure("enableUser")
     */
    public function enableAction()
    {
        $connectedUser = $this->_helper->auth();

        /** @var $user User */
        $user = User::load($this->getParam('id'));

        if ($user === $connectedUser) {
            UI_Message::addMessageStatic(
                __('User', 'editProfile', 'ownAccountActivationProhibited'),
                UI_Message::TYPE_ERROR
            );
            $this->redirect('user/profile/edit/id/' . $user->getId());
        }
        else {

            // Activation de l'utilisateur
            $user->enable();
            $user->save();
            $this->entityManager->flush();

            // Envoi d'un email d'alerte
            $subject = __('User', 'email', 'subjectAccountActivated');
            $content = __('User', 'email', 'bodyAccountActivated', ['APPLICATION_NAME' => $this->emailNoReplyName]);
            $this->userService->sendEmail($user, $subject, $content);

            $message = __('User', 'editProfile', 'accountActivated') . ' ' . __('User', 'editProfile', 'userInformedByEmail');
            UI_Message::addMessageStatic($message, UI_Message::TYPE_SUCCESS);

            $this->redirect('user/profile/edit/id/' . $user->getId());
        }
    }

    /**
     * Formulaire de modification de l'email de l'utilisateur
     * @Secure("editUser")
     */
    public function editEmailAction()
    {
        $loggedInUser = $this->_helper->auth();
        /** @var $user User */
        $user = User::load($this->getParam('id'));

        $editSelfEmail = ($user === $loggedInUser);

        if ($this->getRequest()->isPost()) {
            $email = $this->getParam('email');
            $password = $this->getParam('password');

            // Validation
            if (empty($email)) {
                $this->addFormError('email', __('UI', 'formValidation', 'emptyRequiredField'));
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
                    $content = __('User', 'email', 'bodyEmailModifiedByUser', [
                        'OLD_EMAIL_ADDRESS' => $user->getEmail(),
                        'NEW_EMAIL_ADDRESS' => $email,
                        'APPLICATION_NAME'  => $this->emailNoReplyName
                    ]);
                } else {
                    $content = __('User', 'email', 'bodyEmailModifiedByAdmin', [
                        'OLD_EMAIL_ADDRESS' => $user->getEmail(),
                        'NEW_EMAIL_ADDRESS' => $email,
                        'APPLICATION_NAME'  => $this->emailNoReplyName
                    ]);
                }

                // Envoi de l'email à l'ancienne adresse
                $this->userService->sendEmail($user, $subject, $content);

                $user->setEmail($email);
                $this->entityManager->flush();

                // Envoi de l'email à la nouvelle adresse
                $this->userService->sendEmail($user, $subject, $content);

                if ($editSelfEmail) {
                    $message = __('UI', 'message', 'updated');
                } else {
                    $message = __('UI', 'message', 'updated') . __('User', 'editEmail', 'userInformedByEmail');
                }
                $this->setFormMessage($message, UI_Message::TYPE_SUCCESS);
            }
            $this->sendFormResponse();
            return;
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
            $oldPassword = $this->getParam('oldPassword');
            $password = $this->getParam('password');
            $password2 = $this->getParam('password2');

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
                $this->setFormMessage(__('UI', 'message', 'updated'), UI_Message::TYPE_SUCCESS);
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

    /**
     * Remet à zéro tous les états d'avancements des tutoriels
     * @Secure("editUser")
     */
    public function resetTutorialsAction()
    {
        /** @var User $loggedInUser */
        $loggedInUser = $this->_helper->auth();
        $loggedInUser->initTutorials();

        $this->sendJsonResponse([]);
    }
}
