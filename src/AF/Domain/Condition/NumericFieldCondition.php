<?php

namespace AF\Domain\Condition;

use Core_Exception_InvalidArgument;

/**
 * @author matthieu.napoli
 * @author yoann.croizer
 */
class NumericFieldCondition extends ElementaryCondition
{
    /**
     * Valeur numérique pour laquelle la condition est effective.
     * @var float|null
     */
    protected $value;

    /**
     * Set the relation Param.
     * @param int $relation
     * @throws Core_Exception_InvalidArgument
     * @return void
     */
    public function setRelation($relation)
    {
        switch ($relation) {
            case self::RELATION_EQUAL:
            case self::RELATION_NEQUAL:
            case self::RELATION_GT:
            case self::RELATION_LT:
            case self::RELATION_GE:
            case self::RELATION_LE:
                break;
            default:
                throw new Core_Exception_InvalidArgument("The relation '$relation' does not exist");
        }
        $this->relation = $relation;
    }

    /**
     * @return float
     */
    public function getValue()
    {
        return $this->value;
    }

    /**
     * @param float|null $value
     */
    public function setValue($value)
    {
        if ($value === null) {
            $this->value = null;
        } else {
            $this->value = (float) $value;
        }
    }
}
