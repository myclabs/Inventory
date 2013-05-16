<?php
/**
 * Classe Keyword_KeywordController
 * @author valentin.claras
 * @author bertrand.ferry
 * @package Keyword
 */

use Core\Annotation\Secure;

/**
 * Controlleur permettant de gérer les Keyword.
 * @package Keyword
 */
class Keyword_KeywordController extends Core_Controller_Ajax
{
    /**
     * Liste des Keywords en consultation.
     *
     * @Secure("viewKeyword")
     */
    public function listAction()
    {
    }

    /**
     * Liste des Keywords en édition.
     *
     * @Secure("editKeyword")
     */
    public function manageAction()
    {
    }

}