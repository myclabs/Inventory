<?php
/**
 * @author valentin.claras
 * @author hugo.charbonnier
 * @author yoann.croizer
 * @author matthieu.napoli
 * @package Calc
 */

/**
 * Operande de type value.
 * @package Calc
 */
class Calc_Value
{
    /**
     * Valeur digitale.
     * @var float|null
     */
    public $digitalValue;

    /**
     * Incertitude relative.
     * @var float|null
     */
    public $relativeUncertainty;

    /**
     * @param float|null $digitalValue
     * @param float|null $relativeUncertainty
     */
    public function __construct($digitalValue = null, $relativeUncertainty = null)
    {
        $this->digitalValue = $digitalValue;
        $this->relativeUncertainty = $relativeUncertainty;
    }

    /**
     * Copy the Value to a new object and changes its digital value
     * @param float|null $digitalValue
     * @return Calc_Value
     */
    public function copyWithNewValue($digitalValue = null)
    {
        return new self($digitalValue, $this->getRelativeUncertainty());
    }

    /**
     * Copy the Value to a new object and changes its relative uncertainty
     * @param float|null $relativeUncertainty
     * @return Calc_Value
     */
    public function copyWithNewUncertainty($relativeUncertainty = null)
    {
        return new self($this->getDigitalValue(), $relativeUncertainty);
    }

    /**
     * @return float|null
     */
    public function getDigitalValue()
    {
        return $this->digitalValue;
    }

    /**
     * @return float|null
     */
    public function getRelativeUncertainty()
    {
        return $this->relativeUncertainty;
    }
}
