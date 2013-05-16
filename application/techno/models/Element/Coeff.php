<?php
/**
 * @author  ronan.gorain
 * @author  matthieu.napoli
 * @package Techno
 */

/**
 * Classe Coeff
 * @package    Techno
 * @subpackage Element
 */
class Techno_Model_Element_Coeff extends Techno_Model_Element
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
