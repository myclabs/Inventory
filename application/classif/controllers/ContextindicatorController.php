<?php
/**
 * Classe Classif_ContextController
 * @author valentin.claras
 * @package    Classif
 * @subpackage Controller
 */

use Core\Annotation\Secure;


/**
 * Classe du controller gérant les Context.
 * @package    Classif
 * @subpackage Controller
 */
class Classif_ContextindicatorController extends Core_Controller
{
    /**
     * Action appelé à l'affichage des ContextIndicator.
     *
     * @Secure("viewClassif")
     */
    public function listAction()
    {
    }

    /**
     * Action appelé à la gestion des ContextIndicator.
     *
     * @Secure("editClassif")
     */
    public function manageAction()
    {
        $this->view->listContexts = array();
        foreach (Classif_Model_Context::loadList() as $context) {
            $this->view->listContexts[$context->getRef()] = $context->getLabel();
        }
        $this->view->listIndicators = array();
        foreach (Classif_Model_Indicator::loadList() as $indicator) {
            $this->view->listIndicators[$indicator->getRef()] = $indicator->getLabel();
        }
        $this->view->listAxes = array();
        foreach (Classif_Model_Axis::loadList() as $axis) {
            $this->view->listAxes[$axis->getRef()] = $axis->getLabel();
        }
    }

}