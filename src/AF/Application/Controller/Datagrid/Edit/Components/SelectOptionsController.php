<?php
/**
 * @author  matthieu.napoli
 * @author  yoann.croizer
 * @package AF
 */

use AF\Domain\AF\Component\Select;
use AF\Domain\AF\Component\Select\SelectOption;
use AF\Domain\AF\Component\Select\SelectSingle;
use AF\Domain\AF\Component\Select\SelectMulti;
use AF\Domain\AF\Condition\Condition;
use AF\Domain\Algorithm\Condition\ConditionAlgo;
use Core\Annotation\Secure;

/**
 * Permet de gérer les options d'un champ de sélection
 * @package AF
 */
class AF_Datagrid_Edit_Components_SelectOptionsController extends UI_Controller_Datagrid
{

    /**
     * (non-PHPdoc)
     * @see UI_Controller_Datagrid::getelementsAction()
     * @Secure("editAF")
     */
    public function getelementsAction()
    {
        /** @var $selectField Select */
        $selectField = Select::load($this->getParam('idSelect'));
        $options = $selectField->getOptions();
        foreach ($options as $option) {
            $data = [];
            $data['index'] = $option->getId();
            $data['label'] = $option->getLabel();
            $data['ref'] = $option->getRef();
            $data['isVisible'] = $option->isVisible();
            $data['enabled'] = $option->isEnabled();
            // Si il s'agit d'une selection multiple on précise si l'option fait partie de la séléction par défaut
            if ($selectField instanceof SelectMulti) {
                /** @var $selectField SelectMulti */
                $data['defaultValue'] = $selectField->hasDefaultValue($option);
            }
            $canMoveUp = ($option->getPosition() > 1);
            $canMoveDown = ($option->getPosition() < $option->getLastEligiblePosition());
            $data['order'] = $this->cellPosition($option->getPosition(), $canMoveUp, $canMoveDown);
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
        /** @var $selectField Select */
        $selectField = Select::load($this->getParam('idSelect'));
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
            $option = new SelectOption();
            try {
                $option->setRef($ref);
            } catch (Core_Exception_User $e) {
                $this->setAddElementErrorMessage('ref', $e->getMessage());
                $this->send();
                return;
            }
            $option->setLabel($this->getAddElementValue('label'));
            $option->setVisible($isVisible);
            $option->setEnabled($this->getAddElementValue('enabled'));

            $selectField->addOption($option);
            if ($selectField instanceof SelectMulti) {
                /** @var $selectField SelectMulti */
                if ($this->getAddElementValue('defaultValue') == 'true') {
                    $selectField->addDefaultValue($option);
                }
            }

            $option->save();
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
        /** @var $option SelectOption */
        $option = SelectOption::load($this->update['index']);
        $newValue = $this->update['value'];
        switch ($this->update['column']) {
            case 'label':
                $option->setLabel($newValue);
                $this->data = $option->getLabel();
                break;
            case 'ref':
                $option->setRef($newValue);
                $this->data = $option->getRef();
                break;
            case 'isVisible':
                $option->setVisible($newValue);
                $this->data = $option->isVisible();
                break;
            case 'enabled':
                $option->setEnabled($newValue);
                $this->data = $option->isEnabled();
                break;
            // Ce cas peut se produire uniquement avec les champs de selection multiple
            case 'defaultValue':
                /** @var $select SelectMulti */
                $select = SelectMulti::load($this->getParam('idSelect'));
                if ($newValue) {
                    $select->addDefaultValue($option);
                } else {
                    $select->removeDefaultValue($option);
                }
                $select->save();
                $this->data = $select->hasDefaultValue($option);
                break;
            case 'order':
                $oldPosition = $option->getPosition();
                switch ($newValue) {
                    case 'goFirst':
                        $newPosition = 1;
                        break;
                    case 'goUp':
                        $newPosition = $oldPosition - 1;
                        break;
                    case 'goDown':
                        $newPosition = $oldPosition + 1;
                        break;
                    case 'goLast':
                        $newPosition = $option->getLastEligiblePosition();
                        break;
                    default:
                        $newPosition = $newValue;
                        break;
                }
                $option->setPosition($newPosition);
                break;
        }
        $option->save();
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
        /** @var $select Select */
        $select = Select::load($this->getParam('idSelect'));
        /** @var $option SelectOption */
        $option = SelectOption::load($this->getParam('index'));
        try {
            $option->delete();
        } catch (Core_ORM_ForeignKeyViolationException $e) {
            if ($e->isSourceEntityInstanceOf(ConditionAlgo::class)) {
                throw new Core_Exception_User('AF', 'configComponentMessage',
                                              'optionUsedByAlgoConditionDeletionDenied');
            }
        }
        $select->removeOption($option);
        if ($select instanceof SelectSingle) {
            /** @var $select SelectSingle */
            if ($select->getDefaultValue() === $option) {
                $select->setDefaultValue(null);
            }
        } elseif ($select instanceof SelectMulti) {
            /** @var $select SelectMulti */
            if ($select->hasDefaultValue($option)) {
                $select->removeDefaultValue($option);
            }
        }
        try {
            $this->entityManager->flush();
        } catch (Core_ORM_ForeignKeyViolationException $e) {
            if ($e->isSourceEntityInstanceOf(Condition::class)) {
                throw new Core_Exception_User('AF', 'configComponentMessage',
                                              'optionUsedByInteractionConditionDeletionDenied');
            }
            throw $e;
        }
        $this->message = __('UI', 'message', 'deleted');
        $this->send();
    }

}
