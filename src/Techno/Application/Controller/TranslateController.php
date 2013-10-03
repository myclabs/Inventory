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
     * Liste des libellés des catégories en mode traduction.
     *
     * @Secure("editTechno")
     */
    public function categoriesAction()
    {
    }

    /**
     * Liste des libellés des familles en mode traduction.
     *
     * @Secure("editTechno")
     */
    public function familiesLabelAction()
    {
    }

    /**
     * Liste des documentation des familles en mode traduction.
     *
     * @Secure("editTechno")
     */
    public function familiesDocumentationAction()
    {
    }

    /**
     * Liste des documentation des familles en mode traduction.
     *
     * @Secure("editTechno")
     */
    public function elementsDocumentationAction()
    {
    }

}