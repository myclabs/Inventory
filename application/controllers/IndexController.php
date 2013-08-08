<?php

use Core\Annotation\Secure;

/**
 * Welcome page
 */
class IndexController extends Core_Controller
{

    /**
     * @Secure("public")
     */
    public function indexAction()
    {
        $auth = Zend_Auth::getInstance();
        if ($auth->hasIdentity()) {
            $this->redirect("orga/organization/");
        }
        $this->redirect("user/action/login");
    }

    /**
     * Welcome page
     * @Secure("public")
     */
    public function accueilAction()
    {
    }

}