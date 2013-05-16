<?php
/**
 * Classe Classif_IndicatorController
 * @author valentin.claras
 * @package    Classif
 * @subpackage Controller
 */

use Core\Annotation\Secure;


/**
 * Classe du controller gérant les Indicator.
 * @package    Classif
 * @subpackage Controller
 */
class Classif_IndicatorController extends Core_Controller
{
    /**
     * Action appelé à l'affichage des Indicator.
     *
     * @Secure("viewClassif")
     */
    public function listAction()
    {
    }

    /**
     * Action appelé à la gestion des Indicator.
     *
     * @Secure("editClassif")
     */
    public function manageAction()
    {
    }

}