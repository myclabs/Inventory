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
use AF\Domain\Component\Select\SelectMulti;
use AF\Domain\Condition\ElementaryCondition;
use AF\Domain\Algorithm\Condition\ElementaryConditionAlgo;
use Core\Annotation\Secure;

class AF_Datagrid_Edit_Components_SelectMultiFieldsController extends UI_Controller_Datagrid
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
        /** @var $selectFields SelectMulti[] */
        $selectFields = SelectMulti::loadList($this->request);
        foreach ($selectFields as $selectField) {
            $data = [];
            $data['index'] = $selectField->getId();
            $data['label'] = $selectField->getLabel();
            $data['ref'] = $selectField->getRef();
            $data['help'] = $this->cellLongText(
                'af/edit_components/popup-help?id=' . $af->getId() . '&component=' . $selectField->getId(),
                'af/datagrid_edit_components_select-fields/get-raw-help?id=' . $af->getId()
                . '&component=' . $selectField->getId(),
                __('UI', 'name', 'help')
            );
            $data['isVisible'] = $selectField->isVisible();
            $data['enabled'] = $selectField->isEnabled();
            $data['required'] = $selectField->getRequired();
            $data['type'] = $selectField->getType();
            $data['options'] = $this->cellPopup('af/edit_components/popup-select-options/idSelect/'
                                                    . $selectField->getId(),
                                                __('UI', 'name', 'options'), 'zoom-in');
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
            $selectField = new SelectMulti();
            $selectField->setAf($af);
            try {
                $selectField->setRef($ref);
            } catch (Core_Exception_User $e) {
                $this->setAddElementErrorMessage('ref', $e->getMessage());
                $this->send();
                return;
            }
            $selectField->setLabel($this->getAddElementValue('label'));
            $selectField->setVisible($isVisible);
            $selectField->setHelp($this->getAddElementValue('help'));
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
        /** @var $selectField SelectMulti */
        $selectField = SelectMulti::load($this->update['index']);
        $newValue = $this->update['value'];
        switch ($this->update['column']) {
            case 'label':
                $selectField->setLabel($newValue);
                $this->data = $selectField->getLabel();
                break;
            case 'ref':
                $selectField->setRef($newValue);
                $this->data = $selectField->getRef();
                break;
            case 'help':
                $selectField->setHelp($newValue);
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
        /** @var $field SelectMulti */
        $field = SelectMulti::load($this->getParam('index'));
        // Vérifie qu'il n'y a pas d'Algo_Condition qui référence cet input
        $query = new Core_Model_Query();
        $query->filter->addCondition(ElementaryConditionAlgo::QUERY_INPUT_REF, $field->getRef());
        $algoConditions = ElementaryConditionAlgo::loadList($query);
        if (count($algoConditions) > 0) {
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
        /** @var $select Select */
        $select = Select::load($this->getParam('component'));
        $this->data = $select->getHelp();
        $this->send();
    }
}
