<?php
/**
 * Classe Keyword_Datagrid_AssociationController
 * @author valentin.claras
 * @author bertrand.ferry
 * @package Keyword
 */

use Core\Annotation\Secure;
use DI\Annotation\Inject;
use Keyword\Domain\KeywordRepository;
use Keyword\Domain\PredicateRepository;
use Keyword\Domain\Association;
use Keyword\Domain\AssociationRepository;

/**
 * Classe controleur de la datagrid de Association.
 * @package Keyword
 */
class Keyword_Datagrid_AssociationController extends UI_Controller_Datagrid
{
    /**
     * @Inject
     * @var KeywordRepository
     */
    private $keywordRepository;

    /**
     * @Inject
     * @var PredicateRepository
     */
    private $predicateRepository;

    /**
     * Methode appelee pour remplir le tableau.
     *
     * @Secure("viewKeyword")
     */
    public function getelementsAction()
    {
        /** @var Association $association */
        foreach ($this->keywordRepository->getAllAssociations($this->request) as $association) {
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

        $this->totalElements = $this->keywordRepository->countAssociations($this->request);
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

        try {
            $subject = $this->keywordRepository->getByRef($refSubject);
        } catch (\Core_Exception_NotFound $e) {
            $this->setAddElementErrorMessage('subject', __('UI', 'formValidation', 'emptyRequiredField'));
        }
        try {
            $predicate = $this->predicateRepository->getByRef($refPredicate);
        } catch (\Core_Exception_NotFound $e) {
            $this->setAddElementErrorMessage('predicate', __('UI', 'formValidation', 'emptyRequiredField'));
        }
        try {
            $object = $this->keywordRepository->getByRef($refObject);
        } catch (\Core_Exception_NotFound $e) {
            $this->setAddElementErrorMessage('object', __('UI', 'formValidation', 'emptyRequiredField'));
        }

        if (empty($this->_addErrorMessages)) {
            $errorMessage = $this->keywordRepository->getErrorMessageForAssociation($subject, $predicate, $object);
            if ($errorMessage !== null) {
                if ($refSubject === $refObject) {
                    $this->setAddElementErrorMessage('subject', $errorMessage);
                    $this->setAddElementErrorMessage('object', $errorMessage);
                } else {
                    $this->setAddElementErrorMessage('predicate', $errorMessage);
                }
            } else {
                $subject->addAssociationWith($predicate, $object);
                $this->entityManager->flush();
                $this->message = __('UI', 'message', 'added');
            }

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
        $subject = $this->keywordRepository->getByRef($refSubject);
        $predicate = $this->predicateRepository->getByRef($refPredicate);
        $object = $this->keywordRepository->getByRef($refObject);
        $association = $this->keywordRepository->getAssociationBySubjectPredicateObject($subject, $predicate, $object);

        $newPredicate = $this->predicateRepository->getByRef($this->update['value']);
        if ($newPredicate === $predicate) {
            $this->message = __('UI', 'message', 'updated');
        } else {
            $this->keywordRepository->checkAssociation($subject, $newPredicate, $object);
            $association->setPredicate($newPredicate);
            $this->entityManager->flush();
            $this->message = __('UI', 'message', 'updated');
        }
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
        $subject = $this->keywordRepository->getByRef($refSubject);
        $predicate = $this->predicateRepository->getByRef($refPredicate);
        $object = $this->keywordRepository->getByRef($refObject);
        $subject->removeAssociation($this->keywordRepository->getAssociationBySubjectPredicateObject($subject, $predicate, $object));
        $this->message = __('UI', 'message', 'deleted');
        $this->send();
    }

}
