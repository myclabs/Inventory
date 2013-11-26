<?php
/**
 * @author     matthieu.napoli
 * @package    User
 * @subpackage Controller
 */

use Core\Annotation\Secure;
use User\Application\Service\AuthAdapter;
use User\Domain\User;
use User\Domain\UserService;

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
     * @var UserService
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
                $authAdapter = new AuthAdapter($this->userService, $email, $password);
                // Tentative d'authentification et stockage du résultat.
                $result = $auth->authenticate($authAdapter);
                if ($result->isValid()) {
                    /** @var $user User */
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
                $user = User::loadByEmailKey($cle);
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
