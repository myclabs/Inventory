<?php
/**
 * @author  matthieu.napoli
 * @package AF
 */

/**
 * Classe aidant à générer un AF (ou sous-AF) en créant un index des composants/actions/conditions
 * déjà générées pour pouvoir les réutiliser dans les associations entre elles
 *
 * @package AF
 */
class AF_GenerationHelper
{

    /**
     * @var AF_Model_InputSet|null
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
     * @param AF_Model_InputSet|null $inputSet
     * @param string                 $mode
     */
    public function __construct(AF_Model_InputSet $inputSet = null, $mode = AF_ViewConfiguration::MODE_WRITE)
    {
        $this->inputSet = $inputSet;
        $this->mode = $mode;
    }

    /**
     * @param AF_Model_Component $component
     * @param Zend_Form_Element  $uiElement
     */
    public function setUIElement(AF_Model_Component $component, Zend_Form_Element $uiElement)
    {
        $this->uiElements[$component->getId()] = $uiElement;
    }

    /**
     * @param AF_Model_Component $component
     * @return Zend_Form_Element
     */
    public function getUIElement(AF_Model_Component $component)
    {
        if (!isset($this->uiElements[$component->getId()])) {
            $this->uiElements[$component->getId()] = $component->getUIElement($this);
        }
        return $this->uiElements[$component->getId()];
    }

    /**
     * @param AF_Model_Component_Select_Option $option
     * @param UI_Form_Element_Option           $uiOption
     */
    public function setUIOption(AF_Model_Component_Select_Option $option, UI_Form_Element_Option $uiOption)
    {
        $this->uiOptions[$option->getId()] = $uiOption;
    }

    /**
     * @param AF_Model_Component_Select_Option $option
     * @return UI_Form_Element_Option
     */
    public function getUIOption(AF_Model_Component_Select_Option $option)
    {
        if (!isset($this->uiOptions[$option->getId()])) {
            $this->uiOptions[$option->getId()] = $option->getUIElement($this);
        }
        return $this->uiOptions[$option->getId()];
    }

    /**
     * @param AF_Model_Action $action
     * @param UI_Form_Action  $uiAction
     */
    public function setUIAction(AF_Model_Action $action, UI_Form_Action $uiAction)
    {
        $this->uiActions[$action->getId()] = $uiAction;
    }

    /**
     * @param AF_Model_Action $action
     * @return UI_Form_Action
     */
    public function getUIAction(AF_Model_Action $action)
    {
        if (!isset($this->uiActions[$action->getId()])) {
            $this->uiActions[$action->getId()] = $action->getUIAction($this);
        }
        return $this->uiActions[$action->getId()];
    }

    /**
     * @param AF_Model_Condition $condition
     * @param UI_Form_Condition  $uiCondition
     */
    public function setUICondition(AF_Model_Condition $condition, UI_Form_Condition $uiCondition)
    {
        $this->uiConditions[$condition->getId()] = $uiCondition;
    }

    /**
     * @param AF_Model_Condition $condition
     * @return UI_Form_Condition
     */
    public function getUICondition(AF_Model_Condition $condition)
    {
        if (!isset($this->uiConditions[$condition->getId()])) {
            $this->uiConditions[$condition->getId()] = $condition->getUICondition($this);
        }
        return $this->uiConditions[$condition->getId()];
    }

    /**
     * @return AF_Model_InputSet|null
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
        return ($this->mode == AF_ViewConfiguration::MODE_READ);
    }

    /**
     * @return string
     */
    public function getMode()
    {
        return $this->mode;
    }

}
