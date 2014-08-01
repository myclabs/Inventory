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
            $this->redirect('orga/workspace/manage');
            return;
        }

        if ($this->getRequest()->isPost()) {
            $projectName = trim($this->getParam('projectName'));
            $this->view->assign('projectName', $projectName);
            $email = trim($this->getParam('email'));
            $this->view->assign('email', $email);
            $password = $this->getParam('password');
            $password2 = $this->getParam('password2');
            $captchaInput = $this->getParam('captcha');

            // Validation du formulaire
            if (! $projectName || ! $email || ! $password || ! $password2) {
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
                $this->publicDemoService->createDemoAccount($email, $password, $projectName);
                $this->entityManager->flush();
                $this->entityManager->commit();
            } catch (Core_ORM_DuplicateEntryException $e) {
                $this->entityManager->rollback();
                UI_Message::addMessageStatic(__('User', 'editEmail', 'emailAlreadyUsed'));
                return;
            } catch (\Exception $e) {
                $this->entityManager->rollback();
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

        $this->_helper->_layout->setLayout('layout-public');
    }
}
