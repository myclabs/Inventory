<?php
/**
 * @author valentin.claras
 * @package Orga
 */

use Core\Annotation\Secure;


/**
 * @author valentin.claras
 * @package Orga
 */
class Orga_IndexController extends Core_Controller
{
    /**
     * Redirection sur la liste.
     * @Secure("public")
     */
    public function indexAction()
    {
        $this->redirect('orga/organization/');
    }
}