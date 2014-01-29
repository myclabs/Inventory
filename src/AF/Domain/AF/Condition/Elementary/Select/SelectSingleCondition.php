<?php

namespace AF\Domain\AF\Condition\Elementary\Select;

use AF\Domain\AF\Component\Select\SelectOption;
use Core_Exception;
use UI_Form_Condition_Elementary;
use AF\Domain\AF\GenerationHelper;
use Core_Exception_InvalidArgument;
use AF\Domain\AF\Condition\ElementaryCondition;

/**
 * @author matthieu.napoli
 * @author yoann.croizer
 */
class SelectSingleCondition extends ElementaryCondition
{
    /**
     * Option sur laquelle agit la condition.
     * @var SelectOption|null
     */
    protected $option;

    /**
     * {@inheritdoc}
     */
    public function getUICondition(GenerationHelper $generationHelper)
    {
        $uiCondition = new UI_Form_Condition_Elementary($this->ref);
        $uiCondition->element = $generationHelper->getUIElement($this->field);
        switch ($this->relation) {
            case self::RELATION_EQUAL:
                $uiCondition->relation = UI_Form_Condition_Elementary::EQUAL;
                break;
            case self::RELATION_NEQUAL:
                $uiCondition->relation = UI_Form_Condition_Elementary::NEQUAL;
                break;
            default:
                throw new Core_Exception("The relation '$this->relation'' is invalid or undefined");
        }
        if ($this->option) {
            $uiCondition->value = $generationHelper->getUIOption($this->option)->ref;
        }
        return $uiCondition;
    }

    /**
     * @param int $relation
     * @throws Core_Exception_InvalidArgument Relation invalide
     */
    public function setRelation($relation)
    {
        if ($relation != self::RELATION_EQUAL && $relation != self::RELATION_NEQUAL) {
            throw new Core_Exception_InvalidArgument("Invalid relation $relation");
        }
        $this->relation = $relation;
    }

    /**
     * @return SelectOption
     */
    public function getOption()
    {
        return $this->option;
    }

    /**
     * Set the Option attribute.
     * @param SelectOption|null $option
     */
    public function setOption(SelectOption $option = null)
    {
        $this->option = $option;
    }
}
