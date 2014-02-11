<?php
/**
 * Classe Classif_AxisController
 * @author valentin.claras
 * @package    Classif
 * @subpackage Controller
 */

use Core\Annotation\Secure;


/**
 * Classe du controller gérant les Axis.
 * @package    Classif
 * @subpackage Controller
 */
class Classif_AxisController extends Core_Controller
{
    /**
     * Action appelé à l'affichage des Axis.
     *
     * @Secure("viewClassif")
     */
    public function listAction()
    {
    }

    /**
     * Action appelé à la gestion des Axis.
     *
     * @Secure("editClassif")
     */
    public function manageAction()
    {
        $this->view->listParents = Classif_Model_Axis::loadList();
    }

}