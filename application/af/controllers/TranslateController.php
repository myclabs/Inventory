<?php
/**
 * @author valentin.claras
 * @package AF
 * @subpackage Controller
 */

use Core\Annotation\Secure;

/**
 * AF_TranslateController
 * @package AF
 * @subpackage Controller
 */
class AF_TranslateController extends Core_Controller
{

    /**
     * Liste des libellés des AF_Model_Category en mode traduction.
     *
     * @Secure("editAF")
     */
    public function categoriesAction()
    {
    }

    /**
     * Liste des libellés des AF_Model_AF en mode traduction.
     *
     * @Secure("editAF")
     */
    public function afsAction()
    {
    }

    /**
     * Liste des libellés des AF_Model_Component en mode traduction.
     *
     * @Secure("editAF")
     */
    public function componentsLabelAction()
    {
    }

    /**
     * Liste des aides des AF_Model_Component en mode traduction.
     *
     * @Secure("editAF")
     */
    public function componentsHelpAction()
    {
    }

    /**
     * Liste des libellés des AF_Model_Component_Select_Option en mode traduction.
     *
     * @Secure("editAF")
     */
    public function optionsAction()
    {
    }

    /**
     * Liste des libellés des Algo_Model_Numeric en mode traduction.
     *
     * @Secure("editAF")
     */
    public function algosAction()
    {
    }

}