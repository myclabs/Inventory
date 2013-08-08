<?php
/**
 * Classe Keyword_Datagrid_KeywordController
 * @author valentin.claras
 * @author bertrand.ferry
 * @package Keyword
 */

use Core\Annotation\Secure;

/**
 * Classe controleur de la datagrid de Keyword.
 * @package Keyword
 */
class Keyword_Datagrid_KeywordController extends UI_Controller_Datagrid
{
    /**
     * (non-PHPdoc)
     * @see UI_Controller_Datagrid::getelementsAction()
     *
     * @Secure("viewKeyword")
     */
    public function getelementsAction()
    {
        foreach (Keyword_Model_Keyword::loadList($this->request) as $keyword) {
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

        $refErrors = Keyword_Service_Keyword::getInstance()->getErrorMessageForNewRef($ref);
        if ($refErrors != null) {
            $this->setAddElementErrorMessage('ref', $refErrors);
        }

        if (empty($this->_addErrorMessages)) {
            $keyword = Keyword_Service_Keyword::getInstance()->add($ref, $label);
            $this->message = __('UI', 'message', 'added');
        }

        $this->send();
    }

    /**
     * (non-PHPdoc)
     * @see UI_Controller_Datagrid::deleteelementAction()
     *
     * @Secure("deleteKeyword")
     */
    public function deleteelementAction()
    {
        $keywordLabel = Keyword_Service_Keyword::getInstance()->delete($this->delete);
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
                $keyword = Keyword_Service_Keyword::getInstance()->updateLabel($keywordRef, $newValue);
                break;
            case 'ref':
                $keyword = Keyword_Service_Keyword::getInstance()->updateRef($keywordRef, $newValue);
                break;
            default:
        }
        $this->data = $newValue;
        $this->message = __('UI', 'message', 'updated');

        $this->send();
    }

}