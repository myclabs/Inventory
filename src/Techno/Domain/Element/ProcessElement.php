<?php

namespace Techno\Domain\Element;

use Calc_Value;
use Unit\UnitAPI;

/**
 * @author ronan.gorain
 * @author matthieu.napoli
 */
class ProcessElement extends Element
{
    /**
     * La valeur du coefficient est exprimée en unité de référence
     * des grandeurs physiques de base
     * @var Calc_Value $value
     */
    protected $value;

    public function __construct()
    {
        $this->value = new Calc_Value();
    }

    /**
     * La valeur du coefficient est exprimée en unité de référence
     * des grandeurs physiques de base
     * @param Calc_Value $value
     */
    public function setValue(Calc_Value $value)
    {
        $this->value = $value;
    }

    /**
     * La valeur du coefficient est exprimée en unité de référence
     * des grandeurs physiques de base
     * @return Calc_Value
     */
    public function getValue()
    {
        return $this->value;
    }

    /**
     * {@inheritdoc}
     */
    public function getValueUnit()
    {
        // Retourne kg eq. CO2 divisé par l'unité
        $unitInverse = $this->getUnit()->reverse();
        return new UnitAPI($unitInverse->getRef() . '.kg_co2e');
    }
}
