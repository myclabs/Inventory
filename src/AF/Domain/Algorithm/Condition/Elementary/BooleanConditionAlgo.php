<?php

namespace AF\Domain\Algorithm\Condition\Elementary;

use AF\Domain\Algorithm\Condition\ElementaryConditionAlgo;
use AF\Domain\Algorithm\InputSet;
use AF\Domain\Algorithm\Input\BooleanInput;
use Core_Exception_InvalidArgument;
use Core_Exception_NotFound;

/**
 * @author matthieu.napoli
 * @author yoann.croizer
 * @author hugo.charbonnier
 */
class BooleanConditionAlgo extends ElementaryConditionAlgo
{
    /**
     * {@inheritdoc}
     * Valeur par défaut : égal
     */
    protected $relation = self::RELATION_EQUAL;

    /**
     * @var bool
     */
    protected $value;

    /**
     * {@inheritdoc}
     */
    public function execute(InputSet $inputSet)
    {
        // On récupère l'input
        /** @var $input \AF\Domain\Algorithm\Input\BooleanInput */
        $input = $inputSet->getInputByRef($this->inputRef);
        if (!$input) {
            throw new Core_Exception_NotFound("Il n'y a pas d'input avec le ref " . $this->inputRef);
        }
        $value = $input->getValue();

        // On effectue la comparaison en fonction de la relation
        $relation = $this->relation;
        switch ($relation) {
            case ElementaryConditionAlgo::RELATION_EQUAL:
                return $value === (bool) $this->value;
            case ElementaryConditionAlgo::RELATION_NOTEQUAL:
                return $value !== (bool) $this->value;
            default:
                throw new Core_Exception_InvalidArgument("Relation non gérée");
        }
    }

    /**
     * @return boolean
     */
    public function getValue()
    {
        return $this->value;
    }

    /**
     * @param boolean $value
     */
    public function setValue($value)
    {
        $this->value = $value;
    }
}
