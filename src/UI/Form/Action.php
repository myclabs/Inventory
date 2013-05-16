<?php
/**
 * @author valentin.claras
 * @package UI
 * @subpackage Form
 */

/**
 * An Action is associated with an element
 *     When all the conditions of the Action are validates,
 *     the Action is launched
 *
 * @package UI
 * @subpackage Form
 */
abstract class UI_Form_Action
{
    /**
     * Set a value of an Element
     * @var string
     */
    const SETVALUE = 'formActionSetValue';

    /**
     * Set a value of an option
     * @var string
     */
    const SETOPTIONS = 'formActionSetOptions';

    /**
     * Show an Element
     * @var string
     */
    const SHOW = 'formActionShow';

    /**
     * Hide an Element
     * @var string
     */
    const HIDE = 'formActionHide';

    /**
     * Enable an Element
     *
     * @var string
     */
    const ENABLE = 'formActionEnable';

    /**
     * Disable an Element
     *
     * @var string
     */
    const DISABLE = 'formActionDisable';

    /**
     * Trigger a custom javascript method previously implemented by the user.
     *
     * @var string
     */
    const CUSTOM = 'custom';

    /**
     * Unique ref of the Action
     *
     * @var string
     */
    protected $_ref;

    /**
     * Action called.
     *
     * @var const
     */
    protected $_functionCalled = null;

    /**
     * Reverse function called.
     *
     * @var string
     */
    protected $_reverseFunctionCalled = null;

    /**
     * Associated condition of the Action.
     *
     * @var UI_Form_Condition
     */
    public $condition = null;

    /**
     * Constructor
     * @param string $ref
     */
    public function __construct($ref)
    {
        if (!(isset($ref))) {
            throw new Core_Exception_InvalidArgument('Ref attribute must be specified');
        }
        $this->_ref = $ref;
    }

    /**
     * @param Zend_Form_Element $element
     *
     * @return string
     */
    abstract protected function getIdTargetedElement(Zend_Form_Element $element);

    /**
     * @return string
     */
    abstract protected function getFunctionCalled();

    /**
     * @return bool
     */
    abstract protected function hasReverseFunction();


    /**
     * @return string
     */
    abstract protected function getReverseFunctionCalled();

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

        $script = '';

        $script .= '$.fn.formAction_' . $this->_ref . ' = function () {';
        $script .= 'var elToAct = $(\'#' . $this->getIdTargetedElement($element) . '\');';
        $script .= 'if (' . $this->condition->getScriptCondition($this->_ref) . ') {';
        $script .= 'elToAct.' . $this->getFunctionCalled() . ';';
        if ($this->hasReverseFunction()) {
            $script .= '} else {';
            $script .= 'elToAct.' . $this->getReverseFunctionCalled(). ';';
        }
        $script .= '}';
        $script .= '};';

        $script .= $this->condition->getScriptListener($this->_ref);

        return $script;
    }

}
