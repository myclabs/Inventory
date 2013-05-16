<?php
/**
 * Classe Keyword_PredicateController
 * @author valentin.claras
 * @package Keyword
 */

use Core\Annotation\Secure;

/**
 * Controlleur permettant de gérer les prédicats.
 * @package Keyword
 */
class Keyword_PredicateController extends Core_Controller
{
    /**
     * Liste des associations en consultation.
     *
     * @Secure("viewKeyword")
     */
    public function listAction()
    {
    }

    /**
     * Liste des associations en édition.
     *
     * @Secure("editKeyword")
     */
    public function manageAction()
    {
    }

}