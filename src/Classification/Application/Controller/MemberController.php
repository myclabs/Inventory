<?php
/**
 * Classe Classification_MemberController
 * @author valentin.claras
 * @package    Classification
 * @subpackage Controller
 */

use Classification\Domain\IndicatorAxis;
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
        $this->view->listAxes = IndicatorAxis::loadListOrderedAsAscendantTree();
    }

}
