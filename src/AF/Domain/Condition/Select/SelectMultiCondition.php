<?php

namespace AF\Domain\Condition\Select;

use AF\Domain\Component\Select\SelectOption;
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
