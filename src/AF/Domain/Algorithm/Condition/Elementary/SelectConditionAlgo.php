<?php

namespace AF\Domain\Algorithm\Condition\Elementary;

use AF\Domain\Algorithm\Condition\ElementaryConditionAlgo;

/**
 * @author matthieu.napoli
 */
abstract class SelectConditionAlgo extends ElementaryConditionAlgo
{
    /**
     * Filtre sur la valeur
     */
    const QUERY_VALUE = 'value';

    /**
     * @var string
     */
    protected $value;

    /**
     * @return string
     */
    public function getValue()
    {
        return $this->value;
    }

    /**
     * @param string $value
     */
    public function setValue($value)
    {
        $this->value = $value;
    }
}
