<?php
/**
 * @author valentin.claras
 * @package Classification
 * @subpackage Controller
 */

use Core\Annotation\Secure;

/**
 * Classification_TranslateController
 * @package Classification
 * @subpackage Controller
 */
class Classification_TranslateController extends Core_Controller
{

    /**
     * Liste des libellés des IndicatorAxis en mode traduction.
     *
     * @Secure("editClassification")
     */
    public function axesAction()
    {
    }

    /**
     * Liste des libellés des Classification_Model_Members en mode traduction.
     *
     * @Secure("editClassification")
     */
    public function membersAction()
    {
    }

    /**
     * Liste des libellés des Indicator en mode traduction.
     *
     * @Secure("editClassification")
     */
    public function indicatorsAction()
    {
    }

    /**
     * Liste des libellés des Context en mode traduction.
     *
     * @Secure("editClassification")
     */
    public function contextsAction()
    {
    }

}
