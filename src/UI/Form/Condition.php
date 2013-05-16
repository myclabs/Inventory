<?php
/**
 * @author valentin.claras
 * @package UI
 * @subpackage Form
 */

/**
 * @package UI
 * @subpackage Form
 */
abstract class UI_Form_Condition
{
    /**
     * Relation Equal
     * @var string
     */
    const EQUAL = '==';

    /**
     * Relation Not Equal
     * @var string
     */
    const NEQUAL = '!==';

    /**
     * Relation Greater Than
     * @var string
     */
    const GT = '>';

    /**
     * Relation Less Than
     * @var string
     */
    const LT = '<';

    /**
     * Relation Greater Than or Equal
     * @var string
     */
    const GE = '>=';

    /**
     * Relation Less Than or Equal
     * @var string
     */
    const LE = '<=';

    /**
     * Unique reference of the Condition
     *
     * @var string
     */
    public $ref;

    /**
     * Constructor
     *
     * @param string $ref
     *
     * @throws Core_Exception
     *             if $ref is invalid
     */
    public function __construct($ref)
    {
        if (!(is_string($ref) || trim($ref) === '')) {
            throw new Core_Exception('ref attribute is required.');
        }
        $this->ref = $ref;
    }

    /**
     * Render the javascript listener.
     *
     * @param string $actionRef unique reference of the action using the condition.
     *
     * @return String
     */
    abstract public function getScriptListener($actionRef);

    /**
     * Render the javascript condition.
     *
     * @param string $actionRef unique reference of the action using the condition.
     *
     * @return String
     */
    abstract public function getScriptCondition($actionRef);

    /**
     * Render the javascript param.
     *
     * @param string $actionRef unique reference of the action using the condition.
     *
     * @return String
     */
    abstract public function getScriptParams($actionRef);
}
