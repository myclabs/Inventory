<?php
/**
 * @author  matthieu.napoli
 * @author  hugo.charbonnier
 * @author  yoann.croizer
 * @package AF
 */

use AF\Domain\AF;
use AF\Domain\Component\Component;
use AF\Domain\Component\Select;
use AF\Domain\Component\Select\SelectOption;
use AF\Domain\Component\Select\SelectSingle;
use AF\Domain\Condition\ElementaryCondition;
use AF\Domain\Algorithm\Condition\ElementaryConditionAlgo;
use AF\Domain\Algorithm\Index\AlgoResultIndex;
use AF\Domain\Algorithm\ParameterCoordinate\AlgoParameterCoordinate;
use Core\Annotation\Secure;

class AF_Datagrid_Edit_Components_SelectSingleFieldsController extends UI_Controller_Datagrid
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
        /** @var $selectFields SelectSingle[] */
        $selectFields = SelectSingle::loadList($this->request);
        foreach ($selectFields as $selectField) {
            $data = [];
            $data['index'] = $selectField->getId();
            $data['label'] = $this->cellTranslatedText($selectField->getLabel());
            $data['ref'] = $selectField->getRef();
            $data['help'] = $this->cellLongText(
                'af/edit_components/popup-help?id=' . $af->getId() . '&component=' . $selectField->getId(),
                'af/datagrid_edit_components_select-single-fields/get-raw-help?id=' . $af->getId()
                . '&component=' . $selectField->getId(),
                __('UI', 'name', 'help')
            );
            $data['isVisible'] = $selectField->isVisible();
            $data['enabled'] = $selectField->isEnabled();
            $data['required'] = $selectField->getRequired();
            $defaultValue = $selectField->getDefaultValue();
            if (null !== $defaultValue) {
                $data['defaultValue'] = $this->cellList($defaultValue->getId());
            }
            $data['type'] = $selectField->getType();
            $data['options'] = $this->cellPopup(
                'af/edit_components/popup-select-options?idSelect=' . $selectField->getId()
                . '&idAF=' . $af->getId(),
                __('UI', 'name', 'options'),
                'zoom-in'
            );
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
        $ref = $this->getAddElementValue('ref');
        if (empty($ref)) {
            $this->setAddElementErrorMessage('ref', __('UI', 'formValidation', 'emptyRequiredField'));
        }
        $isVisible = $this->getAddElementValue('isVisible');
        if (empty($isVisible)) {
            $this->setAddElementErrorMessage('isVisible', __('UI', 'formValidation', 'emptyRequiredField'));
        }
        $type = $this->getAddElementValue('type');
        if (empty($type)) {
            $this->setAddElementErrorMessage('type', __('UI', 'formValidation', 'emptyRequiredField'));
        }
        // Pas d'erreurs
        if (empty($this->_addErrorMessages)) {
            $selectField = new SelectSingle();
            $selectField->setAf($af);
            try {
                $selectField->setRef($ref);
            } catch (Core_Exception_User $e) {
                $this->setAddElementErrorMessage('ref', $e->getMessage());
                $this->send();
                return;
            }
            $this->translationHelper->set($selectField->getLabel(), $this->getAddElementValue('label'));
            $this->translationHelper->set($selectField->getHelp(), $this->getAddElementValue('help'));
            $selectField->setVisible($isVisible);
            $selectField->setEnabled($this->getAddElementValue('enabled'));
            $selectField->setRequired($this->getAddElementValue('required'));
            $selectField->setType($type);
            $af->addComponent($selectField);

            $selectField->save();
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
        /** @var $selectField SelectSingle */
        $selectField = SelectSingle::load($this->update['index']);
        $newValue = $this->update['value'];
        switch ($this->update['column']) {
            case 'label':
                $this->translationHelper->set($selectField->getLabel(), $newValue);
                $this->data = $this->cellTranslatedText($selectField->getLabel());
                break;
            case 'ref':
                $selectField->setRef($newValue);
                $this->data = $selectField->getRef();
                break;
            case 'help':
                $this->translationHelper->set($selectField->getHelp(), $newValue);
                $this->data = null;
                break;
            case 'isVisible':
                $selectField->setVisible($newValue);
                $this->data = $selectField->isVisible();
                break;
            case 'enabled':
                $selectField->setEnabled($newValue);
                $this->data = $selectField->isEnabled();
                break;
            case 'required':
                $selectField->setRequired($newValue);
                $this->data = $selectField->getRequired();
                break;
            case 'defaultValue':
                if ($newValue) {
                    /** @var $option SelectOption */
                    $option = SelectOption::load($newValue);
                    $selectField->setDefaultValue($option);
                    $this->data = $this->cellList($selectField->getDefaultValue()->getId());
                } else {
                    $selectField->setDefaultValue(null);
                }
                break;
            case 'type':
                $selectField->setType($newValue);
                $this->data = $selectField->getType();
                break;
        }
        $selectField->save();
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
        /** @var $af AF */
        $af = AF::load($this->getParam('id'));
        /** @var $field SelectSingle */
        $field = SelectSingle::load($this->getParam('index'));
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
                throw new Core_Exception_User('AF', 'configComponentMessage',
                    'fieldUsedByInteractionConditionDeletionDenied');
            } elseif ($e->isSourceEntityInstanceOf(AlgoParameterCoordinate::class)) {
                throw new Core_Exception_User('AF', 'configComponentMessage', 'fieldUsedByIndexation');
            } elseif ($e->isSourceEntityInstanceOf(AlgoResultIndex::class)) {
                throw new Core_Exception_User('AF', 'configComponentMessage', 'fieldUsedByIndexation');
            }
            throw $e;
        }
        $this->message = __('UI', 'message', 'deleted');
        $this->send();
    }

    /**
     * Retourne la liste des options d'un select
     * @Secure("editAF")
     */
    public function getOptionListAction()
    {
        $this->addElementList(null, '');
        /** @var $select Select */
        $select = Select::load($this->getParam('index'));
        foreach ($select->getOptions() as $option) {
            $this->addElementList($option->getId(), $this->translationHelper->toString($option->getLabel()));
        }
        $this->send();
    }

    /**
     * Retourne le texte brut de l'aide
     * @Secure("editAF")
     */
    public function getRawHelpAction()
    {
        /** @var $select Select */
        $select = Select::load($this->getParam('component'));
        $this->data = $this->translationHelper->toString($select->getHelp());
        $this->send();
    }
}
