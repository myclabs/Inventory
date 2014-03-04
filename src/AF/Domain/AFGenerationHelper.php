<?php

namespace AF\Domain;

use AF\Domain\Action\Action;
use AF\Domain\Component\Select\SelectOption;
use AF\Domain\InputSet\InputSet;
use AF\Domain\Condition\Condition;
use AF\Domain\Component\Component;
use AF\Application\AFViewConfiguration;
use UI_Form_Action;
use UI_Form_Condition;
use UI_Form_Element_Option;
use UI_Form_ZendElement;
use Zend_Form_Element;

/**
 * Classe aidant à générer un AF (ou sous-AF) en créant un index des composants/actions/conditions
 * déjà générées pour pouvoir les réutiliser dans les associations entre elles
 *
 * @author matthieu.napoli
 */
class AFGenerationHelper
{
    /**
     * @var InputSet|null
     */
    private $inputSet;

    /**
     * @var string
     */
    private $mode;

    /**
     * @var Zend_Form_Element[]
     */
    private $uiElements = [];

    /**
     * @var UI_Form_Element_Option[]
     */
    private $uiOptions = [];

    /**
     * @var UI_Form_Action[]
     */
    private $uiActions = [];

    /**
     * @var UI_Form_Condition[]
     */
    private $uiConditions = [];

    /**
     * @param InputSet|null $inputSet
     * @param string        $mode
     */
    public function __construct(InputSet $inputSet = null, $mode = AFViewConfiguration::MODE_WRITE)
    {
        $this->inputSet = $inputSet;
        $this->mode = $mode;
    }

    /**
     * @param Component           $component
     * @param UI_Form_ZendElement $uiElement
     */
    public function setUIElement(Component $component, UI_Form_ZendElement $uiElement)
    {
        $this->uiElements[$component->getId()] = $uiElement;
    }

    /**
     * @param Component $component
     * @return UI_Form_ZendElement
     */
    public function getUIElement(Component $component)
    {
        if (!isset($this->uiElements[$component->getId()])) {
            $this->uiElements[$component->getId()] = $component->getUIElement($this);
        }
        return $this->uiElements[$component->getId()];
    }

    /**
     * @param SelectOption           $option
     * @param UI_Form_Element_Option $uiOption
     */
    public function setUIOption(SelectOption $option, UI_Form_Element_Option $uiOption)
    {
        $this->uiOptions[$option->getId()] = $uiOption;
    }

    /**
     * @param SelectOption $option
     * @return UI_Form_Element_Option
     */
    public function getUIOption(SelectOption $option)
    {
        if (!isset($this->uiOptions[$option->getId()])) {
            $this->uiOptions[$option->getId()] = $option->getUIElement($this);
        }
        return $this->uiOptions[$option->getId()];
    }

    /**
     * @param Action         $action
     * @param UI_Form_Action $uiAction
     */
    public function setUIAction(Action $action, UI_Form_Action $uiAction)
    {
        $this->uiActions[$action->getId()] = $uiAction;
    }

    /**
     * @param Action $action
     * @return UI_Form_Action
     */
    public function getUIAction(Action $action)
    {
        if (!isset($this->uiActions[$action->getId()])) {
            $this->uiActions[$action->getId()] = $action->getUIAction($this);
        }
        return $this->uiActions[$action->getId()];
    }

    /**
     * @param Condition         $condition
     * @param UI_Form_Condition $uiCondition
     */
    public function setUICondition(Condition $condition, UI_Form_Condition $uiCondition)
    {
        $this->uiConditions[$condition->getId()] = $uiCondition;
    }

    /**
     * @param Condition $condition
     * @return UI_Form_Condition
     */
    public function getUICondition(Condition $condition)
    {
        if (!isset($this->uiConditions[$condition->getId()])) {
            $this->uiConditions[$condition->getId()] = $condition->getUICondition($this);
        }
        return $this->uiConditions[$condition->getId()];
    }

    /**
     * @return InputSet|null
     */
    public function getInputSet()
    {
        return $this->inputSet;
    }

    /**
     * @return bool
     */
    public function isReadOnly()
    {
        return ($this->mode == AFViewConfiguration::MODE_READ);
    }

    /**
     * @return string
     */
    public function getMode()
    {
        return $this->mode;
    }
}
