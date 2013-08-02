<?php
/**
 * Classe Keyword_AssociationController
 * @author valentin.claras
 * @package Keyword
 */

use Core\Annotation\Secure;
use Keyword\Domain\Keyword;
use Keyword\Domain\Predicate;

/**
 * Controlleur permettant de gérer les associations entre Keyword.
 * @package Keyword
 */
class Keyword_AssociationController extends Core_Controller
{
    /**
     * Liste des associations en consultation.
     *
     * @Secure("viewKeyword")
     */
    public function listAction()
    {
        $this->view->listKeywords = array();
        foreach (Keyword::loadList() as $keyword) {
            $this->view->listKeywords[$keyword->getRef()] = $keyword->getLabel();
        }
        $this->view->listPredicates = array();
        foreach (Predicate::loadList() as $predicate) {
            $this->view->listPredicates[$predicate->getRef()] = $predicate->getLabel();
        }
    }

    /**
     * Liste des associations en édition.
     *
     * @Secure("editKeyword")
     */
    public function manageAction()
    {
        $this->view->listKeywords = array();
        foreach (Keyword::loadList() as $keyword) {
            $this->view->listKeywords[$keyword->getRef()] = $keyword->getLabel();
        }
        $this->view->listPredicates = array();
        foreach (Predicate::loadList() as $predicate) {
            $this->view->listPredicates[$predicate->getRef()] = $predicate->getLabel();
        }
    }

}
