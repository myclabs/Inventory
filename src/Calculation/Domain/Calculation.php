<?php

namespace Calculation\Domain;

/**
 * Calculation.
 *
 * @author matthieu.napoli
 */
interface Calculation
{
    /**
     * @param ValueSet $input
     * @return ValueSet Result
     */
    public function calculate(ValueSet $input);
}
