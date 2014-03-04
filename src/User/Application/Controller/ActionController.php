<?php

use Core\Annotation\Secure;
use User\Application\Service\AuthAdapter;
use User\Domain\User;
use User\Domain\UserService;

/**
 * Contrôleur de gestion des actions de l'utilisateurs.
 *
 * @author matthieu.napoli
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
     * @Inject("feature.register")
     * @var boolean
     */
    private $enableRegister;

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
        $this->view->assign('user', $this->_helper->auth());
        $this->view->assign('enableRegister', $this->enableRegister);

        if ($this->getRequest()->isPost()) {
            $email = $this->getParam('email');
            $password = $this->getParam('password');

            // Validation
            if (! $email) {
                UI_Message::addMessageStatic(__('UI', 'formValidation', 'emptyRequiredField'));
                return;
            }
            if (! $password) {
                UI_Message::addMessageStatic(__('UI', 'formValidation', 'emptyRequiredField'));
                return;
            }

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
                $this->redirect($this->getReferer());
            } else {
                UI_Message::addMessageStatic(implode(', ', $result->getMessages()));
            }
        }
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
                UI_Message::addMessageStatic(
                    __('User', 'messages', 'authenticationKeyInvalid'),
                    UI_Message::TYPE_SUCCESS
                );
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
