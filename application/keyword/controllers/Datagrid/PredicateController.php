<?php
/**
 * @author bertrand.ferry
 * @package Keyword
 */

use Core\Annotation\Secure;

/**
 * Classe controleur de la datagrid de Predicate.
 * @package Keyword
 */
class Keyword_Datagrid_PredicateController extends UI_Controller_Datagrid
{
    /**
     * (non-PHPdoc)
     * @see UI_Controller_Datagrid::getelementsAction()
     *
     * @Secure("viewKeyword")
     */
    public function getelementsAction()
    {
        foreach (Keyword_Model_Predicate::loadList($this->request) as $predicate) {
            /** @var Keyword_Model_Predicate $predicate */
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

        $this->totalElements = Keyword_Model_Predicate::countTotal($this->request);
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
        /** @var Keyword_Service_Predicate $predicateService */
        $predicateService = $this->get('Keyword_Service_Predicate');

        $ref = $this->getAddElementValue('ref');
        $label = $this->getAddElementValue('label');
        $revRef = $this->getAddElementValue('reverseRef');
        $revLabel = $this->getAddElementValue('reverseLabel');
        $description = $this->getAddElementValue('description');

        $refErrors = $predicateService->getErrorMessageForNewRef($ref);
        if ($refErrors != null) {
            $this->setAddElementErrorMessage('ref', $refErrors);
        }
        $revRefErrors = $predicateService->getErrorMessageForNewReverseRef($revRef);
        if ($revRefErrors != null) {
            $this->setAddElementErrorMessage('reverseRef', $revRefErrors);
        }
        if ($ref === $revRef) {
            $this->setAddElementErrorMessage('ref', __('Keyword', 'predicate', 'refIsSameAsRevRef'));
            $this->setAddElementErrorMessage('reverseRef', __('Keyword', 'predicate', 'refIsSameAsRevRef'));
        }

        if (empty($this->_addErrorMessages)) {
            $predicate = $predicateService->add($ref, $label, $revRef, $revLabel, $description);
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
        /** @var Keyword_Service_Predicate $predicateService */
        $predicateService = $this->get('Keyword_Service_Predicate');

        $predicateService->delete($this->delete);
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
        /** @var Keyword_Service_Predicate $predicateService */
        $predicateService = $this->get('Keyword_Service_Predicate');

        $predicateRef = $this->update['index'];
        $newValue = $this->update['value'];

        switch ($this->update['column']) {
            case 'label':
                $predicateService->updateLabel($predicateRef, $newValue);
                break;
            case 'reverseLabel':
                $predicateService->updateReverseLabel($predicateRef, $newValue);
                break;
            case 'ref':
                $predicateService->updateRef($predicateRef, $newValue);
                break;
            case 'reverseRef':
                $predicateService->updateReverseRef($predicateRef, $newValue);
                break;
            case 'description':
                $predicateService->updateDescription($predicateRef, $newValue);
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
        $predicate = Keyword_Model_Predicate::loadByRef($this->getParam('ref'));
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
        $predicate = Keyword_Model_Predicate::loadByRef($this->getParam('ref'));
        $this->data = $predicate->getDescription();
        $this->send();
    }

}