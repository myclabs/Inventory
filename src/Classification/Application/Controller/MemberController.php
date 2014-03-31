<?php
/**
 * Classe Classification_MemberController
 * @author valentin.claras
 * @package    Classification
 * @subpackage Controller
 */

use Classification\Domain\Axis;
use Core\Annotation\Secure;


/**
 * Classe du controller gérant les Member.
 * @package    Classification
 * @subpackage Controller
 */
class Classification_MemberController extends Core_Controller
{
    /**
     * Action appelé à l'affichage des Member.
     *
     * @Secure("viewClassification")
     */
    public function listAction()
    {
    }

    /**
     * Action appelé à la gestion des Member.
     *
     * @Secure("editClassification")
     */
    public function manageAction()
    {
        $this->view->listAxes = Axis::loadListOrderedAsAscendantTree();
        $this->view->headScript()->appendFile('scripts/ui/refRefactor.js', 'text/javascript');
    }

}
