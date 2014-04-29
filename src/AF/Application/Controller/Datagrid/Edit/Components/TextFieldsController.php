<?php
/**
 * @author  matthieu.napoli
 * @package AF
 */

use AF\Domain\AF;
use AF\Domain\Component\Component;
use AF\Domain\Component\TextField;
use AF\Domain\Condition\ElementaryCondition;
use Core\Annotation\Secure;

/**
 * Text fields datagrid Controller
 * @package AF
 */
class AF_Datagrid_Edit_Components_TextFieldsController extends UI_Controller_Datagrid
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
        /** @var $textFields TextField[] */
        $textFields = TextField::loadList($this->request);
        foreach ($textFields as $field) {
            $data = [];
            $data['index'] = $field->getId();
            $data['label'] = $this->cellTranslatedText($field->getLabel());
            $data['ref'] = $field->getRef();
            $data['help'] = $this->cellLongText(
                'af/edit_components/popup-help?id=' . $af->getId() . '&component=' . $field->getId(),
                'af/datagrid_edit_components_text-fields/get-raw-help?id=' . $af->getId()
                . '&component=' . $field->getId(),
                __('UI', 'name', 'help')
            );
            $data['isVisible'] = $field->isVisible();
            $data['enabled'] = $field->isEnabled();
            $data['required'] = $field->getRequired();
            $data['type'] = $field->getType();
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
        $type = $this->getAddElementValue('type');
        if (empty($type)) {
            $this->setAddElementErrorMessage('type', __('UI', 'formValidation', 'emptyRequiredField'));
        }
        // Pas d'erreurs
        if (empty($this->_addErrorMessages)) {
            $field = new TextField($type);
            $field->setAf($af);
            try {
                $field->setRef($ref);
            } catch (Core_Exception_User $e) {
                $this->setAddElementErrorMessage('ref', $e->getMessage());
                $this->send();
                return;
            }
            $this->translationHelper->set($field->getLabel(), $this->getAddElementValue('label'));
            $this->translationHelper->set($field->getHelp(), $this->getAddElementValue('help'));
            $field->setVisible($isVisible);
            $field->setEnabled($this->getAddElementValue('enabled'));
            $field->setRequired($this->getAddElementValue('required'));
            $af->addComponent($field);

            $field->save();
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
        /** @var $field TextField */
        $field = TextField::load($this->update['index']);
        $newValue = $this->update['value'];
        switch ($this->update['column']) {
            case 'label':
                $this->translationHelper->set($field->getLabel(), $newValue);
                $this->data = $this->cellTranslatedText($field->getLabel());
                break;
            case 'ref':
                $field->setRef($newValue);
                $this->data = $field->getRef();
                break;
            case 'help':
                $this->translationHelper->set($field->getHelp(), $newValue);
                $this->data = null;
                break;
            case 'isVisible':
                $field->setVisible($newValue);
                $this->data = $field->isVisible();
                break;
            case 'enabled':
                $field->setEnabled($newValue);
                $this->data = $field->isEnabled();
                break;
            case 'required':
                $field->setRequired($newValue);
                $this->data = $field->getRequired();
                break;
            case 'type':
                $field->setType($newValue);
                $this->data = $field->getType();
                break;
        }
        $field->save();
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
        /** @var $field TextField */
        $field = TextField::load($this->getParam('index'));
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
        /** @var $numeric TextField */
        $numeric = TextField::load($this->getParam('component'));
        $this->data = $this->translationHelper->toString($numeric->getHelp());
        $this->send();
    }
}
