<?php

namespace Calculation\Domain\Value;

/**
 * Numeric value.
 *
 * @author matthieu.napoli
 */
interface NumericValue
{
    /**
     * @return \Calc_UnitValue
     */
    public function getValue();
}
