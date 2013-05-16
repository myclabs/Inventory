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
        $this->_redirect("index/accueil");
    }

    /**
     * Welcome page
     * @Secure("public")
     */
    public function accueilAction()
    {
    }

}