<?php

namespace AF\Domain\Algorithm\Condition\Elementary\Select;

use AF\Domain\Algorithm\Condition\Elementary\SelectConditionAlgo;
use AF\Domain\Algorithm\Condition\ElementaryConditionAlgo;
use AF\Domain\Algorithm\InputSet;
use AF\Domain\Algorithm\Input\StringInput;
use Core_Exception_InvalidArgument;
use Core_Exception_NotFound;

/**
 * @author matthieu.napoli
 * @author yoann.croizer
 * @author hugo.charbonnier
 */
class SelectSingleConditionAlgo extends SelectConditionAlgo
{
    /**
     * {@inheritdoc}
     */
    public function execute(InputSet $inputSet)
    {
        // On récupère l'input
        /** @var $input StringInput */
        $input = $inputSet->getInputByRef($this->inputRef);
        if (!$input) {
            throw new Core_Exception_NotFound("Il n'y a pas d'input avec le ref " . $this->inputRef);
        }
        $value = $input->getValue();

        switch ($this->relation) {
            case ElementaryConditionAlgo::RELATION_EQUAL:
                return ($value == $this->value);
            case ElementaryConditionAlgo::RELATION_NOTEQUAL:
                return !($value == $this->value);
            default:
                throw new Core_Exception_InvalidArgument(
                    "Relation incorrecte, doit être RELATION_EQUAL ou RELATION_NOTEQUAL"
                );
        }
    }
}
