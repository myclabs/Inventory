<?php
/**
 * @author  matthieu.napoli
 * @author  yoann.croizer
 * @package AF
 */

use Core\Annotation\Secure;

/**
 * @package AF
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
        /** @var $af AF_Model_AF */
        $af = AF_Model_AF::load($this->_getParam('id'));
        //  Récupère tous les composants
        $query = new Core_Model_Query();
        $query->filter->addCondition(AF_Model_Component::QUERY_AF, $af);
        /** @var $components AF_Model_Component[] */
        $components = AF_Model_Component::loadList($query);
        // Affiche les actions dans l'ordre des composants
        foreach ($components as $component) {
            foreach ($component->getActions() as $action) {
                // On ne garde que les action de type setValue
                if ($action instanceof AF_Model_Action_SetValue || $action instanceof AF_Model_Action_SetAlgoValue) {
                    $data = [];
                    /** @var $action AF_Model_Action */
                    $data['index'] = $action->getId();
                    $condition = $action->getCondition();
                    if ($condition) {
                        $data['condition'] = $this->cellList($action->getCondition()->getId());
                    }
                    $data['targetComponent'] = $this->cellList($component->getId());
                    if ($action instanceof AF_Model_Action_SetAlgoValue) {
                        $data['type'] = $this->cellList('setAlgoValue');
                    } else {
                        $data['type'] = $this->cellList('setValue');
                    }
                    // Value
                    switch (get_class($action)) {
                        case 'AF_Model_Action_SetValue_Numeric':
                            /** @var $action AF_Model_Action_SetValue_Numeric */
                            $data['value'] = $action->getValue()->digitalValue;
                            if (null !== $action->getValue()->relativeUncertainty) {
                                $data['value'] .= ' &#177; '; // Symbole +-
                                $data['value'] .= $action->getValue()->relativeUncertainty;
                                $data['value'] .= ' %';
                            }
                            break;
                        case 'AF_Model_Action_SetValue_Checkbox':
                            /** @var $action AF_Model_Action_SetValue_Checkbox */
                            if ((bool) $action->getChecked()) {
                                $data['value'] = __('UI', 'property', 'checked');
                            } else {
                                $data['value'] = __('UI', 'property', 'unchecked');
                            }
                            break;
                        case 'AF_Model_Action_SetValue_Select_Single':
                            /** @var $action AF_Model_Action_SetValue_Select_Single */
                            if (null !== $action->getOption()) {
                                $data['value'] = $action->getOption()->getLabel();
                            } else {
                                $data['value'] = null;
                            }
                            break;
                        case 'AF_Model_Action_SetAlgoValue':
                            /** @var $action AF_Model_Action_SetAlgoValue */
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
            /** @var $targetComponent AF_Model_Component */
            $targetComponent = AF_Model_Component::load($targetComponentId);
            $type = $this->getAddElementValue('type');
            /** @var $action AF_Model_Action */
            if ($type == 'setValue') {
                switch (get_class($targetComponent)) {
                    case 'AF_Model_Component_Numeric':
                        $action = new AF_Model_Action_SetValue_Numeric();
                        break;
                    case 'AF_Model_Component_Checkbox':
                        $action = new AF_Model_Action_SetValue_Checkbox();
                        break;
                    case 'AF_Model_Component_Select_Single':
                        $action = new AF_Model_Action_SetValue_Select_Single();
                        break;
                    default:
                        throw new Core_Exception("Unhandled component type");
                }
            } elseif ($type == 'setAlgoValue') {
                $action = new AF_Model_Action_SetAlgoValue();
            } else {
                throw new Core_Exception("Invalid action type");
            }
            // Target component
            $action->setTargetComponent($targetComponent);
            $targetComponent->addAction($action);
            // Condition
            if ($this->getAddElementValue('condition')) {
                $condition = AF_Model_Condition::load($this->getAddElementValue('condition'));
                $action->setCondition($condition);
            }
            $action->save();
            $targetComponent->save();
            $entityManagers = Zend_Registry::get('EntityManagers');
            $entityManagers['default']->flush();
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
        /** @var $action AF_Model_Action */
        $action = AF_Model_Action::load($this->update['index']);
        $newValue = $this->update['value'];
        switch ($this->update['column']) {
            case 'condition':
                if ($newValue) {
                    /** @var $condition AF_Model_Condition */
                    $condition = AF_Model_Condition::load($newValue);
                    $action->setCondition($condition);
                    $this->data = $this->cellList($condition->getId());
                } else {
                    $action->setCondition(null);
                }
                break;
        }
        $action->save();
        $entityManagers = Zend_Registry::get('EntityManagers');
        $entityManagers['default']->flush();
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
        /** @var $action AF_Model_Action */
        $action = AF_Model_Action::load($this->_getParam('index'));
        $action->delete();
        $action->getTargetComponent()->removeAction($action);
        $action->getTargetComponent()->save();
        $entityManagers = Zend_Registry::get('EntityManagers');
        $entityManagers['default']->flush();
        $this->message = __('UI', 'message', 'deleted');
        $this->send();
    }

}
