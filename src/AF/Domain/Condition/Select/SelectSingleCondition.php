<?php

namespace AF\Domain\Condition\Select;

use AF\Domain\Component\Select\SelectOption;
use Core_Exception_InvalidArgument;
use AF\Domain\Condition\ElementaryCondition;

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
     * @return SelectOption|null
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
