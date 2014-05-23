<?php

namespace AF\Application\Form\Condition;

/**
 * Used to associate elementary conditions with operator & or |
 *     to create complexes conditions
 *
 * @author valentin.claras
 * @author yoann.croizer
 */
class ExpressionCondition extends FormCondition
{
    // Constants definition
    /**
     * Useful for $this->expression
     * @var string
     */
    const OR_SIGN = '||';

    /**
     * Useful for $this->expression
     * @var string
     */
    const AND_SIGN = '&&';


    /**
     * Expression of the condition
     *
     * @var string
     */
    public $expression;

    /**
     * List of conditions
     *
     * @var FormCondition[]
     */
    protected $_conditions;

    /**
     * Add conditions to a Multi Condition
     * If $condition is an instance of UI_Form_Condition_Multi, add all conditions of $condition
     *
     * @param FormCondition $condition
     * @return void
     */
    public function addCondition($condition)
    {
        $this->_conditions[$condition->ref] = $condition;
    }

    /**
     * Get all conditions of the Multi Condition
     *
     * @return FormCondition[]
     */
    public function getConditions()
    {
        return $this->_conditions;
    }

    /**
     * Render the javascript listener.
     *
     * @param string $actionRef unique reference of the action using the condition.
     *
     * @return String
     */
    public function getScriptListener($actionRef)
    {
        $scriptListener = '';

        foreach ($this->_conditions as $condition) {
            $scriptListener .= $condition->getScriptListener($actionRef);
        }

        return $scriptListener;
    }

    /**
     * Render the javascript condition.
     *
     * @param string $actionRef unique reference of the action using the condition.
     *
     * @return String
     */
    public function getScriptCondition($actionRef)
    {
        $scriptCondition = '';

        foreach ($this->_conditions as $condition) {
            if ($condition instanceof self) {
                $scriptCondition .= '(' . $condition->getScriptCondition($actionRef) . ')';
            } else {
                $scriptCondition .= $condition->getScriptCondition($actionRef);
            }
            $scriptCondition .= ' ' . $this->expression . ' ';
        }
        $scriptCondition = substr($scriptCondition, 0, -(strlen($this->expression) + 2));

        return $scriptCondition;
    }

    /**
     * Render the javascript param.
     *
     * @param string $actionRef unique reference of the action using the condition.
     *
     * @return String
     */
    public function getScriptParams($actionRef)
    {
        $scriptParam = '';

        foreach ($this->_conditions as $condition) {
            $scriptParam .= $condition->getScriptParams($actionRef) . '&';
        }

        return $scriptParam;
    }
}
