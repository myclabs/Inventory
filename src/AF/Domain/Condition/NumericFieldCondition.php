<?php

namespace AF\Domain\Condition;

use Core_Exception;
use AF\Application\Form\Condition\ElementaryCondition as FormElementaryCondition;
use Core_Exception_InvalidArgument;
use AF\Domain\AFGenerationHelper;

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
     * {@inheritdoc}
     */
    public function getUICondition(AFGenerationHelper $generationHelper)
    {
        $uiCondition = new FormElementaryCondition($this->ref);
        $uiCondition->element = $generationHelper->getUIElement($this->field);
        switch ($this->getRelation()) {
            case self::RELATION_EQUAL:
                $uiCondition->relation = FormElementaryCondition::EQUAL;
                break;
            case self::RELATION_NEQUAL:
                $uiCondition->relation = FormElementaryCondition::NEQUAL;
                break;
            case self::RELATION_GT:
                $uiCondition->relation = FormElementaryCondition::GT;
                break;
            case self::RELATION_LT:
                $uiCondition->relation = FormElementaryCondition::LT;
                break;
            case self::RELATION_GE:
                $uiCondition->relation = FormElementaryCondition::GE;
                break;
            case self::RELATION_LE:
                $uiCondition->relation = FormElementaryCondition::LE;
                break;
            default:
                throw new Core_Exception("The relation '{$this->getRelation()}'' is invalid or undefined");
        }
        $uiCondition->value = $this->value;
        return $uiCondition;
    }

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
