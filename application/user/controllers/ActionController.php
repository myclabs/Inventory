<?php
/**
 * @author     matthieu.napoli
 * @package    User
 * @subpackage Controller
 */

use Core\Annotation\Secure;
use DI\Annotation\Inject;

/**
 * Contrôleur de gestion des actions de l'utilisateurs
 * @package    User
 * @subpackage Controller
 */
class User_ActionController extends UI_Controller_Captcha
{

    use UI_Controller_Helper_Form;

    /**
     * @Inject
     * @var User_Service_User
     */
    private $userService;

    /**
     * Par défaut : redirige vers l'action de login.
     * @Secure("public")
     */
    public function indexAction()
    {
        $this->redirect('login');
    }

    /**
     * Login d'un utilisateur
     * @Secure("public")
     */
    public function loginAction()
    {
        /** @noinspection PhpUndefinedFieldInspection */
        $this->view->referer = Zend_Controller_Front::getInstance()->getBaseUrl().'/'.$this->getReferer();

        if ($this->getRequest()->isPost()) {
            $formData = $this->getFormData("login");
            $email = $formData->getValue('email');
            $password = $formData->getValue('password');

            // Validation
            if (! $email) {
                $this->addFormError('email', __('UI', 'formValidation', 'emptyRequiredField'));
            }
            if (! $password) {
                $this->addFormError('password', __('UI', 'formValidation', 'emptyRequiredField'));
            }

            if (! $this->hasFormError()) {
                // Obtention d'une référence de l'instance du Singleton de Zend_Auth.
                $auth = Zend_Auth::getInstance();
                // Définition de l'adaptateur d'authentification.
                $authAdapter = new User_AuthAdapter($email, $password);
                // Tentative d'authentification et stockage du résultat.
                $result = $auth->authenticate($authAdapter);
                if ($result->isValid()) {
                    /** @var $user User_Model_User */
                    $user = $this->_helper->auth();
                    if ($user->isEmailValidated() === false) {
                        $user->setEmailValidated(true);
                        $user->save();
                    }
                    $this->sendFormResponse();
                } else {
                    $messages = $result->getMessages();
                    $this->setFormMessage(implode(', ', $messages), UI_Message::TYPE_ALERT);
                }
            }
            $this->sendFormResponse();
        }
        $this->view->user = $this->_helper->auth();
    }

    /**
     * Logout d'un utilisateur
     * @Secure("public")
     */
    public function logoutAction()
    {
        // Vide l'identité mémorisée
        Zend_Auth::getInstance()->clearIdentity();
        $this->redirect($this->getReferer());
    }

    /**
     * authentification du mail de l'utilisateur
     * @Secure("public")
     */
    public function emailauthenticationAction()
    {
        $user = null;
        $cle = $this->getParam('mailKey');
        if ($cle != null) {
            //on charge l'utilisateur à partir de la clé mail
            try {
                $user = User_Model_User::loadByEmailKey($cle);
            } catch (Core_Exception_User $e) {
                UI_Message::addMessageStatic(__('User', 'messages', 'authenticationKeyInvalid'));
            }
            if ($user != null) {
                $user->eraseEmailKey();
                $user->setEmailValidated(true);
                $user->save();
                UI_Message::addMessageStatic(__('User', 'messages', 'authenticationKeyInvalid'),
                                             UI_Message::TYPE_SUCCESS);
            }
        } else {
            throw new Core_Exception_InvalidArgument(__('User', 'exceptions', 'noEmailKeySpecified'));
        }
        $this->redirect('login');
    }

    /**
     * Formulaire d'oubli de mot de passe
     * @Secure("public")
     */
    public function passwordForgottenAction()
    {
        if ($this->getRequest()->isPost()) {

            $formData = $this->getFormData('passwordForgotten');

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
                $entityManagers = Zend_Registry::get('EntityManagers');
                $entityManagers['default']->flush();

                // On envoie le mail à l'utilisateur
                $url = 'http://' . $_SERVER["SERVER_NAME"]
                    . $this->view->baseUrl() . "/user/action/new-password?key="
                    . $user->getEmailKey();
                $subject = __('User', 'email', 'subjectForgottenPassword');
                $config = Zend_Registry::get('configuration');
                if ((empty($config->emails->contact->adress)) || (empty($config->emails->contact->name))) {
                    throw new Core_Exception_NotFound('Le courriel de "contact" n\'a pas été défini !');
                }
                $content = __('User',
                              'email',
                              'bodyForgottenPassword',
                              array(
                                   'PASSWORD_RESET_CONFIRMATION_LINK' => $url,
                                   'APPLICATION_NAME'                 => $config->emails->noreply->name
                              ));
                $this->userService->sendEmail($user, $subject, $content);
                $this->setFormMessage(__('User', 'resetPassword', 'emailNewPasswordLinkSent'),
                                      UI_Message::TYPE_SUCCESS);
            }
            $this->sendFormResponse();
        }
    }

    /**
     * Demande de nouveau mot de passe
     * @Secure("public")
     */
    public function newPasswordAction()
    {
        $key = $this->getParam('key');
        if (!$key) {
            UI_Message::addMessageStatic(__('User', 'messages', 'unknownEmailKey'));
            $this->redirect('user/action/password-forgotten');
            return;
        }

        //on charge l'utilisateur à partir de la clé mail
        try {
            $user = User_Model_User::loadByEmailKey($key);
        } catch (Core_Exception_NotFound $e) {
            UI_Message::addMessageStatic(__('User', 'messages', 'unknownEmailKey'));
            $this->redirect('user/action/password-forgotten');
            return;
        }

        $config = Zend_Registry::get('configuration');
        if ((empty($config->emails->contact->adress)) || (empty($config->emails->contact->name))) {
            throw new Core_Exception_NotFound('Le courriel de "contact" n\'a pas été définit !');
        }

        $user->eraseEmailKey();
        $password = $user->setRandomPassword();
        $user->save();
        $entityManagers = Zend_Registry::get('EntityManagers');
        $entityManagers['default']->flush();

        $subject = __('User', 'email', 'subjectNewPassword',
                    array(
                         'APPLICATION_NAME' => $config->emails->noreply->name
                    ));
        $content = __('User', 'email', 'bodyNewPassword',
                      array(
                           'PASSWORD'         => $password,
                           'APPLICATION_NAME' => $config->emails->noreply->name,
                           'URL_APPLICATION'  => 'http://' . $_SERVER["SERVER_NAME"] . $this->view->baseUrl(),
                      ));
        $this->userService->sendEmail($user, $subject, $content);
    }

    /**
     * Renvoie la page de référence.
     * @return string
     */
    protected function getReferer()
    {
        $refer = urldecode($this->getParam('refer'));
        if (($refer !== null)
            && ($refer !== '')
            && !(strpos($refer, '/user/action/'))
            && ($refer != '/error/error')
        ) {
            return $refer;
        } else {
            return null;
        }
    }

}
