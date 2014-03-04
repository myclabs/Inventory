<?php
/**
 * Classe Classif_MemberController
 * @author valentin.claras
 * @package    Classif
 * @subpackage Controller
 */

use Core\Annotation\Secure;


/**
 * Classe du controller gérant les Member.
 * @package    Classif
 * @subpackage Controller
 */
class Classif_MemberController extends Core_Controller
{
    /**
     * Action appelé à l'affichage des Member.
     *
     * @Secure("viewClassif")
     */
    public function listAction()
    {
    }

    /**
     * Action appelé à la gestion des Member.
     *
     * @Secure("editClassif")
     */
    public function manageAction()
    {
        $this->view->listAxes = Classif_Model_Axis::loadListOrderedAsAscendantTree();
        $this->view->headScript()->appendFile('scripts/ui/refRefactor.js', 'text/javascript');
    }

}