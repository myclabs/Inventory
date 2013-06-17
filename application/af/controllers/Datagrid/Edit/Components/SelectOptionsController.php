<?php
/**
 * @author  matthieu.napoli
 * @author  yoann.croizer
 * @package AF
 */

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
        /** @var $selectField AF_Model_Component_Select */
        $selectField = AF_Model_Component_Select::load($this->getParam('idSelect'));
        $options = $selectField->getOptions();
        foreach ($options as $option) {
            $data = [];
            $data['index'] = $option->getId();
            $data['label'] = $option->getLabel();
            $data['ref'] = $option->getRef();
            $data['isVisible'] = $option->isVisible();
            $data['enabled'] = $option->isEnabled();
            // Si il s'agit d'une selection multiple on précise si l'option fait partie de la séléction par défaut
            if ($selectField instanceof AF_Model_Component_Select_Multi) {
                /** @var $selectField AF_Model_Component_Select_Multi */
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
        /** @var $selectField AF_Model_Component_Select */
        $selectField = AF_Model_Component_Select::load($this->getParam('idSelect'));
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
            $option = new AF_Model_Component_Select_Option();
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
            if ($selectField instanceof AF_Model_Component_Select_Multi) {
                /** @var $selectField AF_Model_Component_Select_Multi */
                if ($this->getAddElementValue('defaultValue') == 'true') {
                    $selectField->addDefaultValue($option);
                }
            }

            $option->save();
            $selectField->save();
            $this->entityManager->flush();

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
        /** @var $option AF_Model_Component_Select_Option */
        $option = AF_Model_Component_Select_Option::load($this->update['index']);
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
                /** @var $select AF_Model_Component_Select_Multi */
                $select = AF_Model_Component_Select_Multi::load($this->getParam('idSelect'));
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
        $this->entityManager->flush();
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
        /** @var $select AF_Model_Component_Select */
        $select = AF_Model_Component_Select::load($this->getParam('idSelect'));
        /** @var $option AF_Model_Component_Select_Option */
        $option = AF_Model_Component_Select_Option::load($this->getParam('index'));
        try {
            $option->delete();
        } catch (Core_ORM_ForeignKeyViolationException $e) {
            if ($e->isSourceEntityInstanceOf('Algo_Model_Condition')) {
                throw new Core_Exception_User('AF', 'configComponentMessage',
                                              'optionUsedByAlgoConditionDeletionDenied');
            }
        }
        $select->removeOption($option);
        if ($select instanceof AF_Model_Component_Select_Single) {
            /** @var $select AF_Model_Component_Select_Single */
            if ($select->getDefaultValue() === $option) {
                $select->setDefaultValue(null);
            }
        } elseif ($select instanceof AF_Model_Component_Select_Multi) {
            /** @var $select AF_Model_Component_Select_Multi */
            if ($select->hasDefaultValue($option)) {
                $select->removeDefaultValue($option);
            }
        }
        try {
            $this->entityManager->flush();
        } catch (Core_ORM_ForeignKeyViolationException $e) {
            if ($e->isSourceEntityInstanceOf('AF_Model_Condition')) {
                throw new Core_Exception_User('AF', 'configComponentMessage',
                                              'optionUsedByInteractionConditionDeletionDenied');
            }
            throw $e;
        }
        $this->message = __('UI', 'message', 'deleted');
        $this->send();
    }

}
