<?php
/**
 * @author valentin.claras
 * @package Techno
 * @subpackage Controller
 */

use Core\Annotation\Secure;

/**
 * Techno_TranslateController
 * @package Techno
 * @subpackage Controller
 */
class Techno_TranslateController extends Core_Controller
{

    /**
     * Liste des libellés des Techno_Model_Category en mode traduction.
     *
     * @Secure("editTechno")
     */
    public function categoriesAction()
    {
    }

    /**
     * Liste des libellés des Techno_Model_Family en mode traduction.
     *
     * @Secure("editTechno")
     */
    public function familiesLabelAction()
    {
    }

    /**
     * Liste des documentation des Techno_Model_Family en mode traduction.
     *
     * @Secure("editTechno")
     */
    public function familiesDocumentationAction()
    {
    }

    /**
     * Liste des documentation des Techno_Model_Element en mode traduction.
     *
     * @Secure("editTechno")
     */
    public function elementsDocumentationAction()
    {
    }

}