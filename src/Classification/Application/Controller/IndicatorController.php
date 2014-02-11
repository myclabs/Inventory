<?php
/**
 * Classe Classification_IndicatorController
 * @author valentin.claras
 * @package    Classification
 * @subpackage Controller
 */

use Core\Annotation\Secure;


/**
 * Classe du controller gérant les Indicator.
 * @package    Classification
 * @subpackage Controller
 */
class Classification_IndicatorController extends Core_Controller
{
    /**
     * Action appelé à l'affichage des Indicator.
     *
     * @Secure("viewClassification")
     */
    public function listAction()
    {
    }

    /**
     * Action appelé à la gestion des Indicator.
     *
     * @Secure("editClassification")
     */
    public function manageAction()
    {
        $this->view->headScript()->appendFile('scripts/ui/refRefactor.js', 'text/javascript');
    }

}
