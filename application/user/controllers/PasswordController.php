<?php

use Core\Annotation\Secure;
use DI\Annotation\Inject;
use User\AuthAdapter;

/**
 * Contrôleur de gestion des mots de passe oubliés
 * @author matthieu.napoli
 */
class User_PasswordController extends UI_Controller_Captcha
{

    use UI_Controller_Helper_Form;

    /**
     * @Inject
     * @var User_Service_User
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
                $emailUsed = User_Model_User::isEmailUsed($email);
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
                $user = User_Model_User::loadByEmail($email);
                $user->generateKeyEmail();
                $user->save();
                $this->entityManager->flush();

                // On envoie le mail à l'utilisateur
                $url = sprintf('http://%s/user/password/reset?code=%s',
                    $_SERVER["SERVER_NAME"] . $this->view->baseUrl(),
                    $user->getEmailKey());
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
                                   'APPLICATION_NAME'    => $config->emails->noreply->name
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

        // Affichage du formulaire de modification du mot de passe
        if ($code != null) {
            try {
                User_Model_User::loadByEmailKey($code);
                $this->view->assign('code', $code);
                $this->_helper->viewRenderer->renderBySpec('reset-password',
                    ['module' => 'user', 'controller' => 'password']);
                return;
            } catch (Core_Exception_NotFound $e) {
                UI_Message::addMessageStatic(__('User', 'messages', 'authenticationKeyInvalid'));
            }
        }

        // Affichage du formulaire de vérification du code
        $this->_helper->viewRenderer->renderBySpec('verify-code', ['module' => 'user', 'controller' => 'password']);
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
            $user = User_Model_User::loadByEmailKey($code);
        } catch (Core_Exception_NotFound $e) {
            $this->redirect('user/password/reset');
            return;
        }

        $config = Zend_Registry::get('configuration');
        if (empty($config->emails->contact->adress)) {
            throw new Core_Exception_NotFound("Le courriel de 'contact' n'a pas été définie");
        }

        $user->eraseEmailKey();
        $password = $user->setRandomPassword();
        $user->save();
        $this->entityManager->flush();

        $subject = __('User', 'email', 'subjectNewPassword',
                    array(
                         'APPLICATION_NAME' => $config->emails->noreply->name
                    ));
        $content = __('User', 'email', 'bodyNewPassword',
                      array(
                           'PASSWORD'         => $password,
                           'APPLICATION_NAME' => $config->emails->noreply->name,
                           'URL_APPLICATION'  => 'http://' . $_SERVER["SERVER_NAME"] . $this->view->baseUrl() . '/',
                      ));
        $this->userService->sendEmail($user, $subject, $content);
    }

}
