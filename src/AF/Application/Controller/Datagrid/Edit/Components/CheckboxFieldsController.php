<?php

use AF\Domain\AF;
use AF\Domain\Component\Component;
use AF\Domain\Component\Checkbox;
use AF\Domain\Condition\ElementaryCondition;
use AF\Domain\Algorithm\Condition\ElementaryConditionAlgo;
use Core\Annotation\Secure;

/**
 * Checkbox fields datagrid Controller
 * @author matthieu.napoli
 * @author hugo.charbonnier
 */
class AF_Datagrid_Edit_Components_CheckboxFieldsController extends UI_Controller_Datagrid
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
        /** @var $checkboxFields Checkbox[] */
        $checkboxFields = Checkbox::loadList($this->request);
        foreach ($checkboxFields as $checkboxField) {
            $data = [];
            $data['index'] = $checkboxField->getId();
            $data['label'] = $this->cellTranslatedText($checkboxField->getLabel());
            $data['ref'] = $checkboxField->getRef();
            $data['help'] = $this->cellLongText(
                'af/edit_components/popup-help?id=' . $af->getId() . '&component=' . $checkboxField->getId(),
                'af/datagrid_edit_components_checkbox-fields/get-raw-help?id=' . $af->getId()
                . '&component=' . $checkboxField->getId(),
                __('UI', 'name', 'help')
            );
            $data['isVisible'] = $checkboxField->isVisible();
            $data['enabled'] = $checkboxField->isEnabled();
            $data['defaultValue'] = $checkboxField->getDefaultValue();
            $this->addLine($data);
        }
        $this->send();
    }

    /**
     * @Secure("editAF")
     */
    public function addelementAction()
    {
        /** @var $af \AF\Domain\AF */
        $af = AF::load($this->getParam('id'));
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
            $checkboxField = new Checkbox();
            $checkboxField->setAf($af);
            try {
                $checkboxField->setRef($ref);
            } catch (Core_Exception_User $e) {
                $this->setAddElementErrorMessage('ref', $e->getMessage());
                $this->send();
                return;
            }
            $this->translationHelper->set($checkboxField->getLabel(), $this->getAddElementValue('label'));
            $this->translationHelper->set($checkboxField->getHelp(), $this->getAddElementValue('help'));
            $checkboxField->setVisible($isVisible);
            $checkboxField->setEnabled($this->getAddElementValue('enabled'));
            if ($this->getAddElementValue('defaultValue') == 'true') {
                $checkboxField->setDefaultValue(true);
            } else {
                $checkboxField->setDefaultValue(false);
            }
            $af->addComponent($checkboxField);

            $checkboxField->save();
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
     * @Secure("editAF")
     */
    public function updateelementAction()
    {
        /** @var $af \AF\Domain\AF */
        $af = AF::load($this->getParam('id'));
        /** @var $checkboxField Checkbox */
        $checkboxField = Checkbox::load($this->update['index']);
        $newValue = $this->update['value'];
        switch ($this->update['column']) {
            case 'ref':
                $checkboxField->setRef($newValue);
                $this->data = $checkboxField->getRef();
                break;
            case 'label':
                $this->translationHelper->set($checkboxField->getLabel(), $newValue);
                $this->data = $this->cellTranslatedText($checkboxField->getLabel());
                break;
            case 'help':
                $this->translationHelper->set($checkboxField->getHelp(), $newValue);
                $this->data = null;
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
     * @Secure("editAF")
     */
    public function deleteelementAction()
    {
        /** @var $af AF */
        $af = AF::load($this->getParam('id'));
        /** @var $field Checkbox */
        $field = Checkbox::load($this->getParam('index'));
        // Vérifie qu'il n'y a pas d'Algo_Condition qui référence cet input
        if ($af->hasAlgoConditionOnInput($field)) {
            throw new Core_Exception_User('AF', 'configComponentMessage', 'fieldUsedByAlgoConditionDeletionDenied');
        }
        // Supprime le champ
        $field->delete();
        try {
            $this->entityManager->flush();
        } catch (Core_ORM_ForeignKeyViolationException $e) {
            if ($e->isSourceEntityInstanceOf(ElementaryCondition::class)) {
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
        /** @var $checkboxField Checkbox */
        $checkboxField = Checkbox::load($this->getParam('component'));
        $this->data = $this->translationHelper->toString($checkboxField->getHelp());
        $this->send();
    }
}
