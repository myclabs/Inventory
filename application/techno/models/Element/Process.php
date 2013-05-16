<?php
/**
 * @author ronan.gorain
 * @author matthieu.napoli
 * @package Techno
 */

/**
 * Classe Process
 * @package Techno
 * @subpackage Element
 */
class Techno_Model_Element_Process extends Techno_Model_Element
{

    /**
     * La valeur du coefficient est exprimée en unité de référence
     * des grandeurs physiques de base
     * @var Calc_Value $value
     */
    protected $value;

    /**
     * Initialise des valeurs par défaut
     */
    public function __construct()
    {
        $this->value = new Calc_Value();
    }

    /**
     * La valeur du coefficient est exprimée en unité de référence
     * des grandeurs physiques de base
     * @param Calc_Value $value
     */
    public function setValue(Calc_Value $value) {
        $this->value = $value;
    }

    /**
     * La valeur du coefficient est exprimée en unité de référence
     * des grandeurs physiques de base
     * @return Calc_Value
     */
    public function getValue() {
        return $this->value;
    }

    /**
     * {@inheritdoc}
     */
    public function getValueUnit()
    {
        // Retourne kg eq. CO2 divisé par l'unité
        $unitInverse = $this->getUnit()->reverse();
        return new Unit_API($unitInverse->getRef().'.kg_co2e');
    }

}
