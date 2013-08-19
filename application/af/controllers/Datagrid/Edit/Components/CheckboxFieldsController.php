<?php
/**
 * @author  matthieu.napoli
 * @author  hugo.charbonnier
 * @package AF
 */

use Core\Annotation\Secure;

/**
 * Checkbox fields datagrid Controller
 * @package AF
 */
class AF_Datagrid_Edit_Components_CheckboxFieldsController extends UI_Controller_Datagrid
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
        /** @var $checkboxFields AF_Model_Component_Checkbox[] */
        $checkboxFields = AF_Model_Component_Checkbox::loadList($this->request);
        foreach ($checkboxFields as $checkboxField) {
            $data = [];
            $data['index'] = $checkboxField->getId();
            $data['label'] = $checkboxField->getLabel();
            $data['ref'] = $checkboxField->getRef();
            $data['help'] = $this->cellLongText('af/edit_components/popup-help/id/' . $checkboxField->getId(),
                                                ' af/datagrid_edit_components_checkbox-fields/get-raw-help/id/'
                                                    . $checkboxField->getId(),
                                                __('UI', 'name', 'help'),
                                                'zoom-in');
            $data['isVisible'] = $checkboxField->isVisible();
            $data['enabled'] = $checkboxField->isEnabled();
            $data['defaultValue'] = $checkboxField->getDefaultValue();
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
            $checkboxField = new AF_Model_Component_Checkbox();
            $checkboxField->setAf($af);
            try {
                $checkboxField->setRef($ref);
            } catch (Core_Exception_User $e) {
                $this->setAddElementErrorMessage('ref', $e->getMessage());
                $this->send();
                return;
            }
            $checkboxField->setLabel($this->getAddElementValue('label'));
            $checkboxField->setVisible($isVisible);
            $checkboxField->setHelp($this->getAddElementValue('help'));
            $checkboxField->setEnabled($this->getAddElementValue('enabled'));
            if ($this->getAddElementValue('defaultValue') == 'true') {
                $checkboxField->setDefaultValue(true);
            } else {
                $checkboxField->setDefaultValue(false);
            }
            $af->getRootGroup()->addSubComponent($checkboxField);
            $af->addComponent($checkboxField);

            $checkboxField->save();
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
        /** @var $checkboxField AF_Model_Component_Checkbox */
        $checkboxField = AF_Model_Component_Checkbox::load($this->update['index']);
        $newValue = $this->update['value'];
        switch ($this->update['column']) {
            case 'ref':
                $checkboxField->setRef($newValue);
                $this->data = $checkboxField->getRef();
                break;
            case 'label':
                $checkboxField->setLabel($newValue);
                $this->data = $checkboxField->getLabel();
                break;
            case 'help':
                $checkboxField->setHelp($newValue);
                $this->data = $this->cellLongText('af/edit_components/popup-help/id/' . $checkboxField->getId(),
                                                  ' af/datagrid_edit_components_checkbox-fields/get-raw-help/id/'
                                                      . $checkboxField->getId(),
                                                  __('UI', 'name', 'help'),
                                                  'zoom-in');
                break;
            case 'isVisible':
                $checkboxField->setVisible($newValue);
                $this->data = $checkboxField->isVisible();
                break;
            case 'enabled':
                $checkboxField->setEnabled($newValue);
                $this->data = $checkboxField->isEnabled();
                break;
            case 'defaultValue':
                $checkboxField->setDefaultValue($newValue);
                $this->data = $checkboxField->getDefaultValue();
                break;
        }
        $checkboxField->save();
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
        /** @var $field AF_Model_Component_Checkbox */
        $field = AF_Model_Component_Checkbox::load($this->getParam('index'));
        // Vérifie qu'il n'y a pas d'Algo_Condition qui référence cet input
        $query = new Core_Model_Query();
        $query->filter->addCondition(Algo_Model_Condition_Elementary::QUERY_INPUT_REF, $field->getRef());
        $algoConditions = Algo_Model_Condition_Elementary::loadList($query);
        if (count($algoConditions) > 0) {
            throw new Core_Exception_User('AF', 'configComponentMessage', 'fieldUsedByAlgoConditionDeletionDenied');
        }
        // Supprime le champ
        $field->delete();
        try {
            $this->entityManager->flush();
        } catch (Core_ORM_ForeignKeyViolationException $e) {
            if ($e->isSourceEntityInstanceOf('AF_Model_Condition_Elementary')) {
                throw new Core_Exception_User('AF', 'configComponentMessage',
                                              'fieldUsedByInteractionConditionDeletionDenied');
            }
            throw $e;
        }
        $this->message = __('UI', 'message', 'deleted');
        $this->send();
    }

    /**
     * Retourne le texte brut de l'aide
     * @Secure("editAF")
     */
    public function getRawHelpAction()
    {
        /** @var $checkboxField AF_Model_Component_Checkbox */
        $checkboxField = AF_Model_Component_Checkbox::load($this->getParam('id'));
        $this->data = $checkboxField->getHelp();
        $this->send();
    }

}
