<?php
/**
 * @author valentin.claras
 * @package Classif
 * @subpackage Controller
 */

use Core\Annotation\Secure;

/**
 * Classif_TranslateController
 * @package Classif
 * @subpackage Controller
 */
class Classif_TranslateController extends Core_Controller
{

    /**
     * Liste des libellés des IndicatorAxis en mode traduction.
     *
     * @Secure("editClassif")
     */
    public function axesAction()
    {
    }

    /**
     * Liste des libellés des Classif_Model_Members en mode traduction.
     *
     * @Secure("editClassif")
     */
    public function membersAction()
    {
    }

    /**
     * Liste des libellés des Indicator en mode traduction.
     *
     * @Secure("editClassif")
     */
    public function indicatorsAction()
    {
    }

    /**
     * Liste des libellés des Context en mode traduction.
     *
     * @Secure("editClassif")
     */
    public function contextsAction()
    {
    }

}
