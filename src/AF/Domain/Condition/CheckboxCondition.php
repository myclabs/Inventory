<?php

namespace AF\Domain\Condition;

/**
 * @author matthieu.napoli
 * @author yoann.croizer
 */
class CheckboxCondition extends ElementaryCondition
{
    /**
     * Valeur boolean pour laquelle la condition est effective.
     * @var bool
     */
    protected $value = true;

    /**
     * Valeur boolean pour laquelle la condition est effective.
     * @return bool
     */
    public function getValue()
    {
        return $this->value;
    }

    /**
     * Valeur boolean pour laquelle la condition est effective.
     * @param bool $value
     */
    public function setValue($value)
    {
        $this->value = (bool) $value;
    }
}
