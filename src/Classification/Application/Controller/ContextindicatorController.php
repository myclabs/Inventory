<?php
/**
 * Classe Classification_ContextController
 * @author valentin.claras
 * @package    Classification
 * @subpackage Controller
 */

use Classification\Domain\IndicatorAxis;
use Classification\Domain\Context;
use Classification\Domain\Indicator;
use Core\Annotation\Secure;


/**
 * Classe du controller gérant les Context.
 * @package    Classification
 * @subpackage Controller
 */
class Classification_ContextindicatorController extends Core_Controller
{
    /**
     * Action appelé à l'affichage des ContextIndicator.
     *
     * @Secure("viewClassification")
     */
    public function listAction()
    {
    }

    /**
     * Action appelé à la gestion des ContextIndicator.
     *
     * @Secure("editClassification")
     */
    public function manageAction()
    {
        $this->view->listContexts = array();
        foreach (Context::loadList() as $context) {
            $this->view->listContexts[$context->getRef()] = $context->getLabel();
        }
        $this->view->listIndicators = array();
        foreach (Indicator::loadList() as $indicator) {
            $this->view->listIndicators[$indicator->getRef()] = $indicator->getLabel();
        }
        $this->view->listAxes = array();
        foreach (IndicatorAxis::loadList() as $axis) {
            $this->view->listAxes[$axis->getRef()] = $axis->getLabel();
        }
    }

}
