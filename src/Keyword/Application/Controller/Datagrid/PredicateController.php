<?php
/**
 * @author bertrand.ferry
 * @package Keyword
 */

use Core\Annotation\Secure;
use DI\Annotation\Inject;
use Keyword\Domain\Predicate;
use Keyword\Domain\PredicateRepository;

/**
 * Classe controleur de la datagrid de Predicate.
 * @package Keyword
 */
class Keyword_Datagrid_PredicateController extends UI_Controller_Datagrid
{
    /**
     * @Inject
     * @var PredicateRepository
     */
    private $predicateRepository;

    /**
     * (non-PHPdoc)
     * @see UI_Controller_Datagrid::getelementsAction()
     *
     * @Secure("viewKeyword")
     */
    public function getelementsAction()
    {
        /** @var Predicate $predicate */
        foreach ($this->predicateRepository->getAll($this->request) as $predicate) {
            /** @var Predicate $predicate */
            $data = array();

            $data['index'] = $predicate->getRef();
            $data['label'] = $this->cellText($predicate->getLabel());
            $data['ref'] = $this->cellText($predicate->getRef());
            $data['reverseLabel'] = $this->cellText($predicate->getReverseLabel());
            $data['reverseRef'] = $this->cellText($predicate->getReverseRef());

            $urlText = 'keyword/datagrid_predicate/getdescription?ref=' . $predicate->getRef();
            $urlBrut = 'keyword/datagrid_predicate/getbrutdescription?ref=' . $predicate->getRef();
            $data['description'] = $this->cellLongText($urlText, $urlBrut);

            $this->addLine($data);
        }

        $this->totalElements = $this->predicateRepository->count($this->request);
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
        $ref = $this->getAddElementValue('ref');
        $label = $this->getAddElementValue('label');
        $reverseRef = $this->getAddElementValue('reverseRef');
        $reverseLabel = $this->getAddElementValue('reverseLabel');
        $description = $this->getAddElementValue('description');

        $refErrors = $this->predicateRepository->getErrorMessageForRef($ref);
        if ($refErrors != null) {
            $this->setAddElementErrorMessage('ref', $refErrors);
        }
        $revRefErrors = $this->predicateRepository->getErrorMessageForRef($reverseRef);
        if ($revRefErrors != null) {
            $this->setAddElementErrorMessage('reverseRef', $revRefErrors);
        }
        if ($ref === $reverseRef) {
            $this->setAddElementErrorMessage('ref', __('Keyword', 'predicate', 'refIsSameAsRevRef'));
            $this->setAddElementErrorMessage('reverseRef', __('Keyword', 'predicate', 'refIsSameAsRevRef'));
        }

        if (empty($this->_addErrorMessages)) {
            $predicate = new Predicate($ref, $reverseRef, $label, $reverseLabel);
            $predicate->setDescription($description);
            $this->predicateRepository->add($predicate);
            $this->entityManager->flush();
            $this->message = __('UI', 'message', 'added');
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
        $this->predicateRepository->remove($this->predicateRepository->getOneByRef($this->delete));
        $this->entityManager->flush();
        $this->message = __('UI', 'message', 'deleted');
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
        $predicate = $this->predicateRepository->getOneByRef($this->update['index']);
        $newValue = $this->update['value'];

        switch ($this->update['column']) {
            case 'label':
                $predicate->setLabel($newValue);
                break;
            case 'reverseLabel':
                $predicate->setReverseLabel($newValue);
                break;
            case 'ref':
                if ($newValue !== $predicate->getRef()) {
                    $this->predicateRepository->checkRef($newValue);
                    $predicate->setRef($newValue);
                }
                break;
            case 'reverseRef':
                if ($newValue !== $predicate->getReverseRef()) {
                    $this->predicateRepository->checkRef($newValue);
                    $predicate->setReverseRef($newValue);
                }
                break;
            case 'description':
                $predicate->setDescription($newValue);
                break;
            default:
                break;
        }
        $this->data = $newValue;
        $this->message = __('UI', 'message', 'updated');

        $this->send();
    }

    /**
     * Renvoie la description mise en forme du Predicate.
     *
     * @Secure("viewKeyword")
     */
    public function getdescriptionAction()
    {
        $predicate = $this->predicateRepository->getOneByRef($this->getParam('ref'));
        $this->data = Core_Tools::textile($predicate->getDescription());
        $this->send();
    }

    /**
     * Renvoie la description brute du Predicate.
     *
     * @Secure("editKeyword")
     */
    public function getbrutdescriptionAction()
    {
        $predicate = $this->predicateRepository->getOneByRef($this->getParam('ref'));
        $this->data = $predicate->getDescription();
        $this->send();
    }

}
