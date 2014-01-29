<?php

use AF\Domain\AF\Action\SetValue;
use AF\Domain\AF\Action\SetAlgoValue;
use AF\Domain\AF\Action\SetValue\SetNumericFieldValue;
use AF\Domain\AF\Action\SetValue\SetCheckboxValue;
use AF\Domain\AF\Action\SetValue\Select\SetSelectSingleValue;
use AF\Domain\AF\AF;
use AF\Domain\AF\Action\Action;
use AF\Domain\AF\Component\NumericField;
use AF\Domain\AF\Component\Checkbox;
use AF\Domain\AF\Component\Select\SelectSingle;
use AF\Domain\AF\Condition\Condition;
use AF\Domain\AF\Component;
use Core\Annotation\Secure;

/**
 * @author matthieu.napoli
 * @author yoann.croizer
 */
class AF_Datagrid_Edit_Actions_SetValueController extends UI_Controller_Datagrid
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
        $locale = Core_Locale::loadDefault();
        //  Récupère tous les composants
        $query = new Core_Model_Query();
        $query->filter->addCondition(Component::QUERY_AF, $af);
        /** @var $components Component[] */
        $components = Component::loadList($query);
        // Affiche les actions dans l'ordre des composants
        foreach ($components as $component) {
            foreach ($component->getActions() as $action) {
                // On ne garde que les action de type setValue
                if ($action instanceof SetValue || $action instanceof SetAlgoValue) {
                    $data = [];
                    /** @var $action \AF\Domain\AF\Action\Action */
                    $data['index'] = $action->getId();
                    $condition = $action->getCondition();
                    if ($condition) {
                        $data['condition'] = $this->cellList($action->getCondition()->getId());
                    }
                    $data['targetComponent'] = $this->cellList($component->getId());
                    if ($action instanceof SetAlgoValue) {
                        $data['type'] = $this->cellList('setAlgoValue');
                    } else {
                        $data['type'] = $this->cellList('setValue');
                    }
                    // Value
                    switch (get_class($action)) {
                        case SetNumericFieldValue::class:
                            /** @var $action SetNumericFieldValue */
                            $data['value'] = $locale->formatNumber($action->getValue()->getDigitalValue());
                            if (null !== $action->getValue()->getRelativeUncertainty()) {
                                $data['value'] .= ' ± ';
                                $data['value'] .= $locale->formatInteger($action->getValue()->getRelativeUncertainty());
                                $data['value'] .= ' %';
                            }
                            break;
                        case Checkbox::class:
                            /** @var $action Checkbox */
                            if ((bool) $action->getChecked()) {
                                $data['value'] = __('UI', 'property', 'checked');
                            } else {
                                $data['value'] = __('UI', 'property', 'unchecked');
                            }
                            break;
                        case SelectSingle::class:
                            /** @var $action SelectSingle */
                            if (null !== $action->getOption()) {
                                $data['value'] = $action->getOption()->getLabel();
                            } else {
                                $data['value'] = null;
                            }
                            break;
                        case SetAlgoValue::class:
                            /** @var $action SetAlgoValue */
                            if ($action->getAlgo()) {
                                $data['value'] = $action->getAlgo()->getRef();
                            } else {
                                $data['value'] = null;
                            }
                            break;
                    }
                    $data['editValue'] = $this->cellPopup($this->_helper->url('update-action-popup',
                                                                              'edit_actions',
                                                                              'af',
                                                                              ['idAction' => $action->getId()]),
                                                          __('UI', 'verb', 'edit'),
                                                          'pencil');
                    $this->addLine($data);
                }
            }
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
        $targetComponentId = $this->getAddElementValue('targetComponent');
        if (empty($targetComponentId)) {
            $this->setAddElementErrorMessage('targetComponent', __('UI', 'formValidation', 'emptyRequiredField'));
        }
        // Pas d'erreurs
        if (empty($this->_addErrorMessages)) {
            /** @var $targetComponent Component */
            $targetComponent = Component::load($targetComponentId);
            $type = $this->getAddElementValue('type');
            /** @var $action \AF\Domain\AF\Action\Action */
            if ($type == 'setValue') {
                switch (get_class($targetComponent)) {
                    case NumericField::class:
                        $action = new SetNumericFieldValue();
                        break;
                    case Checkbox::class:
                        $action = new Checkbox();
                        break;
                    case SelectSingle::class:
                        $action = new SelectSingle();
                        break;
                    default:
                        throw new Core_Exception("Unhandled component type");
                }
            } elseif ($type == 'setAlgoValue') {
                $action = new SetAlgoValue();
            } else {
                throw new Core_Exception("Invalid action type");
            }
            // Target component
            $action->setTargetComponent($targetComponent);
            $targetComponent->addAction($action);
            // Condition
            if ($this->getAddElementValue('condition')) {
                $condition = Condition::load($this->getAddElementValue('condition'));
                $action->setCondition($condition);
            }
            $action->save();
            $targetComponent->save();
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
        /** @var $action \AF\Domain\AF\Action\Action */
        $action = Action::load($this->update['index']);
        $newValue = $this->update['value'];
        switch ($this->update['column']) {
            case 'condition':
                if ($newValue) {
                    /** @var $condition \AF\Domain\AF\Condition\Condition */
                    $condition = Condition::load($newValue);
                    $action->setCondition($condition);
                    $this->data = $this->cellList($condition->getId());
                } else {
                    $action->setCondition(null);
                }
                break;
        }
        $action->save();
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
        /** @var $action \AF\Domain\AF\Action\Action */
        $action = Action::load($this->getParam('index'));
        $action->delete();
        $action->getTargetComponent()->removeAction($action);
        $action->getTargetComponent()->save();
        $this->entityManager->flush();
        $this->message = __('UI', 'message', 'deleted');
        $this->send();
    }

}
