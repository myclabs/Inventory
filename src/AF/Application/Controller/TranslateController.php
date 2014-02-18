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
     * Liste des libellés des Category en mode traduction.
     *
     * @Secure("editAF")
     */
    public function categoriesAction()
    {
    }

    /**
     * Liste des libellés des AF en mode traduction.
     *
     * @Secure("editAF")
     */
    public function afsAction()
    {
    }

    /**
     * Liste des libellés des Component en mode traduction.
     *
     * @Secure("editAF")
     */
    public function componentsLabelAction()
    {
    }

    /**
     * Liste des aides des Component en mode traduction.
     *
     * @Secure("editAF")
     */
    public function componentsHelpAction()
    {
    }

    /**
     * Liste des libellés des SelectOption en mode traduction.
     *
     * @Secure("editAF")
     */
    public function optionsAction()
    {
    }

    /**
     * Liste des libellés des NumericAlgo en mode traduction.
     *
     * @Secure("editAF")
     */
    public function algosAction()
    {
    }

}
