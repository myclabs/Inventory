<?php

namespace AccountingForm\Domain\Value;

use Calc_UnitValue;

/**
 * Numeric value.
 *
 * @author matthieu.napoli
 */
class NumericValue implements Value
{
    /**
     * @var Calc_UnitValue
     */
    private $value;

    public function __construct(Calc_UnitValue $value)
    {
        $this->value = $value;
    }

    /**
     * @return Calc_UnitValue
     */
    public function getValue()
    {
        return $this->value;
    }
}
