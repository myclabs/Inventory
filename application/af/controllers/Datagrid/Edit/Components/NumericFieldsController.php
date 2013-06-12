<?php
/**
 * @author  matthieu.napoli
 * @author  hugo.charbonnier
 * @package AF
 */

use Core\Annotation\Secure;

/**
 * Numeric fields datagrid Controller
 * @package AF
 */
class AF_Datagrid_Edit_Components_NumericFieldsController extends UI_Controller_Datagrid
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
        /** @var $numericFields AF_Model_Component_Numeric[] */
        $numericFields = AF_Model_Component_Numeric::loadList($this->request);
        foreach ($numericFields as $numericField) {
            $data = [];
            $data['index'] = $numericField->getId();
            $data['label'] = $numericField->getLabel();
            $data['ref'] = $numericField->getRef();
            $data['help'] = $this->cellLongText('af/edit_components/popup-help/id/' . $numericField->getId(),
                                                ' af/datagrid_edit_components_numeric-fields/get-raw-help/id/'
                                                    . $numericField->getId(),
                                                __('UI', 'name', 'help'),
                                                'zoom-in');
            $data['isVisible'] = $numericField->isVisible();
            $data['enabled'] = $numericField->isEnabled();
            $data['required'] = $numericField->getRequired();
            $data['unit'] = $numericField->getUnit()->getRef();
            $data['withUncertainty'] = $numericField->getWithUncertainty();
            $data['digitalValue'] = $numericField->getDefaultValue()->digitalValue;
            $data['relativeUncertainty'] = $numericField->getDefaultValue()->relativeUncertainty;
            $data['defaultValueReminder'] = $numericField->getDefaultValueReminder();
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
        try {
            $unitRef = $this->getAddElementValue('unit');
            if (empty($unitRef)) {
                $this->setAddElementErrorMessage('unit', __('UI', 'formValidation', 'invalidUnit'));
            }
            $unit = new Unit_API($unitRef);
            $unit->getNormalizedUnit();
        } catch (Core_Exception_NotFound $e) {
            $this->setAddElementErrorMessage('unit', __('UI', 'formValidation', 'invalidUnit'));
        }
        // Pas d'erreurs
        if (empty($this->_addErrorMessages)) {
            $numericField = new AF_Model_Component_Numeric();
            $numericField->setAf($af);
            try {
                $numericField->setRef($ref);
            } catch (Core_Exception_User $e) {
                $this->setAddElementErrorMessage('ref', $e->getMessage());
                $this->send();
                return;
            }
            $numericField->setLabel($this->getAddElementValue('label'));
            $numericField->setVisible($isVisible);
            $numericField->setHelp($this->getAddElementValue('help'));
            $numericField->setEnabled($this->getAddElementValue('enabled'));
            $numericField->setRequired($this->getAddElementValue('required'));
            $numericField->setWithUncertainty($this->getAddElementValue('withUncertainty'));
            /** @noinspection PhpUndefinedVariableInspection */
            $numericField->setUnit($unit);
            $value = new Calc_Value();
            $value->digitalValue = $this->getAddElementValue('digitalValue');
            $value->relativeUncertainty = $this->getAddElementValue('relativeUncertainty');
            $numericField->setDefaultValue($value);
            $numericField->setDefaultValueReminder($this->getAddElementValue('defaultValueReminder'));
            $af->getRootGroup()->addSubComponent($numericField);
            $af->addComponent($numericField);

            $numericField->save();
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
        /** @var $numericField AF_Model_Component_Numeric */
        $numericField = AF_Model_Component_Numeric::load($this->update['index']);
        $newValue = $this->update['value'];
        switch ($this->update['column']) {
            case 'label':
                $numericField->setLabel($newValue);
                $this->data = $numericField->getLabel();
                break;
            case 'ref':
                $numericField->setRef($newValue);
                $this->data = $numericField->getRef();
                break;
            case 'help':
                $numericField->setHelp($newValue);
                $this->data = $this->cellLongText('af/edit_components/popup-help/id/' . $numericField->getId(),
                                                  ' af/datagrid_edit_components_numeric-fields/get-raw-help/id/'
                                                      . $numericField->getId(),
                                                  __('UI', 'name', 'help'),
                                                  'zoom-in');
                break;
            case 'isVisible':
                $numericField->setVisible($newValue);
                $this->data = $numericField->isVisible();
                break;
            case 'enabled':
                $numericField->setEnabled($newValue);
                $this->data = $numericField->isEnabled();
                break;
            case 'required':
                $numericField->setRequired($newValue);
                $this->data = $numericField->getRequired();
                break;
            case 'unit':
                try {
                    $unit = new Unit_API($newValue);
                    $unit->getNormalizedUnit();
                } catch (Core_Exception_NotFound $e) {
                    throw new Core_Exception_User('UI', 'formValidation', 'invalidUnit');
                }
                $numericField->setUnit($unit);
                $this->data = $numericField->getUnit()->getRef();
                break;
            case 'withUncertainty':
                $numericField->setWithUncertainty($newValue);
                $this->data = $numericField->getWithUncertainty();
                break;
            case 'digitalValue':
                $value = clone $numericField->getDefaultValue();
                $value->digitalValue = $newValue;
                $numericField->setDefaultValue($value);
                $this->data = $numericField->getDefaultValue()->digitalValue;
                break;
            case 'relativeUncertainty':
                $value = clone $numericField->getDefaultValue();
                $value->relativeUncertainty = $newValue;
                $numericField->setDefaultValue($value);
                $this->data = $numericField->getDefaultValue()->relativeUncertainty;
                break;
            case 'defaultValueReminder':
                $numericField->setDefaultValueReminder($newValue);
                $this->data = $numericField->getDefaultValueReminder();
                break;
        }
        $numericField->save();
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
        /** @var $af AF_Model_AF */
        $af = AF_Model_AF::load($this->getParam('id'));
        /** @var $field AF_Model_Component_Numeric */
        $field = AF_Model_Component_Numeric::load($this->getParam('index'));
        // Vérifie qu'il n'y a pas d'Algo_Condition qui référence cet input
        $query = new Core_Model_Query();
        $query->filter->addCondition(Algo_Model_Condition_Elementary::QUERY_INPUT_REF, $field->getRef());
        $algoConditions = Algo_Model_Condition_Elementary::loadList($query);
        if (count($algoConditions) > 0) {
            throw new Core_Exception_User('AF', 'configComponentMessage', 'fieldUsedByAlgoConditionDeletionDenied');
        }
        // Supprime le champ
        $field->delete();
        $field->getGroup()->removeSubComponent($field);
        $af->removeComponent($field);
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
        /** @var $numeric AF_Model_Component_Numeric */
        $numeric = AF_Model_Component_Numeric::load($this->getParam('id'));
        $this->data = $numeric->getHelp();
        $this->send();
    }

}
