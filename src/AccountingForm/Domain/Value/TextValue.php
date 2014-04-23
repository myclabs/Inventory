<?php

namespace AccountingForm\Domain\Value;

/**
 * String value.
 *
 * @author matthieu.napoli
 */
class StringValue implements Value
{
    /**
     * @var string
     */
    private $value;

    /**
     * @param string $value
     */
    public function __construct($value)
    {
        $this->value = (string) $value;
    }

    /**
     * @return string
     */
    public function getValue()
    {
        return $this->value;
    }
}
