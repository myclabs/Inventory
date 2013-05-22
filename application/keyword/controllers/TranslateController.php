<?php
/**
 * @author valentin.claras
 * @package Keyword
 * @subpackage Controller
 */

use Core\Annotation\Secure;

/**
 * Keyword_TranslateController
 * @package Keyword
 * @subpackage Controller
 */
class Keyword_TranslateController extends Core_Controller
{

    /**
     * Liste des libellés des Keyword_Model_Predicate en mode traduction.
     *
     * @Secure("editKeyword")
     */
    public function predicatesLabelAction()
    {
    }

    /**
     * Liste des libellés inverses des Keyword_Model_Predicate en mode traduction.
     *
     * @Secure("editKeyword")
     */
    public function predicatesReverselabelAction()
    {
    }

    /**
     * Liste des libellés des Keyword_Model_Keyword en mode traduction.
     *
     * @Secure("editKeyword")
     */
    public function keywordsAction()
    {
    }

}