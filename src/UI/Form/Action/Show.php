<?php
/**
 * @author valentin.claras
 * @package UI
 * @subpackage Form
 */

/**
 * An Action which will show the element.
 *
 * @package UI
 * @subpackage Form
 */
class UI_Form_Action_Show extends UI_Form_Action_SetState
{
    /**
     * Constructor
     * @param string $ref
     */
    public function __construct($ref)
    {
       parent::__construct($ref);
       $this->_functionCalled = self::SHOW;
       $this->_reverseFunctionCalled = self::HIDE;
    }
}
