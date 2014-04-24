<?php

namespace AccountingForm\Domain\Processing\ParameterImporter;

use AccountingForm\Domain\ValueSet;
use AF\Domain\Algorithm\AlgoConfigurationError;

/**
 * Coordonée dans une dimension d'une famille.
 *
 * @author matthieu.napoli
 * @author cyril.perraud
 */
abstract class ParameterCoordinate
{
    /**
     * @var string
     */
    protected $dimensionRef;

    /**
     * @param string $dimensionRef
     */
    public function __construct($dimensionRef)
    {
        $this->dimensionRef = (string) $dimensionRef;
    }

    /**
     * @param ValueSet $values
     * @return string
     */
    abstract public function getMemberRef(ValueSet $values);

    /**
     * @return AlgoConfigurationError[]
     */
    abstract public function validate();

    /**
     * @return string
     */
    public function getDimensionRef()
    {
        return $this->dimensionRef;
    }
}
