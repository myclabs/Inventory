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
     * @Inject
     * @var AssociationRepository
     */
    private $associationRepository;

    /**
     * Methode appelee pour remplir le tableau.
     *
     * @Secure("viewKeyword")
     */
    public function getelementsAction()
    {
        /** @var Association $association */
        foreach ($this->associationRepository->getAll($this->request) as $association) {
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

        $this->totalElements = $this->associationRepository->count($this->request);
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
            $subject = $this->keywordRepository->getOneByRef($refSubject);
        } catch (\Core_Exception_NotFound $e) {
            $this->setAddElementErrorMessage('subject', __('UI', 'formValidation', 'emptyRequiredField'));
        }
        try {
            $predicate = $this->predicateRepository->getOneByRef($refPredicate);
        } catch (\Core_Exception_NotFound $e) {
            $this->setAddElementErrorMessage('predicate', __('UI', 'formValidation', 'emptyRequiredField'));
        }
        try {
            $object = $this->keywordRepository->getOneByRef($refObject);
        } catch (\Core_Exception_NotFound $e) {
            $this->setAddElementErrorMessage('object', __('UI', 'formValidation', 'emptyRequiredField'));
        }

        if (empty($this->_addErrorMessages)) {
            $association = new Association($subject, $predicate, $object);
            $errorMessage = $this->associationRepository->getErrorMessageForAssociation($association);
            if ($errorMessage !== null) {
                $this->setAddElementErrorMessage('subject', $errorMessage);
                $this->setAddElementErrorMessage('predicate', $errorMessage);
                $this->setAddElementErrorMessage('object', $errorMessage);
                //@todo Supprimer le clear quand le plugin flush aura aussi été supprimé.
                $this->entityManager->clear();
            } else {
                $this->associationRepository->add($association);
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
        $subject = $this->keywordRepository->getOneByRef($refSubject);
        $predicate = $this->predicateRepository->getOneByRef($refPredicate);
        $object = $this->keywordRepository->getOneByRef($refObject);
        $association = $this->associationRepository->getOneBySubjectPredicateObject($subject, $predicate, $object);

        $newPredicate = $this->predicateRepository->getOneByRef($this->update['value']);
        if ($newPredicate === $predicate) {
            $this->message = __('UI', 'message', 'updated');
        } else {
            $association->setPredicate($newPredicate);
            $this->associationRepository->checkAssociation($association);
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
        $subject = $this->keywordRepository->getOneByRef($refSubject);
        $predicate = $this->predicateRepository->getOneByRef($refPredicate);
        $object = $this->keywordRepository->getOneByRef($refObject);
        $association = $this->associationRepository->getOneBySubjectPredicateObject($subject, $predicate, $object);
        $this->associationRepository->remove($association);
        $this->message = __('UI', 'message', 'deleted');
        $this->send();
    }

}
