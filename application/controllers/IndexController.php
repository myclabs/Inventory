<?php

use Core\Annotation\Secure;

class IndexController extends Core_Controller
{
    /**
     * @Secure("public")
     */
    public function indexAction()
    {
        $auth = Zend_Auth::getInstance();
        if ($auth->hasIdentity()) {
            $this->redirect('account/dashboard');
        }
        $this->redirect('user/action/login');
    }
}
