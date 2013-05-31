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
class Keyword_GraphController extends Core_Controller_Ajax
{
    /**
     * Graphe des Keyword, racine.
     *
     * @Secure("viewKeyword")
     */
    public function rootAction()
    {
        $this->view->rootKeywords = Keyword_Model_Keyword::loadListRoots();
    }

    /**
     * Redirige vers un la consultation d'un Keyword.
     *
     * @Secure("viewKeyword")
     */
    public function gotokeywordAction()
    {
        $refGoToKeyword = $this->getParam('keywordGoTo');
        if (!isset($refGoToKeyword)) {
            $this->redirect('keyword/graph/root');
        }
        $this->redirect('keyword/graph/consult?ref='.$refGoToKeyword);
    }

    /**
     * Graphe des Keyword, détail d'un Keyword.
     *
     * @Secure("viewKeyword")
     */
    public function consultAction()
    {
        $refCurrentKeyword = $this->getParam('ref');
        if (!isset($refCurrentKeyword)) {
            $this->redirect('keyword/graph/root');
        }
        try {
            $this->view->keyword = Keyword_Model_Keyword::loadByRef($refCurrentKeyword);
        } catch (Core_Exception_NotFound $e) {
            UI_Message::addMessageStatic(__('Keyword', 'graph', 'nonExistentKeyword'));
            $this->redirect('keyword/graph/root');
        }

        $this->view->subjectKeywords = array();
        foreach ($this->view->keyword->getAssociationsAsObject() as $objectAssociation) {
            $predicateLabel = $objectAssociation->getPredicate()->getReverseLabel();
            if (!isset($this->view->subjectKeywords[$predicateLabel])) {
                $this->view->subjectKeywords[$predicateLabel] = array();
            }
            $this->view->subjectKeywords[$predicateLabel][] = $objectAssociation->getSubject();
        }

        $this->view->objectKeywords = array();
        foreach ($this->view->keyword->getAssociationsAsSubject() as $subjectAssociation) {
            $predicateLabel = $subjectAssociation->getPredicate()->getLabel();
            if (!isset($this->view->objectKeywords[$predicateLabel])) {
                $this->view->objectKeywords[$predicateLabel] = array();
            }
            $this->view->objectKeywords[$predicateLabel][] = $subjectAssociation->getObject();
        }
    }

    /**
     * Renvoie la liste des Keywords.
     *
     * @Secure("viewKeyword")
     */
    public function autocompleteproviderAction()
    {
        $listKeywords = array();

        $ref = $this->getParam('q');
        $queryMatch = new Core_Model_Query();
        $queryMatch->filter->addCondition(Keyword_Model_Keyword::QUERY_LABEL, $ref, Core_Model_Filter::OPERATOR_CONTAINS);
        foreach (Keyword_Model_Keyword::loadList($queryMatch) as $keyword) {
            $listKeywords[] = array('id' => $keyword->getRef(), 'text' => $keyword->getLabel());
        }

        $this->sendJsonResponse($listKeywords);
    }

}