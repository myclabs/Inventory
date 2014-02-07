<?php

use Core\Annotation\Secure;
use User\Application\Service\AuthAdapter;
use User\Domain\User;
use User\Domain\UserService;

/**
 * Inscription des utilisateurs.
 *
 * @author matthieu.napoli
 */
class User_RegisterController extends UI_Controller_Captcha
{
    use UI_Controller_Helper_Form;

    /**
     * @Inject
     * @var UserService
     */
    private $userService;

    /**
     * @Inject
     * @var Orga_Service_OrganizationService
     */
    private $organizationService;

    /**
     * @Inject("feature.register")
     * @var boolean
     */
    private $enableRegister;

    /**
     * @Secure("public")
     */
    public function indexAction()
    {
        if (! $this->enableRegister) {
            $this->redirect('user/action/login');
            return;
        }

        // Si l'utilisateur est déjà connecté, on redirige
        if ($this->_helper->auth()) {
            $this->redirect('orga/organization/manage');
            return;
        }

        if ($this->getRequest()->isPost()) {
            $email = $this->getParam('email');
            $this->view->email = $email;
            $password = $this->getParam('password');
            $password2 = $this->getParam('password2');
            $captchaInput = $this->getParam('captcha');

            // Validation du formulaire
            if (! $email || ! $password || ! $password2) {
                UI_Message::addMessageStatic(__('UI', 'formValidation', 'allFieldsRequired'));
                return;
            }
            if ($password && ($password != $password2)) {
                UI_Message::addMessageStatic(__('User', 'editPassword', 'passwordsAreNotIdentical'));
                return;
            }
            if (! filter_var($email, FILTER_VALIDATE_EMAIL)) {
                UI_Message::addMessageStatic(__('User', 'editEmail', 'invalidEmail'));
                return;
            }
            $captchaField = new UI_Form_Element_Captcha('captcha', $this->view->baseUrl('/user/captcha/newimage'));
            if (! $captchaField->isValid($captchaInput)) {
                UI_Message::addMessageStatic(__('User', 'resetPassword', 'invalidCaptchaInput'));
                return;
            }

            try {
                $this->entityManager->beginTransaction();
                $this->organizationService->createDemoOrganizationAndUser($email, $password);
                $this->entityManager->flush();
                $this->entityManager->commit();
            } catch (Core_ORM_DuplicateEntryException $e) {
                $this->entityManager->rollback();
                UI_Message::addMessageStatic(__('User', 'editEmail', 'emailAlreadyUsed'));
                return;
            }

            // Authentification dans la foulée
            $auth = Zend_Auth::getInstance();
            $authAdapter = new AuthAdapter($this->userService, $email, $password);
            $auth->authenticate($authAdapter);

            // Redirige sur l'accueil
            $this->redirect('');
            return;
        }
    }
}
