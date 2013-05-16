<?php
/**
 * @author valentin.claras
 * @package UI
 * @subpackage Form
 */

/**
 * An Action which will disable the element.
 *
 * @package UI
 * @subpackage Form
 */
class UI_Form_Action_Disable extends UI_Form_Action_SetState
{
    /**
     * Constructor
     * @param string $ref
     */
    public function __construct($ref)
    {
       parent::__construct($ref);
       $this->_functionCalled = self::DISABLE;
       $this->_reverseFunctionCalled = self::ENABLE;
    }
}
