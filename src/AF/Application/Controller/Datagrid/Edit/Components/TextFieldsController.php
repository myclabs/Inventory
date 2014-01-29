<?php
/**
 * @author  matthieu.napoli
 * @package AF
 */

use AF\Domain\AF\AF;
use AF\Domain\AF\Component\Component;
use AF\Domain\AF\Component\TextField;
use AF\Domain\AF\Condition\ElementaryCondition;
use AF\Domain\Algorithm\Condition\ElementaryConditionAlgo;
use Core\Annotation\Secure;

/**
 * Text fields datagrid Controller
 * @package AF
 */
class AF_Datagrid_Edit_Components_TextFieldsController extends UI_Controller_Datagrid
{

    /**
     * (non-PHPdoc)
     * @see UI_Controller_Datagrid::getelementsAction()
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
            $data['label'] = $field->getLabel();
            $data['ref'] = $field->getRef();
            $data['help'] = $this->cellLongText('af/edit_components/popup-help/id/' . $field->getId(),
                                                ' af/datagrid_edit_components_text-fields/get-raw-help/id/'
                                                    . $field->getId(),
                                                __('UI', 'name', 'help'),
                                                'zoom-in');
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
            $field->setLabel($this->getAddElementValue('label'));
            $field->setVisible($isVisible);
            $field->setHelp($this->getAddElementValue('help'));
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
                $field->setLabel($newValue);
                $this->data = $field->getLabel();
                break;
            case 'ref':
                $field->setRef($newValue);
                $this->data = $field->getRef();
                break;
            case 'help':
                $field->setHelp($newValue);
                $this->data = $this->cellLongText('af/edit_components/popup-help/id/' . $field->getId(),
                                                  ' af/datagrid_edit_components_text-fields/get-raw-help/id/'
                                                      . $field->getId(),
                                                  __('UI', 'name', 'help'),
                                                  'zoom-in');
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
     * (non-PHPdoc)
     * @see UI_Controller_Datagrid::deleteelementAction()
     * @Secure("editAF")
     */
    public function deleteelementAction()
    {
        /** @var $af AF */
        $af = AF::load($this->getParam('id'));
        /** @var $field TextField */
        $field = TextField::load($this->getParam('index'));
        // Vérifie qu'il n'y a pas d'Algo_Condition qui référence cet input
        $query = new Core_Model_Query();
        $query->filter->addCondition(ElementaryConditionAlgo::QUERY_INPUT_REF, $field->getRef());
        $algoConditions = ElementaryConditionAlgo::loadList($query);
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
        $numeric = TextField::load($this->getParam('id'));
        $this->data = $numeric->getHelp();
        $this->send();
    }

}
