<?php

namespace Techno\Domain\Element;

use Calc_Value;

/**
 * @author ronan.gorain
 * @author matthieu.napoli
 */
class CoeffElement extends Element
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
}
