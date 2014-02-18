<?php

namespace AF\Domain\Algorithm\Condition\Select;

use AF\Domain\Algorithm\Condition\SelectConditionAlgo;
use AF\Domain\Algorithm\Condition\ElementaryConditionAlgo;
use AF\Domain\Algorithm\InputSet;
use AF\Domain\Algorithm\Input\StringCollectionInput;
use Core_Exception_InvalidArgument;
use Core_Exception_NotFound;

/**
 * @author matthieu.napoli
 * @author yoann.croizer
 * @author hugo.charbonnier
 */
class SelectMultiConditionAlgo extends SelectConditionAlgo
{
    /**
     * {@inheritdoc}
     */
    public function execute(InputSet $inputSet)
    {
        // On récupère l'input
        /** @var $input StringCollectionInput */
        $input = $inputSet->getInputByRef($this->inputRef);
        if (!$input) {
            throw new Core_Exception_NotFound("Il n'y a pas d'input avec le ref " . $this->inputRef);
        }
        $value = $input->getValue();
        switch ($this->relation) {
            case ElementaryConditionAlgo::RELATION_CONTAINS:
                return in_array($this->value, $value);
            case ElementaryConditionAlgo::RELATION_NOTCONTAINS:
                return !in_array($this->value, $value);
            default:
                throw new Core_Exception_InvalidArgument(
                    "Relation incorrecte, doit être RELATION_CONTAINS ou RELATION_NOTCONTAINS"
                );
        }
    }
}
