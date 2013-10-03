<?php
/**
 * @author  matthieu.napoli
 * @author  hugo.charbonnier
 * @author  thibaud.rolland
 * @package AF
 */

use Core\Annotation\Secure;
use Doctrine\DBAL\DBALException;

/**
 * Conditions Controller
 * @package AF
 */
class AF_Datagrid_Edit_Conditions_ElementaryController extends UI_Controller_Datagrid
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
        $query = new Core_Model_Query();
        $query->filter->addCondition(AF_Model_Condition::QUERY_AF, $af);
        /** @var $conditions AF_Model_Condition_Elementary[] */
        $conditions = AF_Model_Condition_Elementary::loadList($query);
        foreach ($conditions as $condition) {
            $data = [];
            $data['index'] = $condition->getId();
            $data['ref'] = $condition->getRef();
            $data['field'] = $this->cellList($condition->getField()->getId());
            if ($condition instanceof AF_Model_Condition_Elementary_Numeric) {
                $data['relation'] = $this->cellList($condition->getRelation());
                $data['value'] = $condition->getValue();
            } elseif ($condition instanceof AF_Model_Condition_Elementary_Checkbox) {
                $data['relation'] = $this->cellList(AF_Model_Condition_Elementary::RELATION_EQUAL);
                if ($condition->getValue()) {
                    $data['value'] = __('UI', 'property', 'checked');
                } else {
                    $data['value'] = __('UI', 'property', 'unchecked');
                }
            } elseif ($condition instanceof AF_Model_Condition_Elementary_Select_Single) {
                $data['relation'] = $this->cellList($condition->getRelation());
                if ($condition->getOption()) {
                    $data['value'] = $condition->getOption()->getLabel();
                }
            } elseif ($condition instanceof AF_Model_Condition_Elementary_Select_Multi) {
                $data['relation'] = $this->cellList($condition->getRelation());
                if ($condition->getOption()) {
                    $data['value'] = $condition->getOption()->getLabel();
                }
            }
            $data['editValue'] = $this->cellPopup($this->_helper->url('update-condition-popup',
                                                                      'edit_conditions',
                                                                      'af',
                                                                      ['idCondition' => $condition->getId()]),
                                                  __('UI', 'verb', 'edit'),
                                                  'pencil');
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
        $fieldId = $this->getAddElementValue('field');
        if (empty($fieldId)) {
            $this->setAddElementErrorMessage('field', __('UI', 'formValidation', 'emptyRequiredField'));
        }
        // Pas d'erreurs
        if (empty($this->_addErrorMessages)) {
            /** @var $field AF_Model_Component_Field */
            $field = AF_Model_Component_Field::load($fieldId);
            switch (get_class($field)) {
                case 'AF_Model_Component_Numeric':
                    $condition = new AF_Model_Condition_Elementary_Numeric();
                    break;
                case 'AF_Model_Component_Checkbox':
                    $condition = new AF_Model_Condition_Elementary_Checkbox();
                    break;
                case 'AF_Model_Component_Select_Single':
                    $condition = new AF_Model_Condition_Elementary_Select_Single();
                    break;
                case 'AF_Model_Component_Select_Multi':
                    $condition = new AF_Model_Condition_Elementary_Select_Multi();
                    break;
                default:
                    throw new Core_Exception("Unhandled field type");
            }
            try {
                $condition->setRef($ref);
            } catch (Core_Exception_User $e) {
                $this->setAddElementErrorMessage('ref', $e->getMessage());
                $this->send();
                return;
            }
            $condition->setField($field);
            $condition->setAf($af);
            $condition->save();
            $af->addCondition($condition);
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
        /** @var $condition AF_Model_Condition_Elementary */
        $condition = AF_Model_Condition_Elementary::load($this->update['index']);
        $newValue = $this->update['value'];
        switch ($this->update['column']) {
            case 'ref':
                $condition->setRef($newValue);
                $this->data = $condition->getRef();
                break;
        }
        $condition->save();
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
        /** @var $condition AF_Model_Condition */
        $condition = AF_Model_Condition::load($this->getParam('index'));
        try {
            $condition->delete();
            $this->entityManager->flush();
            $this->message = __('UI', 'message', 'deleted');
        } catch (DBALException $e) {
            throw new Core_Exception_User('AF', 'configInteractionMessage', 'conditionUsedByActionDeletionDenied');
        }
        $this->message = __('UI', 'message', 'deleted');
        $this->send();
    }

}
