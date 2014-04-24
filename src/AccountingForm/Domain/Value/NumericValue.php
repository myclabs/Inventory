<?php

namespace AccountingForm\Domain\Value;

use Calc_UnitValue;
use Unit\UnitAPI;

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

    /**
     * @return float|null
     */
    public function getNumericValue()
    {
        return $this->value->getDigitalValue();
    }

    /**
     * @return float|null
     */
    public function getRelativeUncertainty()
    {
        return $this->value->getRelativeUncertainty();
    }

    /**
     * @return UnitAPI
     */
    public function getUnit()
    {
        return $this->value->getUnit();
    }

    /**
     * @param UnitAPI $unit
     * @return NumericValue
     */
    public function convertTo(UnitAPI $unit)
    {
        return new self($this->value->convertTo($unit));
    }
}
