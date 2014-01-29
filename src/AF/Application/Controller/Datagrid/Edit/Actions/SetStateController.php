<?php
/**
 * @author  matthieu.napoli
 * @author  yoann.croizer
 * @package AF
 */

use AF\Domain\AF\Action\SetState;
use AF\Domain\AF\AF;
use AF\Domain\AF\Condition\Condition;
use AF\Domain\AF\Component\Component;
use Core\Annotation\Secure;

/**
 * @package AF
 */
class AF_Datagrid_Edit_Actions_SetStateController extends UI_Controller_Datagrid
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
        //  Récupère tous les composants
        $query = new Core_Model_Query();
        $query->filter->addCondition(Component::QUERY_AF, $af);
        /** @var $components \AF\Domain\AF\Component\Component[] */
        $components = Component::loadList($query);
        // Affiche les actions dans l'ordre des composants
        foreach ($components as $component) {
            foreach ($component->getActions() as $action) {
                // On ne garde que les action de type setState
                if ($action instanceof SetState) {
                    $data = [];
                    $data['index'] = $action->getId();
                    $condition = $action->getCondition();
                    if ($condition) {
                        $data['condition'] = $this->cellList($action->getCondition()->getId());
                    }
                    $data['targetComponent'] = $this->cellList($component->getId());
                    $data['typeState'] = $action->getState();
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
            $this->setAddElementErrorMessage('targetComponent', 'Un composant doit être sélectionné');
        }
        // Pas d'erreurs
        if (empty($this->_addErrorMessages)) {
            /** @var $targetComponent \AF\Domain\AF\Component\Component */
            $targetComponent = Component::load($targetComponentId);
            if ($this->getAddElementValue('condition')) {
                $condition = Condition::load($this->getAddElementValue('condition'));
            } else {
                $condition = null;
            }
            $action = new SetState();
            $action->setTargetComponent($targetComponent);
            $targetComponent->addAction($action);
            if ($condition) {
                $action->setCondition($condition);
            }
            $action->setState($this->getAddElementValue('typeState'));
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
        /** @var $action SetState */
        $action = SetState::load($this->update['index']);
        $newValue = $this->update['value'];
        switch ($this->update['column']) {
            case 'condition':
                if ($newValue) {
                    /** @var $condition Condition */
                    $condition = Condition::load($newValue);
                    $action->setCondition($condition);
                    $this->data = $this->cellList($condition->getId());
                } else {
                    $action->setCondition(null);
                }
                break;
            case 'typeState':
                $action->setState($newValue);
                $this->data = $action->getState();
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
        /** @var $action SetState */
        $action = SetState::load($this->getParam('index'));
        $action->delete();
        $action->getTargetComponent()->removeAction($action);
        $action->getTargetComponent()->save();
        $this->entityManager->flush();
        $this->message = __('UI', 'message', 'deleted');
        $this->send();
    }

}
