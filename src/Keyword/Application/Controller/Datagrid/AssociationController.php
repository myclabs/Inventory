<?php
/**
 * Classe Keyword_Datagrid_AssociationController
 * @author valentin.claras
 * @author bertrand.ferry
 * @package Keyword
 */

use Core\Annotation\Secure;
use DI\Annotation\Inject;
use Keyword\Domain\Association;
use Keyword\Domain\AssociationService;

/**
 * Classe controleur de la datagrid de Association.
 * @package Keyword
 */
class Keyword_Datagrid_AssociationController extends UI_Controller_Datagrid
{
    /**
     * @Inject
     * @var AssociationService
     */
    private $associationService;

    /**
     * Methode appelee pour remplir le tableau.
     *
     * @Secure("viewKeyword")
     */
    public function getelementsAction()
    {
        foreach (Association::loadList($this->request) as $association) {
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

        $this->totalElements = Association::countTotal($this->request);
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
        $refSubject = $this->getAddElementValue('subject');
        $refObject = $this->getAddElementValue('object');
        $refPredicate = $this->getAddElementValue('predicate');

        $subjectError = $this->associationService->getErrorMessageForAddSubject($refSubject);
        if ($subjectError != null) {
            $this->setAddElementErrorMessage('subject', $subjectError);
        }
        $objectError = $this->associationService->getErrorMessageForAddObject($refObject);
        if ($objectError != null) {
            $this->setAddElementErrorMessage('object', $objectError);
        }
        $predicateError = $this->associationService->getErrorMessageForAddPredicate($refPredicate);
        if ($predicateError != null) {
            $this->setAddElementErrorMessage('predicate', $predicateError);
        }
        if (empty($this->_addErrorMessages)) {
            $allError = $this->associationService->getErrorMessageForAdd($refSubject, $refObject, $refPredicate);
            if ($allError != null) {
                $this->setAddElementErrorMessage('predicate', $allError);
            }
        }

        if (empty($this->_addErrorMessages)) {
            $this->associationService->add($refSubject, $refObject, $refPredicate);
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
        if ($this->update['column'] !== 'predicate') {
            parent::updateelementAction();
        }
        list($refSubject, $refObject, $refPredicate) = explode('#', $this->update['index']);
        $newPredicate = $this->update['value'];
        $this->associationService->updatePredicate($refSubject, $refObject, $refPredicate, $newPredicate);
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
        list($refSubject, $refObject, $refPredicate) = explode('#', $this->delete);
        $this->associationService->delete($refSubject, $refObject, $refPredicate);
        $this->message = __('UI', 'message', 'deleted');
        $this->send();
    }

}
