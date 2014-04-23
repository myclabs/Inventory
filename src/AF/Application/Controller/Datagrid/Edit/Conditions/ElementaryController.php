<?php
/**
 * @author  matthieu.napoli
 * @author  hugo.charbonnier
 * @author  thibaud.rolland
 * @package AF
 */

use AF\Domain\AF;
use AF\Domain\Component\NumericField;
use AF\Domain\Component\Checkbox;
use AF\Domain\Component\Field;
use AF\Domain\Component\Select\SelectSingle;
use AF\Domain\Component\Select\SelectMulti;
use AF\Domain\Condition\Condition;
use AF\Domain\Condition\ElementaryCondition;
use AF\Domain\Condition\NumericFieldCondition;
use AF\Domain\Condition\CheckboxCondition;
use AF\Domain\Condition\Select\SelectSingleCondition;
use AF\Domain\Condition\Select\SelectMultiCondition;
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
        /** @var $af AF */
        $af = AF::load($this->getParam('id'));
        $query = new Core_Model_Query();
        $query->filter->addCondition(Condition::QUERY_AF, $af);
        /** @var $conditions ElementaryCondition[] */
        $conditions = ElementaryCondition::loadList($query);
        foreach ($conditions as $condition) {
            $data = [];
            $data['index'] = $condition->getId();
            $data['ref'] = $condition->getRef();
            $data['field'] = $this->cellList($condition->getField()->getId());
            if ($condition instanceof NumericFieldCondition) {
                $data['relation'] = $this->cellList($condition->getRelation());
                $data['value'] = $condition->getValue();
            } elseif ($condition instanceof CheckboxCondition) {
                $data['relation'] = $this->cellList(ElementaryCondition::RELATION_EQUAL);
                if ($condition->getValue()) {
                    $data['value'] = __('UI', 'property', 'checked');
                } else {
                    $data['value'] = __('UI', 'property', 'unchecked');
                }
            } elseif ($condition instanceof SelectSingleCondition) {
                $data['relation'] = $this->cellList($condition->getRelation());
                if ($condition->getOption()) {
                    $data['value'] = $condition->getOption()->getLabel();
                }
            } elseif ($condition instanceof SelectMultiCondition) {
                $data['relation'] = $this->cellList($condition->getRelation());
                if ($condition->getOption()) {
                    $data['value'] = $condition->getOption()->getLabel();
                }
            }
            $data['editValue'] = $this->cellPopup($this->_helper->url(
                'update-condition-popup',
                'edit_conditions',
                'af',
                ['idCondition' => $condition->getId(), 'idAF' => $af->getId()]
            ), __('UI', 'verb', 'edit'), 'pencil');
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
        $fieldId = $this->getAddElementValue('field');
        if (empty($fieldId)) {
            $this->setAddElementErrorMessage('field', __('UI', 'formValidation', 'emptyRequiredField'));
        }
        // Pas d'erreurs
        if (empty($this->_addErrorMessages)) {
            /** @var $field Field */
            $field = Field::load($fieldId);
            switch (get_class($field)) {
                case NumericField::class:
                    $condition = new NumericFieldCondition();
                    break;
                case Checkbox::class:
                    $condition = new CheckboxCondition();
                    break;
                case SelectSingle::class:
                    $condition = new SelectSingleCondition();
                    break;
                case SelectMulti::class:
                    $condition = new SelectMultiCondition();
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
        /** @var $condition \AF\Domain\Condition\ElementaryCondition */
        $condition = ElementaryCondition::load($this->update['index']);
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
        /** @var $condition Condition */
        $condition = Condition::load($this->getParam('index'));
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
