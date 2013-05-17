<?php
/**
 * @author     matthieu.napoli
 * @author     yoann.croizer
 * @author     hugo.charbonnier
 * @package    Algo
 * @subpackage Condition
 */

/**
 * @package    Algo
 * @subpackage Condition
 */
class Algo_Model_Condition_Elementary_Numeric extends Algo_Model_Condition_Elementary
{

    /**
     * @var float|null
     */
    protected $value;

    /**
     * Execute
     * @param Algo_Model_InputSet $inputSet
     * @return bool
     */
    public function execute(Algo_Model_InputSet $inputSet)
    {
        /** @var $input Algo_Model_Input_Numeric */
        $input = $inputSet->getInputByRef($this->inputRef);
        if (!$input) {
            throw new Core_Exception_NotFound("Il n'y a pas d'input avec le ref " . $this->inputRef);
        }
        // Valeur attendue vide
        if ($this->getValue() === null) {
            if ($this->getRelation() == Algo_Model_Condition_Elementary::RELATION_EQUAL) {
                return ($input->getValue() === null || $input->getValue()->value->digitalValue === null);
            } elseif ($this->getRelation() == Algo_Model_Condition_Elementary::RELATION_NOTEQUAL) {
                return ($input->getValue() !== null && $input->getValue()->value->digitalValue !== null);
            } else {
                throw new Core_Exception_UndefinedAttribute("The value of the condition {$this->ref} must be defined");
            }
        }
        $expectedValue = new Calc_UnitValue($input->getValue()->unit, new Calc_Value());
        $expectedValue->value->digitalValue = $this->getValue();
        // Valeur attendue non vide
        switch ($this->getRelation()) {
            case Algo_Model_Condition_Elementary::RELATION_EQUAL:
                return $expectedValue->toCompare($input->getValue(), Calc_UnitValue::RELATION_EQUAL);
            case Algo_Model_Condition_Elementary::RELATION_NOTEQUAL:
                return $expectedValue->toCompare($input->getValue(), Calc_UnitValue::RELATION_NOTEQUAL);
            case Algo_Model_Condition_Elementary::RELATION_GE:
                return $expectedValue->toCompare($input->getValue(), Calc_UnitValue::RELATION_GE);
            case Algo_Model_Condition_Elementary::RELATION_GT:
                return $expectedValue->toCompare($input->getValue(), Calc_UnitValue::RELATION_GT);
            case Algo_Model_Condition_Elementary::RELATION_LE:
                return $expectedValue->toCompare($input->getValue(), Calc_UnitValue::RELATION_LE);
            case Algo_Model_Condition_Elementary::RELATION_LT:
                return $expectedValue->toCompare($input->getValue(), Calc_UnitValue::RELATION_LT);
            default:
                throw new Core_Exception_InvalidArgument("Relation non gérée");
        }
    }

    /**
     * @return float|null
     */
    public function getValue()
    {
        return $this->value;
    }

    /**
     * @param float|null $value
     */
    public function setValue($value = null)
    {
        $this->value = $value;
    }

}
