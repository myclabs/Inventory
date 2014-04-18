<?php

namespace Calculation\Domain;

use Calculation\Domain\Value\Value;

/**
 * A set of values implemented with an array.
 *
 * @author matthieu.napoli
 */
class ArrayValueSet implements ValueSet
{
    /**
     * @var Value[]
     */
    private $values = [];

    /**
     * @param Value[] $values
     */
    public function __construct(array $values = [])
    {
        $this->values = $values;
    }

    /**
     * {@inheritdoc}
     */
    public function getValue($key)
    {
        if (! $this->hasValue($key)) {
            throw new \Core_Exception_NotFound;
        }

        return $this->values[$key];
    }

    /**
     * {@inheritdoc}
     */
    public function hasValue($key)
    {
        return array_key_exists($key, $this->values);
    }

    /**
     * {@inheritdoc}
     */
    public function getAllValues()
    {
        return $this->values;
    }

    public function add(ValueSet $valueSet)
    {
        $this->values += $valueSet->getAllValues();
    }

    public function set($key, Value $value)
    {
        $this->values[$key] = $value;
    }
}
