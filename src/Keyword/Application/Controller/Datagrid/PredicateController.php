<?php
/**
 * @author bertrand.ferry
 * @package Keyword
 */

use Core\Annotation\Secure;
use DI\Annotation\Inject;
use Keyword\Domain\PredicateService;
use Keyword\Domain\Predicate;

/**
 * Classe controleur de la datagrid de Predicate.
 * @package Keyword
 */
class Keyword_Datagrid_PredicateController extends UI_Controller_Datagrid
{
    /**
     * @Inject
     * @var PredicateService
     */
    private $predicateService;

    /**
     * (non-PHPdoc)
     * @see UI_Controller_Datagrid::getelementsAction()
     *
     * @Secure("viewKeyword")
     */
    public function getelementsAction()
    {
        foreach (Predicate::loadList($this->request) as $predicate) {
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

        $this->totalElements = Predicate::countTotal($this->request);
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
        $revRef = $this->getAddElementValue('reverseRef');
        $revLabel = $this->getAddElementValue('reverseLabel');
        $description = $this->getAddElementValue('description');

        $refErrors = $this->predicateService->getErrorMessageForNewRef($ref);
        if ($refErrors != null) {
            $this->setAddElementErrorMessage('ref', $refErrors);
        }
        $revRefErrors = $this->predicateService->getErrorMessageForNewReverseRef($revRef);
        if ($revRefErrors != null) {
            $this->setAddElementErrorMessage('reverseRef', $revRefErrors);
        }
        if ($ref === $revRef) {
            $this->setAddElementErrorMessage('ref', __('Keyword', 'predicate', 'refIsSameAsRevRef'));
            $this->setAddElementErrorMessage('reverseRef', __('Keyword', 'predicate', 'refIsSameAsRevRef'));
        }

        if (empty($this->_addErrorMessages)) {
            $this->predicateService->add($ref, $label, $revRef, $revLabel, $description);
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
        $this->predicateService->delete($this->delete);
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
        $predicateRef = $this->update['index'];
        $newValue = $this->update['value'];

        switch ($this->update['column']) {
            case 'label':
                $this->predicateService->updateLabel($predicateRef, $newValue);
                break;
            case 'reverseLabel':
                $this->predicateService->updateReverseLabel($predicateRef, $newValue);
                break;
            case 'ref':
                $this->predicateService->updateRef($predicateRef, $newValue);
                break;
            case 'reverseRef':
                $this->predicateService->updateReverseRef($predicateRef, $newValue);
                break;
            case 'description':
                $this->predicateService->updateDescription($predicateRef, $newValue);
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
        $predicate = Predicate::loadByRef($this->getParam('ref'));
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
        $predicate = Predicate::loadByRef($this->getParam('ref'));
        $this->data = $predicate->getDescription();
        $this->send();
    }

}
