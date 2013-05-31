<?php
/**
 * @author valentin.claras
 * @package UI
 * @subpackage Form
 */

/**
 * An Action wich will do a custom javascript function on the element.
 *
 * @package UI
 * @subpackage Form
 */
class UI_Form_Action_Custom extends UI_Form_Action
{
    /**
     * Constructor
     * @param unknown_type $ref
     */
    public function __construct($ref)
    {
        parent::__construct($ref);
    }

    /**
     * Define the function to be called on the element.
     *
     * @param string $functionName
     */
    public function setFunctionName($functionName)
    {
        $this->_functionCalled = $functionName;
    }

    /**
     * Define the reversed function to be called on the element.
     *
     * @param string $reverseFunctionName
     */
    public function setReverseFunctionName($reverseFunctionName)
    {
        $this->_reverseFunctionCalled = $reverseFunctionName;
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
        return $this->_functionCalled.'()';
    }

    /**
     * @return bool
     */
    protected function hasReverseFunction()
    {
        return ($this->_reverseFunctionCalled !== null);
    }

    /**
     * @return string
     */
    protected function getReverseFunctionCalled()
    {
        return $this->_reverseFunctionCalled.'()';
    }
}
