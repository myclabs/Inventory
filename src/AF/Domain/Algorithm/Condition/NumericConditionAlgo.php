<?php

namespace AF\Domain\Algorithm\Condition;

use AF\Domain\Algorithm\InputSet;
use AF\Domain\Algorithm\Input\NumericInput;
use Calc_UnitValue;
use Core_Exception_InvalidArgument;
use Core_Exception_NotFound;
use Core_Exception_UndefinedAttribute;

/**
 * @author matthieu.napoli
 * @author yoann.croizer
 * @author hugo.charbonnier
 */
class NumericConditionAlgo extends ElementaryConditionAlgo
{
    /**
     * @var float|null
     */
    protected $value;

    /**
     * {@inheritdoc}
     */
    public function execute(InputSet $inputSet)
    {
        /** @var $input NumericInput */
        $input = $inputSet->getInputByRef($this->inputRef);
        if (!$input) {
            throw new Core_Exception_NotFound("Il n'y a pas d'input avec le ref " . $this->inputRef);
        }
        // Valeur attendue vide
        if ($this->getValue() === null) {
            if ($this->getRelation() == ElementaryConditionAlgo::RELATION_EQUAL) {
                return ($input->getValue() === null || $input->getValue()->getDigitalValue() === null);
            } elseif ($this->getRelation() == ElementaryConditionAlgo::RELATION_NOTEQUAL) {
                return ($input->getValue() !== null && $input->getValue()->getDigitalValue() !== null);
            } else {
                throw new Core_Exception_UndefinedAttribute("The value of the condition {$this->ref} must be defined");
            }
        }
        $expectedValue = new Calc_UnitValue($input->getValue()->getUnit(), $this->getValue());
        // Valeur attendue non vide
        switch ($this->getRelation()) {
            case ElementaryConditionAlgo::RELATION_EQUAL:
                return $expectedValue->toCompare($input->getValue(), Calc_UnitValue::RELATION_EQUAL);
            case ElementaryConditionAlgo::RELATION_NOTEQUAL:
                return $expectedValue->toCompare($input->getValue(), Calc_UnitValue::RELATION_NOTEQUAL);
            case ElementaryConditionAlgo::RELATION_GE:
                return $expectedValue->toCompare($input->getValue(), Calc_UnitValue::RELATION_GE);
            case ElementaryConditionAlgo::RELATION_GT:
                return $expectedValue->toCompare($input->getValue(), Calc_UnitValue::RELATION_GT);
            case ElementaryConditionAlgo::RELATION_LE:
                return $expectedValue->toCompare($input->getValue(), Calc_UnitValue::RELATION_LE);
            case ElementaryConditionAlgo::RELATION_LT:
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
