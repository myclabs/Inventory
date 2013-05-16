<?php
/**
 * @author valentin.claras
 * @package UI
 * @subpackage Form
 */

/**
 * An Action wich will change the value of an element.
 *
 * @package UI
 * @subpackage Form
 */
class UI_Form_Action_SetValue extends UI_Form_Action
{
    /**
     * Value to set when conditions are met.
     *
     * @var mixed(string|array)
     */
    public $value = null;

    /**
     * Value to set when conditions are not met.
     *
     * @var mixed(string|array)
     */
    public $backValue = null;

    /**
     * Uncertainty to set when conditions are met.
     *
     * @var string
     */
    public $uncertainty = null;

    /**
     * Uncertainty to set when conditions are not met.
     *
     * @var string
     */
    public $backUncertainty = null;

    /**
     *
     * @var string
     */
    public $request = null;


    /**
     * Constructor
     * @param string $ref
     */
    public function __construct($ref)
    {
        parent::__construct($ref);
        $this->_functionCalled = self::SETVALUE;
        $this->_reverseFunctionCalled = self::SETVALUE;
    }

    /**
     * @param Zend_Form_Element $element
     *
     * @return string
     */
    protected function getIdTargetedElement(Zend_Form_Element $element)
    {
        return $element->getId();
    }

    /**
     * @return string
     */
    protected function getFunctionCalled()
    {
        if (is_array($this->value)) {
            return $this->_functionCalled.'([\''.implode('\', ', $this->value).'\'])';
        } else {
            return $this->_functionCalled.'(\''.$this->value.'\')';
        }
    }

    /**
     * @return bool
     */
    protected function hasReverseFunction()
    {
        return ($this->backValue !== null);
    }

    /**
     * @return string
     */
    protected function getReverseFunctionCalled()
    {
        if (is_array($this->backValue)) {
            return $this->_reverseFunctionCalled.'([\''.implode('\', ', $this->backValue).'\'])';
        } else {
            return $this->_reverseFunctionCalled.'(\''.$this->backValue.'\')';
        }
    }

    /**
     * Render the javascript action.
     *
     * @param Zend_Form_Element $element
     *
     * @return string
     */
    public function getScript(Zend_Form_Element $element)
    {
        if ($this->condition === null) {
            return null;
        }

        $withPercent = false;
        if (($element instanceof UI_Form_Element_Pattern_Value) && ($element->getPercent() !== null)) {
            $withPercent = true;
        }

        if (($this->request === null) && ($withPercent === false)) {
            return parent::getScript($element);
        }

        $script = '';

        $script .= $this->condition->getScriptListener($this->_ref);

        $script .= '$.fn.formAction_' . $this->_ref . ' = function () {';
        $script .= 'var elToAct = $(\'#' . $this->getIdTargetedElement($element) . '\');';
        // If we have a percent input we get it from the DOM.
        if ($withPercent) {
            $script .= 'var elPercentToAct = $(\'#'.$element->getPercent()->id.'\');';
        }
        $script .= 'if (' . $this->condition->getScriptCondition($this->_ref) . ') {';
        if ($this->request === null) {
            $script .= 'elToAct.' . $this->getFunctionCalled() . ';';
            // If we have a percent input we change it when we change the target input of the action
            if ($withPercent) {
                $script .= 'elPercentToAct.' . $this->_functionCalled . '(\'' . $this->uncertainty . '\');';
            }
        } else {
            $script .= '$(\'#' . $element->getId() . '-line\').children(\'.controls\').append(';
            $script .= '\'<div class="input loading">';
            $script .= '<img alt="' . __('UI', 'loading', 'loading') . '" src="images/ui/ajax-loader.gif"></i>';
            $script .= '</div>\'';
            $script .= ');';
            $script .= 'var params = \'' . $this->condition->getScriptParams($this->_ref) . '\';';
            $script .= '$.get(';
            $script .= '\'' . $this->request . '\', ';
            $script .= 'params, ';
            $script .= 'function(o){';
            $script .= 'elToAct.' . $this->_functionCalled . '(o.value);';
            if ($withPercent) {
                $script .= 'elPercentToAct.' . $this->_functionCalled . '(o.uncertainty);';
            }
            $script .= '$(\'#' . $element->getId() . '-line div.input.loading\').remove();';
            $script .= '}';
            $script .= ').error(function(o) {';
            $script .= '$(\'#' . $element->getId() . '-line div.input.loading\').remove();';
            $script .= 'errorHandler(o);';
            $script .= '});';
        }
        if ($this->hasReverseFunction()) {
            $script .= '} else {';
            $script .= 'elToAct.' . $this->getReverseFunctionCalled(). ';';
            if ($withPercent) {
                $script .= 'elPercentToAct.' . $this->_reverseFunctionCalled . '(' . $this->backUncertainty . ');';
            }
        }
        $script .= '}';
        $script .= '};';

        return $script;
    }
}
