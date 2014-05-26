<?php

namespace AF\Domain\Condition\Select;

use AF\Domain\Component\Select\SelectOption;
use Core_Exception;
use AF\Application\Form\Condition\ElementaryCondition as FormElementaryCondition;
use AF\Domain\AFGenerationHelper;
use Core_Exception_InvalidArgument;
use AF\Domain\Condition\ElementaryCondition;

/**
 * @author matthieu.napoli
 * @author yoann.croizer
 */
class SelectMultiCondition extends ElementaryCondition
{
    /**
     * {@inheritdoc}
     */
    protected $relation = self::RELATION_CONTAINS;

    /**
     * Option sur laquelle agit la condition.
     * @var SelectOption|null
     */
    protected $option;

    /**
     * {@inheritdoc}
     */
    public function getUICondition(AFGenerationHelper $generationHelper)
    {
        $uiCondition = new FormElementaryCondition($this->ref);
        $uiCondition->element = $generationHelper->getUIElement($this->field);
        switch ($this->relation) {
            case self::RELATION_CONTAINS:
                $uiCondition->relation = FormElementaryCondition::EQUAL;
                break;
            case self::RELATION_NCONTAINS:
                $uiCondition->relation = FormElementaryCondition::NEQUAL;
                break;
            default:
                throw new Core_Exception("The relation '$this->relation'' is invalid or undefined");
        }
        $uiCondition->value = $generationHelper->getUIOption($this->option)->ref;
        return $uiCondition;
    }

    /**
     * @param int $relation
     * @throws Core_Exception_InvalidArgument
     */
    public function setRelation($relation)
    {
        if ($relation != self::RELATION_CONTAINS && $relation != self::RELATION_NCONTAINS) {
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
     * @param SelectOption|null $option
     */
    public function setOption(SelectOption $option = null)
    {
        $this->option = $option;
    }
}
