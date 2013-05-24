<?php
/**
 * @author valentin.claras
 * @package Inventory
 * @subpackage Controller
 */

use Core\Annotation\Secure;

/**
 * Inventory_TranslateController
 * @package Inventory
 * @subpackage Controller
 */
class Inventory_TranslateController extends Core_Controller
{

    /**
     * Liste des libellés des Inventory_Model_Project en mode traduction.
     *
     * @Secure("editProjects")
     */
    public function projectsAction()
    {
    }

}