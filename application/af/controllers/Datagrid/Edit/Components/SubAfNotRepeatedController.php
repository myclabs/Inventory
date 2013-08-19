<?php
/**
 * @author  matthieu.napoli
 * @author  hugo.charbonnier
 * @author  thibaud.rolland
 * @package AF
 */

use Core\Annotation\Secure;

/**
 * Conditions Controller
 * @package AF
 */
class AF_Datagrid_Edit_Components_SubAfNotRepeatedController extends UI_Controller_Datagrid
{

    /**
     * (non-PHPdoc)
     * @see UI_Controller_Datagrid::getelementsAction()
     * @Secure("editAF")
     */
    public function getelementsAction()
    {
        /** @var $af AF_Model_AF */
        $af = AF_Model_AF::load($this->getParam('id'));
        // Filtre sur l'AF
        $this->request->filter->addCondition(AF_Model_Component::QUERY_AF, $af);
        /** @var $subAFList AF_Model_Component_SubAF_NotRepeated[] */
        $subAFList = AF_Model_Component_SubAF_NotRepeated::loadList($this->request);
        foreach ($subAFList as $subAF) {
            $data = [];
            $data['index'] = $subAF->getId();
            $data['label'] = $subAF->getLabel();
            $data['ref'] = $subAF->getRef();
            $data['help'] = $this->cellLongText('af/edit_components/popup-help/id/' . $subAF->getId(),
                                                ' af/datagrid_edit_components_sub-af-not-repeated/get-raw-help/id/'
                                                    . $subAF->getId(),
                                                __('UI', 'name', 'help'),
                                                'zoom-in');
            $data['isVisible'] = $subAF->isVisible();
            $data['targetAF'] = $subAF->getCalledAF()->getId();
            $data['foldaway'] = $subAF->getFoldaway();
            $this->addLine($data);
        }
        $this->send();
    }

    /**
     * (non-PHPdoc)
     * @see UI_Controller_Datagrid::addelementAction()
     * @Secure("editAF")
     */
    public function addelementAction()
    {
        /** @var $af AF_Model_AF */
        $af = AF_Model_AF::load($this->getParam('id'));
        $ref = $this->getAddElementValue('ref');
        if (empty($ref)) {
            $this->setAddElementErrorMessage('ref', __('UI', 'formValidation', 'emptyRequiredField'));
        }
        $isVisible = $this->getAddElementValue('isVisible');
        if (empty($isVisible)) {
            $this->setAddElementErrorMessage('isVisible', __('UI', 'formValidation', 'emptyRequiredField'));
        }
        // Pas d'erreurs
        if (empty($this->_addErrorMessages)) {
            $subAF = new AF_Model_Component_SubAF_NotRepeated();
            $subAF->setAf($af);
            try {
                $subAF->setRef($ref);
            } catch (Core_Exception_User $e) {
                $this->setAddElementErrorMessage('ref', $e->getMessage());
                $this->send();
                return;
            }
            $subAF->setLabel($this->getAddElementValue('label'));
            $subAF->setFoldaway($this->getAddElementValue('foldaway'));
            $subAF->setVisible($isVisible);
            $subAF->setHelp($this->getAddElementValue('help'));
            /** @var $calledAF AF_Model_AF */
            $calledAF = AF_Model_AF::load($this->getAddElementValue('targetAF'));
            $subAF->setCalledAF($calledAF);
            $af->getRootGroup()->addSubComponent($subAF);
            $af->addComponent($subAF);

            $subAF->save();
            $af->getRootGroup()->save();
            try {
                $this->entityManager->flush();
            } catch (Core_ORM_DuplicateEntryException $e) {
                $this->setAddElementErrorMessage('ref', __('UI', 'formValidation', 'alreadyUsedIdentifier'));
                $this->send();
                return;
            }

            $this->message = __('UI', 'message', 'added');
        }
        $this->send();
    }

    /**
     * (non-PHPdoc)
     * @see UI_Controller_Datagrid::updateelementAction()
     * @Secure("editAF")
     */
    public function updateelementAction()
    {
        /** @var $subAF AF_Model_Component_SubAF_NotRepeated */
        $subAF = AF_Model_Component_SubAF_NotRepeated::load($this->update['index']);
        $newValue = $this->update['value'];
        switch ($this->update['column']) {
            case 'ref':
                $subAF->setRef($newValue);
                $this->data = $subAF->getRef();
                break;
            case 'label':
                $subAF->setLabel($newValue);
                $this->data = $subAF->getLabel();
                break;
            case 'help':
                $subAF->setHelp($newValue);
                $this->data = $this->cellLongText('af/edit_components/popup-help/id/' . $subAF->getId(),
                                                  ' af/datagrid_edit_components_sub-af/get-raw-help/id/'
                                                      . $subAF->getId(),
                                                  __('UI', 'name', 'help'),
                                                  'zoom-in');
                break;
            case 'isVisible':
                $subAF->setVisible($newValue);
                $this->data = $subAF->isVisible();
                break;
            case 'foldaway':
                $subAF->setFoldaway($newValue);
                $this->data = $subAF->getFoldaway();
                break;
            case 'targetAF':
                $subAF->setCalledAF(AF_Model_AF::load($newValue));
                $this->data = $subAF->getCalledAF()->getId();
                break;
        }
        $subAF->save();
        try {
            $this->entityManager->flush();
        } catch (Core_ORM_DuplicateEntryException $e) {
            throw new Core_Exception_User('UI', 'formValidation', 'alreadyUsedIdentifier');
        }
        $this->message = __('UI', 'message', 'updated');
        $this->send();
    }

    /**
     * (non-PHPdoc)
     * @see UI_Controller_Datagrid::deleteelementAction()
     * @Secure("editAF")
     */
    public function deleteelementAction()
    {
        /** @var $subAF AF_Model_Component_SubAF_NotRepeated */
        $subAF = AF_Model_Component_SubAF_NotRepeated::load($this->getParam('index'));
        $subAF->delete();
        $this->entityManager->flush();
        $this->message = __('UI', 'message', 'deleted');
        $this->send();
    }

    /**
     * Retourne le texte brut de l'aide
     * @Secure("editAF")
     */
    public function getRawHelpAction()
    {
        /** @var $subAF AF_Model_Component_SubAF_NotRepeated */
        $subAF = AF_Model_Component_SubAF_NotRepeated::load($this->getParam('id'));
        $this->data = $subAF->getHelp();
        $this->send();
    }

}
