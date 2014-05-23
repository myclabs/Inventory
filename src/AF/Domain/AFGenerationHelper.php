<?php

namespace AF\Domain;

use AF\Domain\Action\Action;
use AF\Domain\Component\Select\SelectOption;
use AF\Domain\InputSet\InputSet;
use AF\Domain\Condition\Condition;
use AF\Domain\Component\Component;
use AF\Application\AFViewConfiguration;
use AF\Application\Form\Action\FormAction;
use AF\Application\Form\Condition\FormCondition;
use AF\Application\Form\Element\Option;
use AF\Application\Form\Element\ZendFormElement;
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
     * @var Option[]
     */
    private $uiOptions = [];

    /**
     * @var FormAction[]
     */
    private $uiActions = [];

    /**
     * @var FormCondition[]
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
     * @param Component       $component
     * @param ZendFormElement $uiElement
     */
    public function setUIElement(Component $component, ZendFormElement $uiElement)
    {
        $this->uiElements[$component->getId()] = $uiElement;
    }

    /**
     * @param Component $component
     * @return ZendFormElement
     */
    public function getUIElement(Component $component)
    {
        if (!isset($this->uiElements[$component->getId()])) {
            $this->uiElements[$component->getId()] = $component->getUIElement($this);
        }
        return $this->uiElements[$component->getId()];
    }

    /**
     * @param SelectOption $option
     * @param Option       $uiOption
     */
    public function setUIOption(SelectOption $option, Option $uiOption)
    {
        $this->uiOptions[$option->getId()] = $uiOption;
    }

    /**
     * @param SelectOption $option
     * @return Option
     */
    public function getUIOption(SelectOption $option)
    {
        if (!isset($this->uiOptions[$option->getId()])) {
            $this->uiOptions[$option->getId()] = $option->getUIElement($this);
        }
        return $this->uiOptions[$option->getId()];
    }

    /**
     * @param Action     $action
     * @param FormAction $uiAction
     */
    public function setUIAction(Action $action, FormAction $uiAction)
    {
        $this->uiActions[$action->getId()] = $uiAction;
    }

    /**
     * @param Action $action
     * @return FormAction
     */
    public function getUIAction(Action $action)
    {
        if (!isset($this->uiActions[$action->getId()])) {
            $this->uiActions[$action->getId()] = $action->getUIAction($this);
        }
        return $this->uiActions[$action->getId()];
    }

    /**
     * @param Condition     $condition
     * @param FormCondition $uiCondition
     */
    public function setUICondition(Condition $condition, FormCondition $uiCondition)
    {
        $this->uiConditions[$condition->getId()] = $uiCondition;
    }

    /**
     * @param Condition $condition
     * @return FormCondition
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
