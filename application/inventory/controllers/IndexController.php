<?php
/**
 * @author valentin.claras
 * @package Inventory
 */

use Core\Annotation\Secure;


/**
 * @author valentin.claras
 * @package Inventory
 */
class Inventory_IndexController extends Core_Controller
{
    /**
     * Redirection sur la liste.
     * @Secure("public")
     */
    public function indexAction()
    {
        $this->_redirect('inventory/project/');
    }
}