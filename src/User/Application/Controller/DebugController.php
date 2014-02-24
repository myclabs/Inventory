<?php

use Core\Annotation\Secure;
use User\Application\ForbiddenException;
use User\Application\Service\DebugAuthAdapter;

/**
 * Contrôleur pour débugger l'application
 * @author matthieu.napoli
 */
class User_DebugController extends UI_Controller_Captcha
{
    use UI_Controller_Helper_Form;

    /**
     * @Inject("debug.login")
     * @var bool
     */
    private $debugLogin;

    /**
     * Bypass du login : connecte un utilisateur sans mot de passe
     * @Secure("public")
     */
    public function loginAction()
    {
        if ($this->debugLogin !== true) {
            throw new ForbiddenException;
        }

        $email = $this->getParam('email');

        $auth = Zend_Auth::getInstance();
        $auth->authenticate(new DebugAuthAdapter($email));

        $response = $this->getResponse();
        $response->setBody('OK');
        $response->sendResponse();
        exit;
    }
}
