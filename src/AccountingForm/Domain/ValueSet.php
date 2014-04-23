<?php

namespace AccountingForm\Domain;

use AccountingForm\Domain\Value\Value;

/**
 * A set of values.
 *
 * @author matthieu.napoli
 */
interface ValueSet
{
    /**
     * @param string $key
     * @throws \Core_Exception_NotFound
     * @return Value
     */
    public function getValue($key);

    /**
     * @param string $key
     * @return boolean
     */
    public function hasValue($key);

    /**
     * @return Value[]
     */
    public function getAllValues();
}
