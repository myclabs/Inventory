<?php

use AF\Domain\Component\Select\SelectOption;
use AF\Domain\Condition\ExpressionCondition;
use AF\Domain\Condition\ElementaryCondition;
use AF\Domain\Condition\NumericFieldCondition;
use AF\Domain\Condition\CheckboxCondition;
use AF\Domain\Condition\Select\SelectSingleCondition;
use AF\Domain\Condition\Select\SelectMultiCondition;
use Core\Annotation\Secure;

/**
 * @author matthieu.napoli
 */
class AF_Edit_ConditionsController extends Core_Controller
{
    /**
     * Permet de modifier une condition de type elementary avec un popup personalisé.
     * @Secure("editAF")
     */
    public function updateConditionPopupAction()
    {
        $this->view->condition = ElementaryCondition::load($this->getParam('idCondition'));
        $this->_helper->layout()->disableLayout();
    }

    /**
     * Permet de modifier une condition de type elementary avec un popup personalisé.
     * AJAX
     * @Secure("editAF")
     */
    public function updateConditionSubmitAction()
    {
        if (!$this->getRequest()->isPost()) {
            throw new Core_Exception_NotFound("Page invalide");
        }
        $condition = ElementaryCondition::load($this->getParam('idCondition'));

        switch (get_class($condition)) {
            case NumericFieldCondition::class:
                /** @var $condition NumericFieldCondition */
                $condition->setRelation($this->getParam('relation'));
                // Autorisation de la valeur NULL
                $value = $this->getParam('value');
                if ($value == '') {
                    $value = null;
                }
                $condition->setValue($value);
                break;
            case CheckboxCondition::class:
                /** @var $condition CheckboxCondition */
                $condition->setValue($this->getParam('value'));
                break;
            case SelectSingleCondition::class:
                /** @var $condition SelectSingleCondition */
                $condition->setRelation($this->getParam('relation'));
                if ($this->getParam('value') != null) {
                    /** @var $option SelectOption */
                    $option = SelectOption::load($this->getParam('value'));
                    $condition->setOption($option);
                } else {
                    $condition->setOption(null);
                }
                break;
            case SelectMultiCondition::class:
                /** @var $condition SelectMultiCondition */
                $condition->setRelation($this->getParam('relation'));
                if ($this->getParam('value') != null) {
                    /** @var $option SelectOption */
                    $option = SelectOption::load($this->getParam('value'));
                    $condition->setOption($option);
                } else {
                    $condition->setOption(null);
                }
                break;
        }
        $condition->save();
        $this->entityManager->flush();
        $this->redirect('/af/edit/menu/id/' . $condition->getAf()->getId() . '/onglet/interaction');
    }

    /**
     * Popup qui affiche une condition expression
     * @Secure("editAF")
     */
    public function popupConditionExpressionAction()
    {
        $this->view->condition = ExpressionCondition::load($this->getParam('id'));
        $this->_helper->layout()->disableLayout();
    }
}
