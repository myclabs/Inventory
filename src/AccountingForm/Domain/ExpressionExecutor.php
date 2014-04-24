<?php

namespace AccountingForm\Domain;

use AccountingForm\Domain\Value\Value;

/**
 * Executes an expression.
 *
 * @author matthieu.napoli
 */
interface ExpressionExecutor
{
    /**
     * @param string   $expression
     * @param ValueSet $values
     * @return Value result
     */
    public function execute($expression, ValueSet $values);
}
