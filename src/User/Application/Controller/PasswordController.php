<?php

use Core\Annotation\Secure;
use User\Application\Service\AuthAdapter;
use User\Domain\User;
use User\Domain\UserService;

/**
 * Contrôleur de gestion des mots de passe oubliés.
 *
 * @author matthieu.napoli
 */
class User_PasswordController extends UI_Controller_Captcha
{

    use UI_Controller_Helper_Form;

    /**
     * @Inject
     * @var UserService
     */
    private $userService;

    /**
     * @Inject("application.url")
     * @var string
     */
    private $applicationUrl;

    /**
     * @Inject("emails.noreply.name")
     * @var string
     */
    private $emailNoReplyName;

    /**
     * Formulaire de "mot de passe oublié"
     * @Secure("public")
     */
    public function forgottenAction()
    {
        $this->_helper->_layout->setLayout('layout-public');
        $this->view->assign('code', $this->getParam('code'));

        if ($this->getRequest()->isPost()) {

            // Valide le formulaire
            $email = $this->getParam('email');
            if (!$email) {
                UI_Message::addMessageStatic(__('User', 'login', 'unknownEmail'));
                return;
            } else {
                $emailUsed = User::isEmailUsed($email);
                if (!$emailUsed) {
                    UI_Message::addMessageStatic(__('User', 'login', 'unknownEmail'));
                    return;
                }
            }

            // Captcha de merde.
            $captchaInput = $this->getParam('captcha');
            $captchaField = new UI_Form_Element_Captcha('captcha', $this->view->baseUrl('/user/captcha/newimage'));
            if (! $captchaField->isValid($captchaInput, $captchaInput)) {
                UI_Message::addMessageStatic(__('User', 'resetPassword', 'invalidCaptchaInput'));
                return;
            }

            $user = User::loadByEmail($email);
            $user->generateKeyEmail();
            $user->save();
            $this->entityManager->flush();

            // On envoie le mail à l'utilisateur
            $url = sprintf(
                '%s/user/password/reset?code=%s',
                $this->applicationUrl,
                $user->getEmailKey()
            );
            $subject = __('User', 'email', 'subjectForgottenPassword');
            $content = __('User', 'email', 'bodyForgottenPassword', [
                'PASSWORD_RESET_LINK' => $url,
                'PASSWORD_RESET_CODE' => $user->getEmailKey(),
                'APPLICATION_NAME'    => $this->emailNoReplyName,
                'URL_APPLICATION'     => $this->applicationUrl . '/',
            ]);
            $this->userService->sendEmail($user, $subject, $content);

            UI_Message::addMessageStatic(
                __('User', 'resetPassword', 'emailNewPasswordLinkSent'),
                UI_Message::TYPE_SUCCESS
            );
        }
    }

    /**
     * Modification du mot de passe grace au code "emailKey"
     * @Secure("public")
     */
    public function resetAction()
    {
        $code = $this->getParam('code');
        if ($code == null) {
            $this->redirect('user/password/forgotten');
        }

        try {
            User::loadByEmailKey($code);
        } catch (Core_Exception_NotFound $e) {
            UI_Message::addMessageStatic(__('User', 'resetPassword', 'confirmationCodeInvalid'));
            $this->redirect('user/password/forgotten?code=' . $code);
        }
        $this->view->assign('code', $code);
        $this->_helper->_layout->setLayout('layout-public');
    }

    /**
     * Demande de nouveau mot de passe
     * @Secure("public")
     */
    public function resetSubmitAction()
    {
        $code = $this->getParam('code');
        if (!$code) {
            $this->redirect('user/password/forgotten');
            return;
        }

        try {
            $user = User::loadByEmailKey($code);
        } catch (Core_Exception_NotFound $e) {
            $this->redirect('user/password/forgotten');
            return;
        }

        $password1 = $this->getParam('password1');
        $password2 = $this->getParam('password2');

        // Validation
        if (empty($password1)) {
            UI_Message::addMessageStatic(__('UI', 'formValidation', 'emptyRequiredField'));
            $this->redirect('user/password/reset?code=' . $code);
        }
        if (empty($password2)) {
            UI_Message::addMessageStatic(__('UI', 'formValidation', 'emptyRequiredField'));
            $this->redirect('user/password/reset?code=' . $code);
        }
        if ($password1 != $password2) {
            UI_Message::addMessageStatic(__('User', 'editPassword', 'passwordsAreNotIdentical'));
            $this->redirect('user/password/reset?code=' . $code);
        }

        // Modifie le mot de passe
        $user->setPassword($password1);
        $user->eraseEmailKey();
        $this->entityManager->flush();

        // Log in automatiquement l'utilisateur
        $auth = Zend_Auth::getInstance();
        $authAdapter = new AuthAdapter($this->userService, $user->getEmail(), $password1);
        $auth->authenticate($authAdapter);

        UI_Message::addMessageStatic(__('UI', 'message', 'updated'), UI_Message::TYPE_SUCCESS);

        $this->redirect('/');
    }
}
