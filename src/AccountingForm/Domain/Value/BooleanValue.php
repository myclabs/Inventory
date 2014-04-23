<?php

namespace AccountingForm\Domain\Value;

/**
 * Boolean value.
 *
 * @author matthieu.napoli
 */
class BooleanValue implements Value
{
    /**
     * @var bool
     */
    private $value;

    /**
     * @param bool $value
     */
    public function __construct($value)
    {
        $this->value = (bool) $value;
    }

    /**
     * @return bool
     */
    public function getValue()
    {
        return $this->value;
    }
}
