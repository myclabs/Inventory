<?php

use Core\Annotation\Secure;


/**
 * @author valentin.claras
 */
class Orga_IndexController extends Core_Controller
{
    /**
     * @Secure("public")
     */
    public function indexAction()
    {
        $this->redirect('orga/organization/');
    }
}