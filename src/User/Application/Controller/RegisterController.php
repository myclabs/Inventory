<?php

use Core\Annotation\Secure;
use Orga\Application\Service\Workspace\PublicDemoService;
use User\Application\Service\AuthAdapter;
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
     * @var PublicDemoService
     */
    private $publicDemoService;

    /**
     * @Inject("feature.register")
     * @var int
     */
    private $enableRegister;

    /**
     * @Inject("feature.workspace.individual.register")
     * @var int
     */
    private $enableRegisterIndividual;

    /**
     * @Inject("feature.workspace.pme.register")
     * @var int
     */
    private $enableRegisterPME;

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
            $this->redirect('account/dashboard');
            return;
        }

        $this->_helper->_layout->setLayout('layout-public');
        $this->view->assign('registerIndividual', ($this->enableRegisterIndividual !== null));
        $this->view->assign('registerPME', ($this->enableRegisterPME !== null));

        if ($this->getRequest()->isPost()) {
            $projectType = trim($this->getParam('projectType'));
            $this->view->assign('projectType', $projectType);
            $email = trim($this->getParam('email'));
            $this->view->assign('email', $email);
            $password = $this->getParam('password');
            $passwordConfirm = $this->getParam('password2');
            $captchaInput = $this->getParam('captcha');

            // Validation du formulaire
            if (! $projectType || ! $email || ! $password || ! $passwordConfirm) {
                UI_Message::addMessageStatic(__('UI', 'formValidation', 'allFieldsRequired'));
                return;
            }
            if ($password && ($password != $passwordConfirm)) {
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
                switch ($projectType) {
                    case 'pme':
                        $this->publicDemoService->createUserToPMEDemo($email, $password);
                        break;
                    default:
                        $this->publicDemoService->createUserToIndividualDemo($email, $password);
                        break;
                }
            } catch (Core_ORM_DuplicateEntryException $e) {
                UI_Message::addMessageStatic(__('User', 'editEmail', 'emailAlreadyUsed'));
                return;
            } catch (\Exception $e) {
                throw $e;
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
