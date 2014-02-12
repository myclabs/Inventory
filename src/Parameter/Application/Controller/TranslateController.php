<?php

use Core\Annotation\Secure;

/**
 * @author valentin.claras
 */
class Parameter_TranslateController extends Core_Controller
{
    /**
     * Liste des libellés des catégories en mode traduction.
     *
     * @Secure("editParameter")
     */
    public function categoriesAction()
    {
    }

    /**
     * Liste des libellés des familles en mode traduction.
     *
     * @Secure("editParameter")
     */
    public function familiesLabelAction()
    {
    }

    /**
     * Liste des documentation des familles en mode traduction.
     *
     * @Secure("editParameter")
     */
    public function familiesDocumentationAction()
    {
    }
}
