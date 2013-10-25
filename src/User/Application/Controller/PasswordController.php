<?php

use Core\Annotation\Secure;
use DI\Annotation\Inject;
use User\Application\AuthAdapter;
use User\Domain\User;
use User\Domain\UserService;

/**
 * Contrôleur de gestion des mots de passe oubliés
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
     * Formulaire de "mot de passe oublié"
     * @Secure("public")
     */
    public function forgottenAction()
    {
        if ($this->getRequest()->isPost()) {

            $formData = $this->getFormData('passwordForgotten');

            // Valide le formulaire
            $email = $formData->getValue('email');
            if (!$email) {
                $this->addFormError('email', __('UI', 'formValidation', 'emptyRequiredField'));
            } else {
                $emailUsed = User::isEmailUsed($email);
                if (!$emailUsed) {
                    $this->addFormError('email', __('User', 'login', 'unknownEmail'));
                }
            }

            // Captcha de merde.
            $captchaInput = [
                'id' => $formData->getHiddenValue('captcha', 'captcha-id'),
                'input' => $formData->getValue('captcha'),
            ];
            $captchaField = new UI_Form_Element_Captcha('captcha', $this->view->baseUrl('/user/captcha/newimage'));
            if (! $captchaField->isValid($captchaInput, $captchaInput)) {
                $this->addFormError('captcha', __('User', 'resetPassword', 'invalidCaptchaInput'));
            }

            if (! $this->hasFormError()) {
                $user = User::loadByEmail($email);
                $user->generateKeyEmail();
                $user->save();
                $this->entityManager->flush();

                // On envoie le mail à l'utilisateur
                $url = sprintf('http://%s/user/password/reset?code=%s',
                    $_SERVER["SERVER_NAME"] . $this->view->baseUrl(),
                    $user->getEmailKey());
                $urlApplication = 'http://' . $_SERVER["SERVER_NAME"] . Zend_Controller_Front::getInstance()->getBaseUrl() . '/';
                $subject = __('User', 'email', 'subjectForgottenPassword');
                $config = Zend_Registry::get('configuration');
                if (empty($config->emails->contact->adress)) {
                    throw new Core_Exception_NotFound('Le courriel de "contact" n\'a pas été défini !');
                }
                $content = __('User',
                              'email',
                              'bodyForgottenPassword',
                              array(
                                   'PASSWORD_RESET_LINK' => $url,
                                   'PASSWORD_RESET_CODE' => $user->getEmailKey(),
                                   'APPLICATION_NAME'    => $config->emails->noreply->name,
                                   'URL_APPLICATION'     => $urlApplication,
                              ));
                $this->userService->sendEmail($user, $subject, $content);
                $this->setFormMessage(__('User', 'resetPassword', 'emailNewPasswordLinkSent'),
                                      UI_Message::TYPE_SUCCESS);
            }
            $this->sendFormResponse();
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
            UI_Message::addMessageStatic(__('User', 'messages', 'authenticationKeyInvalid'));
            $this->redirect('user/password/forgotten');
        }
        $this->view->assign('code', $code);
    }

    /**
     * Demande de nouveau mot de passe
     * @Secure("public")
     */
    public function resetSubmitAction()
    {
        $code = $this->getParam('code');
        if (!$code) {
            $this->redirect('user/password/reset');
            return;
        }

        try {
            $user = User::loadByEmailKey($code);
        } catch (Core_Exception_NotFound $e) {
            $this->redirect('user/password/reset');
            return;
        }

        $formData = $this->getFormData('editPassword');
        $password1 = $formData->getValue('password1');
        $password2 = $formData->getValue('password2');

        // Validation
        if (empty($password1)) {
            $this->addFormError('password1', __('UI', 'formValidation', 'emptyRequiredField'));
        }
        if (empty($password2)) {
            $this->addFormError('password2', __('UI', 'formValidation', 'emptyRequiredField'));
        }
        if ($password1 && ($password1 != $password2)) {
            $this->addFormError('password2', __('User', 'editPassword', 'passwordsAreNotIdentical'));
        }

        if (! $this->hasFormError()) {
            // Modifie le mot de passe
            $user->setPassword($password1);
            $user->eraseEmailKey();
            $this->entityManager->flush();

            // Log in automatiquement l'utilisateur
            $auth = Zend_Auth::getInstance();
            $authAdapter = new AuthAdapter($user->getEmail(), $password1);
            $auth->authenticate($authAdapter);

            UI_Message::addMessageStatic(__('UI', 'message', 'updated'), UI_Message::TYPE_SUCCESS);
        }

        $this->sendFormResponse();
    }

}
