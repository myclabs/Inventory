<?php
/**
 * Classe Keyword_Datagrid_KeywordController
 * @author valentin.claras
 * @author bertrand.ferry
 * @package Keyword
 */

use Core\Annotation\Secure;
use DI\Annotation\Inject;

/**
 * Classe controleur de la datagrid de Keyword.
 * @package Keyword
 */
class Keyword_Datagrid_KeywordController extends UI_Controller_Datagrid
{
    /**
     * @Inject
     * @var Keyword_Service_Keyword
     */
    private $keywordService;

    /**
     * (non-PHPdoc)
     * @see UI_Controller_Datagrid::getelementsAction()
     *
     * @Secure("viewKeyword")
     */
    public function getelementsAction()
    {
        foreach (Keyword_Model_Keyword::loadList($this->request) as $keyword) {
            /** @var Keyword_Model_Keyword $keyword */
            $data = array();

            $data['index'] = $keyword->getRef();
            $data['label'] = $this->cellText($keyword->getLabel());
            $data['ref'] = $this->cellText($keyword->getRef());
            $data['nbRelations'] = $this->cellNumber($keyword->countAssociations());
            $data['linkToGraph'] = $this->cellLink('keyword/graph/consult?ref=' . $keyword->getRef());

            $this->addLine($data);
        }

        $this->totalElements = Keyword_Model_Keyword::countTotal($this->request);
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

        $refErrors = $this->keywordService->getErrorMessageForNewRef($ref);
        if ($refErrors != null) {
            $this->setAddElementErrorMessage('ref', $refErrors);
        }

        if (empty($this->_addErrorMessages)) {
            $this->keywordService->add($ref, $label);
            $this->message = __('UI', 'message', 'added');
        }

        $this->send();
    }

    /**
     * (non-PHPdoc)
     * @see UI_Controller_Datagrid::deleteelementAction()
     */
    public function deleteelementAction()
    {
        $this->keywordService->delete($this->delete);
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
        $keywordRef = $this->update['index'];
        $newValue = $this->update['value'];

        switch ($this->update['column']) {
            case 'label':
                $this->keywordService->updateLabel($keywordRef, $newValue);
                break;
            case 'ref':
                $this->keywordService->updateRef($keywordRef, $newValue);
                break;
            default:
        }
        $this->data = $newValue;
        $this->message = __('UI', 'message', 'updated');

        $this->send();
    }

}