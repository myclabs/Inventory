<?php

use Core\Annotation\Secure;
use Orga\Application\Service\Workspace\FreeApplicationRegisteringService;
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
     * @var FreeApplicationRegisteringService
     */
    private $freeApplicationRegisteringService;

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
     * @Inject("feature.workspace.collectivity.register")
     * @var int
     */
    private $enableRegisterCollectivity;

    /**
     * @Inject("feature.workspace.smes.register")
     * @var int
     */
    private $enableRegisterSMEs;

    /**
     * @Secure("public")
     */
    public function indexAction()
    {
        if (! $this->enableRegister) {
            $this->redirect('user/action/login');
            return;
        }

        // Si l'utilisateur est déjà connecté, on redirige.
        if ($this->_helper->auth()) {
            $this->view->assign('email', $this->_helper->auth()->getEmail());
            $this->view->assign('emailConfirm', $this->_helper->auth()->getEmail());
        }

        $this->_helper->_layout->setLayout('layout-public');

        $this->view->assign('registerIndividual', ($this->enableRegisterIndividual !== null));
        $this->view->assign('registerCollectivity', ($this->enableRegisterCollectivity !== null));
        $this->view->assign('registerSMEs', ($this->enableRegisterSMEs !== null));

        if ($this->getRequest()->isPost()) {
            $projectType = trim($this->getParam('projectType'));
            $this->view->assign('projectType', $projectType);
            $email = trim($this->getParam('email'));
            $this->view->assign('email', $email);
            $emailConfirm = $this->getParam('emailConfirm');
            $this->view->assign('emailConfirm', $emailConfirm);
            $captchaInput = $this->getParam('captcha');

            // Validation du formulaire
            if (! $projectType || ! $email || ! $emailConfirm) {
                UI_Message::addMessageStatic(__('UI', 'formValidation', 'allFieldsRequired'));
                return;
            }
            if (! filter_var($email, FILTER_VALIDATE_EMAIL)) {
                UI_Message::addMessageStatic(__('User', 'editEmail', 'invalidEmail'));
                return;
            }
            if ($email && ($email != $emailConfirm)) {
                UI_Message::addMessageStatic(__('User', 'editEmail', 'emailsAreNotIdentical'));
                return;
            }
            $captchaField = new UI_Form_Element_Captcha('captcha', $this->view->baseUrl('/user/captcha/newimage'));
            if (! $captchaField->isValid($captchaInput)) {
                UI_Message::addMessageStatic(__('User', 'resetPassword', 'invalidCaptchaInput'));
                return;
            }

            try {
                switch ($projectType) {
                    case 'smes':
                        $password = $this->freeApplicationRegisteringService->createOrAddUserToSMEsDemo($email);
                        break;
                    case 'collectivity':
                        $password = $this->freeApplicationRegisteringService->createOrAddUserToCollectivityDemo($email);
                        break;
                    default:
                        $password = $this->freeApplicationRegisteringService->createOrAddUserToIndividualDemo($email);
                        break;
                }
            } catch (Core_ORM_DuplicateEntryException $e) {
                UI_Message::addMessageStatic(__('User', 'editEmail', 'emailAlreadyUsed'));
                return;
            } catch (\Exception $e) {
                throw $e;
            }

            if ($password) {
                // Authentification dans la foulée pour une création de compte.
                $auth = Zend_Auth::getInstance();
                $authAdapter = new AuthAdapter($this->userService, $email, $password);
                $auth->authenticate($authAdapter);
            }

            // Redirige sur l'accueil
            $this->redirect('');
            return;
        }
    }
}
