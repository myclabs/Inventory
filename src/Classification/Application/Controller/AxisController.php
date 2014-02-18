<?php
/**
 * Classe Classification_AxisController
 * @author valentin.claras
 * @package    Classification
 * @subpackage Controller
 */

use Classification\Domain\IndicatorAxis;
use Core\Annotation\Secure;


/**
 * Classe du controller gérant les Axis.
 * @package    Classification
 * @subpackage Controller
 */
class Classification_AxisController extends Core_Controller
{
    /**
     * Action appelé à l'affichage des Axis.
     *
     * @Secure("viewClassification")
     */
    public function listAction()
    {
    }

    /**
     * Action appelé à la gestion des Axis.
     *
     * @Secure("editClassification")
     */
    public function manageAction()
    {
        $this->view->listParents = IndicatorAxis::loadList();
    }

}
