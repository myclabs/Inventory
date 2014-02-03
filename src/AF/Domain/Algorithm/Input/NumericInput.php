<?php

namespace AF\Domain\Algorithm\Input;

use Calc_UnitValue;

/**
 * @author matthieu.napoli
 */
interface NumericInput extends Input
{
    /**
     * @return Calc_UnitValue
     */
    public function getValue();
}
