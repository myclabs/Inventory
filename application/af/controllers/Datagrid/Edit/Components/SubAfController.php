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
class AF_Datagrid_Edit_Components_SubAfController extends UI_Controller_Datagrid
{

    /**
     * (non-PHPdoc)
     * @see UI_Controller_Datagrid::getelementsAction()
     * @Secure("editAF")
     */
    public function getelementsAction()
    {
        /** @var $af AF_Model_AF */
        $af = AF_Model_AF::load($this->_getParam('id'));
        // Filtre sur l'AF
        $this->request->filter->addCondition(AF_Model_Component::QUERY_AF, $af);
        /** @var $subAFList AF_Model_Component_SubAF[] */
        $subAFList = AF_Model_Component_SubAF::loadList($this->request);
        foreach ($subAFList as $subAF) {
            $data = [];
            $data['index'] = $subAF->getId();
            $data['label'] = $subAF->getLabel();
            $data['ref'] = $subAF->getRef();
            $data['help'] = $this->cellLongText('af/edit_components/popup-help/id/' . $subAF->getId(),
                                                ' af/datagrid_edit_components_sub-af/get-raw-help/id/'
                                                    . $subAF->getId(),
                                                __('UI', 'name', 'help'),
                                                'zoom-in');
            $data['isVisible'] = $subAF->isVisible();
            $data['targetAF'] = $subAF->getCalledAF()->getId();
            $data['foldaway'] = $subAF->getFoldaway();
            if ($subAF instanceof AF_Model_Component_SubAF_Repeated) {
                switch ($subAF->getMinInputNumber()) {
                    case AF_Model_Component_SubAF_Repeated::MININPUTNUMBER_0:
                        $data['isRepeated'] = AF_Model_Component_SubAF_Repeated::REPEATED_SUB_AF_NULL;
                        break;
                    case AF_Model_Component_SubAF_Repeated::MININPUTNUMBER_1_NOT_DELETABLE:
                        $data['isRepeated'] = AF_Model_Component_SubAF_Repeated::REPEATED_SUB_AF_ONE_MANDATORY;
                        break;
                    case AF_Model_Component_SubAF_Repeated::MININPUTNUMBER_1_DELETABLE:
                        $data['isRepeated'] = AF_Model_Component_SubAF_Repeated::REPEATED_SUB_AF_ONE_DELETABLE;
                        break;
                }
                $data['hasFreeLabel'] = $subAF->getWithFreeLabel();
            } else {
                $data['isRepeated'] = AF_Model_Component_SubAF_Repeated::NOT_REPEATED_SUB_AF;
                $data['hasFreeLabel'] = 0;
            }
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
        $af = AF_Model_AF::load($this->_getParam('id'));
        $ref = $this->getAddElementValue('ref');
        if (empty($ref)) {
            $this->setAddElementErrorMessage('ref', __('UI', 'formValidation', 'emptyRequiredField'));
        }
        $isVisible = $this->getAddElementValue('isVisible');
        if (empty($isVisible)) {
            $this->setAddElementErrorMessage('isVisible', __('UI', 'formValidation', 'emptyRequiredField'));
        }
        $isRepeated = $this->getAddElementValue('isRepeated');
        if (empty($isRepeated)) {
            $this->setAddElementErrorMessage('isRepeated', __('UI', 'formValidation', 'emptyRequiredField'));
        }
        // Pas d'erreurs
        if (empty($this->_addErrorMessages)) {
            // Repeated / Not repeated
            switch ($isRepeated) {
                case AF_Model_Component_SubAF_Repeated::NOT_REPEATED_SUB_AF:
                    $subAF = new AF_Model_Component_SubAF_NotRepeated();
                    break;
                case AF_Model_Component_SubAF_Repeated::REPEATED_SUB_AF_NULL:
                    $subAF = new AF_Model_Component_SubAF_Repeated();
                    $subAF->setMinInputNumber(AF_Model_Component_SubAF_Repeated::MININPUTNUMBER_0);
                    break;
                case AF_Model_Component_SubAF_Repeated::REPEATED_SUB_AF_ONE_MANDATORY:
                    $subAF = new AF_Model_Component_SubAF_Repeated();
                    $subAF->setMinInputNumber(AF_Model_Component_SubAF_Repeated::MININPUTNUMBER_1_NOT_DELETABLE);
                    break;
                case AF_Model_Component_SubAF_Repeated::REPEATED_SUB_AF_ONE_DELETABLE:
                    $subAF = new AF_Model_Component_SubAF_Repeated();
                    $subAF->setMinInputNumber(AF_Model_Component_SubAF_Repeated::MININPUTNUMBER_1_DELETABLE);
                    break;
                default:
                    throw new Core_Exception("Type de sous-formulaire inconnu");
            }
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
            if ($subAF instanceof AF_Model_Component_SubAF_Repeated) {
                $subAF->setWithFreeLabel($this->getAddElementValue('hasFreeLabel'));
            }
            $af->getRootGroup()->addSubComponent($subAF);
            $af->addComponent($subAF);

            $subAF->save();
            $af->getRootGroup()->save();
            $entityManagers = Zend_Registry::get('EntityManagers');
            try {
                $entityManagers['default']->flush();
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
        /** @var $subAF AF_Model_Component_SubAF */
        $subAF = AF_Model_Component_SubAF::load($this->update['index']);
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
            case 'hasFreeLabel':
                if ($subAF instanceof AF_Model_Component_SubAF_Repeated) {
                    /** @var $subAF AF_Model_Component_SubAF_Repeated */
                    $subAF->setWithFreeLabel($newValue);
                } else {
                    throw new Core_Exception_User('AF', 'configComponentMessage', 'notRepeatedWithoutFreeLAbel');
                }
                break;
        }
        $subAF->save();
        $entityManagers = Zend_Registry::get('EntityManagers');
        $entityManagers['default']->flush();
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
        /** @var $subAF AF_Model_Component_SubAF */
        $subAF = AF_Model_Component_SubAF::load($this->_getParam('index'));
        $subAF->delete();
        $entityManagers = Zend_Registry::get('EntityManagers');
        $entityManagers['default']->flush();
        $this->message = __('UI', 'message', 'deleted');
        $this->send();
    }

    /**
     * Retourne le texte brut de l'aide
     * @Secure("editAF")
     */
    public function getRawHelpAction()
    {
        /** @var $subAF AF_Model_Component_SubAF */
        $subAF = AF_Model_Component_SubAF::load($this->_getParam('id'));
        $this->data = $subAF->getHelp();
        $this->send();
    }

}
