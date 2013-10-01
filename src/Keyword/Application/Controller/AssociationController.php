<?php
/**
 * Classe Keyword_AssociationController
 * @author valentin.claras
 * @package Keyword
 */

use Core\Annotation\Secure;
use DI\Annotation\Inject;
use Keyword\Domain\Keyword;
use Keyword\Domain\KeywordRepository;
use Keyword\Domain\Predicate;
use Keyword\Domain\PredicateRepository;

/**
 * Controlleur permettant de gérer les associations entre Keyword.
 * @package Keyword
 */
class Keyword_AssociationController extends Core_Controller
{
    /**
     * @Inject
     * @var KeywordRepository
     */
    protected $keywordRepository;
    /**
     * @Inject
     * @var PredicateRepository
     */
    protected $predicateRepository;

    /**
     * Liste des associations en consultation.
     *
     * @Secure("viewKeyword")
     */
    public function listAction()
    {
        $this->view->listKeywords = array();
        foreach ($this->keywordRepository->getAll() as $keyword) {
            $this->view->listKeywords[$keyword->getRef()] = $keyword->getLabel();
        }
        $this->view->listPredicates = array();
        foreach ($this->predicateRepository->getAll() as $predicate) {
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
        foreach ($this->keywordRepository->getAll() as $keyword) {
            $this->view->listKeywords[$keyword->getRef()] = $keyword->getLabel();
        }
        $this->view->listPredicates = array();
        foreach ($this->predicateRepository->getAll() as $predicate) {
            $this->view->listPredicates[$predicate->getRef()] = $predicate->getLabel();
        }
    }

}
