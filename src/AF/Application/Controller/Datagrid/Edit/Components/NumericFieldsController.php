<?php

use AF\Domain\AF;
use AF\Domain\Component\Component;
use AF\Domain\Component\NumericField;
use AF\Domain\Condition\ElementaryCondition;
use Core\Annotation\Secure;
use Unit\UnitAPI;

/**
 * Numeric fields datagrid Controller
 * @author matthieu.napoli
 * @author hugo.charbonnier
 */
class AF_Datagrid_Edit_Components_NumericFieldsController extends UI_Controller_Datagrid
{
    /**
     * @Secure("editAF")
     */
    public function getelementsAction()
    {
        /** @var $af AF */
        $af = AF::load($this->getParam('id'));
        // Filtre sur l'AF
        $this->request->filter->addCondition(Component::QUERY_AF, $af);
        /** @var $numericFields NumericField[] */
        $numericFields = NumericField::loadList($this->request);
        foreach ($numericFields as $numericField) {
            $data = [];
            $data['index'] = $numericField->getId();
            $data['label'] = $numericField->getLabel();
            $data['ref'] = $numericField->getRef();
            $data['help'] = $this->cellLongText(
                'af/edit_components/popup-help?id=' . $af->getId() . '&component=' . $numericField->getId(),
                'af/datagrid_edit_components_numeric-fields/get-raw-help?id=' . $af->getId()
                . '&component=' . $numericField->getId(),
                __('UI', 'name', 'help')
            );
            $data['isVisible'] = $numericField->isVisible();
            $data['enabled'] = $numericField->isEnabled();
            $data['required'] = $numericField->getRequired();
            $data['unit'] = $this->cellText($numericField->getUnit()->getRef(), $numericField->getUnit()->getSymbol());
            $data['unitSelection'] = $numericField->hasUnitSelection();
            $data['withUncertainty'] = $numericField->getWithUncertainty();
            $data['digitalValue'] = $this->cellNumber($numericField->getDefaultValue()->getDigitalValue());
            $data['relativeUncertainty'] = $this->cellNumber($numericField->getDefaultValue()->getRelativeUncertainty());
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
        /** @var $af AF */
        $af = AF::load($this->getParam('id'));
        $locale = Core_Locale::loadDefault();
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
            $unit = new UnitAPI($unitRef);
            $unit->getNormalizedUnit();
        } catch (Core_Exception_NotFound $e) {
            $this->setAddElementErrorMessage('unit', __('UI', 'formValidation', 'invalidUnit'));
        }
        try {
            $digitalValue = $locale->readNumber($this->getAddElementValue('digitalValue'));
        } catch(Core_Exception_InvalidArgument $e) {
            $this->setAddElementErrorMessage('digitalValue', __('UI', 'formValidation', 'invalidNumber'));
        }
        try {
            $relativeUncertainty = $locale->readInteger($this->getAddElementValue('relativeUncertainty'));
        } catch(Core_Exception_InvalidArgument $e) {
            $this->setAddElementErrorMessage('relativeUncertainty', __('UI', 'formValidation', 'invalidNumber'));
        }
        // Pas d'erreurs
        if (empty($this->_addErrorMessages)) {
            $numericField = new NumericField();
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
            $numericField->setUnitSelection($this->getAddElementValue('unitSelection'));
            $numericField->setWithUncertainty($this->getAddElementValue('withUncertainty'));
            /** @noinspection PhpUndefinedVariableInspection */
            $numericField->setUnit($unit);
            /** @noinspection PhpUndefinedVariableInspection */
            $defaultValue = new Calc_Value($digitalValue, $relativeUncertainty);
            $numericField->setDefaultValue($defaultValue);
            $numericField->setDefaultValueReminder($this->getAddElementValue('defaultValueReminder'));
            $af->addComponent($numericField);

            $numericField->save();
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
        /** @var $numericField NumericField */
        $numericField = NumericField::load($this->update['index']);
        $locale = Core_Locale::loadDefault();
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
                    if (empty($newValue)) {
                        throw new Core_Exception_User('UI', 'formValidation', 'invalidUnit');
                    }
                    $unit = new UnitAPI($newValue);
                    $unit->getNormalizedUnit();
                } catch (Core_Exception_NotFound $e) {
                    throw new Core_Exception_User('UI', 'formValidation', 'invalidUnit');
                }
                $numericField->setUnit($unit);
                $this->data = $this->cellText($numericField->getUnit()->getRef(), $numericField->getUnit()->getSymbol());
                break;
            case 'unitSelection':
                $numericField->setUnitSelection($newValue);
                $this->data = $numericField->hasUnitSelection();
                break;
            case 'withUncertainty':
                $numericField->setWithUncertainty($newValue);
                $this->data = $numericField->getWithUncertainty();
                break;
            case 'digitalValue':
                try {
                    $newValue = $locale->readNumber($newValue);
                } catch(Core_Exception_InvalidArgument $e) {
                    throw new Core_Exception_User('UI', 'formValidation', 'invalidNumber');
                }
                $value = $numericField->getDefaultValue()->copyWithNewValue($newValue);
                $numericField->setDefaultValue($value);
                $this->data = $this->cellNumber($numericField->getDefaultValue()->getDigitalValue());
                break;
            case 'relativeUncertainty':
                try {
                    $newValue = $locale->readInteger($newValue);
                } catch(Core_Exception_InvalidArgument $e) {
                    throw new Core_Exception_User('UI', 'formValidation', 'invalidNumber');
                }
                $value = $numericField->getDefaultValue()->copyWithNewUncertainty($newValue);
                $numericField->setDefaultValue($value);
                $this->data = $this->cellNumber($numericField->getDefaultValue()->getRelativeUncertainty());
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
     * @Secure("editAF")
     */
    public function deleteelementAction()
    {
        /** @var $af AF */
        $af = AF::load($this->getParam('id'));
        /** @var $field NumericField */
        $field = NumericField::load($this->getParam('index'));
        // Vérifie qu'il n'y a pas d'Algo_Condition qui référence cet input
        if ($af->hasAlgoConditionOnInput($field)) {
            throw new Core_Exception_User('AF', 'configComponentMessage', 'fieldUsedByAlgoConditionDeletionDenied');
        }
        // Supprime le champ
        $field->delete();
        $field->getGroup()->removeSubComponent($field);
        $af->removeComponent($field);
        try {
            $this->entityManager->flush();
        } catch (Core_ORM_ForeignKeyViolationException $e) {
            if ($e->isSourceEntityInstanceOf(ElementaryCondition::class)) {
                throw new Core_Exception_User(
                    'AF',
                    'configComponentMessage',
                    'fieldUsedByInteractionConditionDeletionDenied'
                );
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
        /** @var $numeric NumericField */
        $numeric = NumericField::load($this->getParam('component'));
        $this->data = $numeric->getHelp();
        $this->send();
    }
}
