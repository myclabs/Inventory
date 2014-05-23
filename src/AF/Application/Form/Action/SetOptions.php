<?php

namespace AF\Application\Form\Action;

use Zend_Form_Element;

/**
 * An Action wich will change the options of a multi element.
 *
 * @author valentin.claras
 */
class SetOptions extends FormAction
{
    /**
     * Value to set when conditions are met.
     *
     * @var array
     */
    public $value = null;

    /**
     * Value to set when conditions are not met.
     *
     * @var array
     */
    public $backValue = null;

    /**
     *
     * @var string
     */
    public $request = null;


    /**
     * @param string $ref
     */
    public function __construct($ref)
    {
        parent::__construct($ref);
        $this->_functionCalled = self::SETOPTIONS;
        $this->_reverseFunctionCalled = self::SETOPTIONS;
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
            return $this->_functionCalled . '([\'' . implode('\', ', $this->value) . '\'])';
        } else {
            return $this->_functionCalled . '(\'' . $this->value . '\')';
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
            return $this->_reverseFunctionCalled . '([\'' . implode('\', ', $this->backValue) . '\'])';
        } else {
            return $this->_reverseFunctionCalled . '(\'' . $this->backValue . '\')';
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
        if ($this->request === null) {
            return parent::getScript($element);
        }

        $script = '';

        $script .= $this->condition->getScriptListener($this->_ref);

        $script .= '$.fn.formAction_' . $this->_ref . ' = function () {';
        $script .= 'var elToAct = $(\'#' . $this->getIdTargetedElement($element) . '\');';
        $script .= 'if (' . $this->condition->getScriptCondition($this->_ref) . ') {';
        $script .= '$(\'#' . $element->getId() . '\').parent().parent().append(';
        $script .= '\'<div class="input loading">';
        $script .= '<img alt="' . __('UI', 'loading', 'loading') . '" src="images/ui/ajax-loader.gif"></i>';
        $script .= '</div>\'';
        $script .= ');';
        $script .= 'var params = \'' . $this->condition->getScriptParams($this->_ref) . '\';';
        $script .= '$.get(';
        $script .= '\'' . $this->request . '\', ';
        $script .= 'params, ';
        $script .= 'function(o){';
        $script .= 'elToAct.' . $this->_functionCalled . '(o);';
        $script .= '$(\'div.input.loading\', $(\'#' . $element->getId() . '\').parent().parent()).remove();';
        $script .= '}';
        $script .= ').error(function(o) {';
        $script .= '$(\'div.input.loading\', $(\'#' . $element->getId() . '\').parent().parent()).remove();';
        $script .= 'errorHandler(o);';
        $script .= '});';
        if ($this->hasReverseFunction()) {
            $script .= '} else {';
            $script .= 'elToAct.' . $this->getReverseFunctionCalled() . ';';
        }
        $script .= '}';
        $script .= '};';

        return $script;
    }
}
