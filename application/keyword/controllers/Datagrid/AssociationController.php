<?php
/**
 * Classe Keyword_Datagrid_AssociationController
 * @author valentin.claras
 * @author bertrand.ferry
 * @package Keyword
 */

use Core\Annotation\Secure;

/**
 * Classe controleur de la datagrid de Association.
 * @package Keyword
 */
class Keyword_Datagrid_AssociationController extends UI_Controller_Datagrid
{
    /**
     * Methode appelee pour remplir le tableau.
     *
     * @Secure("viewKeyword")
     */
    public function getelementsAction()
    {
        foreach (Keyword_Model_Association::loadList($this->request) as $association) {
            $data = array();

            $refSubject = $association->getSubject()->getRef();
            $refObject = $association->getObject()->getRef();
            $refPredicate = $association->getPredicate()->getRef();
            $data['index'] = $refSubject.'#'.$refObject.'#'.$refPredicate;
            $data['subject'] = $this->cellList($refSubject, $association->getSubject()->getLabel());
            $data['object'] = $this->cellList($refObject, $association->getObject()->getLabel());
            $data['predicate'] = $this->cellList($refPredicate);

            $this->addLine($data);
        }

        $this->totalElements = Keyword_Model_Association::countTotal($this->request);
        $this->send();
    }

    /**
     * (non-PHPdoc)
     * @see UI_Controller_Datagrid::addelementAction()
     *
     * @Secure("editKeyword")
     */
    public function addelementAction()
    {
        /** @var Keyword_Service_Association $associationService */
        $associationService = $this->get('Keyword_Service_Association');

        $refSubject = $this->getAddElementValue('subject');
        $refObject = $this->getAddElementValue('object');
        $refPredicate = $this->getAddElementValue('predicate');

        $subjectError = $associationService->getErrorMessageForAddSubject($refSubject);
        if ($subjectError != null) {
            $this->setAddElementErrorMessage('subject', $subjectError);
        }
        $objectError = $associationService->getErrorMessageForAddObject($refObject);
        if ($objectError != null) {
            $this->setAddElementErrorMessage('object', $objectError);
        }
        $predicateError = $associationService->getErrorMessageForAddPredicate($refPredicate);
        if ($predicateError != null) {
            $this->setAddElementErrorMessage('predicate', $predicateError);
        }
        if (empty($this->_addErrorMessages)) {
            $allError = $associationService->getErrorMessageForAdd($refSubject, $refObject, $refPredicate);
            if ($allError != null) {
                $this->setAddElementErrorMessage('predicate', $allError);
            }
        }

        if (empty($this->_addErrorMessages)) {
            $associationService->add($refSubject, $refObject, $refPredicate);
            $this->message = __('UI', 'message', 'added');
        }

        $this->send();
    }

    /**
     * (non-PHPdoc)
     * @see UI_Controller_Datagrid::updateelementAction()
     *
     * @Secure("editKeyword")
     */
    public function updateelementAction()
    {
        /** @var Keyword_Service_Association $associationService */
        $associationService = $this->get('Keyword_Service_Association');

        if ($this->update['column'] !== 'predicate') {
            parent::updateelementAction();
        }
        list($refSubject, $refObject, $refPredicate) = explode('#', $this->update['index']);
        $newPredicate = $this->update['value'];
        $associationService->updatePredicate($refSubject, $refObject, $refPredicate, $newPredicate);
        $this->message = __('UI', 'message', 'updated');
        $this->send();
    }

    /**
     * (non-PHPdoc)
     * @see UI_Controller_Datagrid::deleteelementAction()
     *
     * @Secure("editKeyword")
     */
    public function deleteelementAction()
    {
        /** @var Keyword_Service_Association $associationService */
        $associationService = $this->get('Keyword_Service_Association');

        list($refSubject, $refObject, $refPredicate) = explode('#', $this->delete);
        $associationService->delete($refSubject, $refObject, $refPredicate);
        $this->message = __('UI', 'message', 'deleted');
        $this->send();
    }

}