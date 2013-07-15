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
    private $digitalValue;

    /**
     * Incertitude relative.
     * @var float|null
     */
    private $relativeUncertainty;

    /**
     * @param float|null $digitalValue
     * @param float|null $relativeUncertainty
     */
    public function __construct($digitalValue = null, $relativeUncertainty = null)
    {
        $this->digitalValue = is_numeric($digitalValue) ? floatval($digitalValue) : null;
        $this->relativeUncertainty = is_numeric($relativeUncertainty) ? floatval($relativeUncertainty) : null;
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

    /**
     * Export the object to a string representation
     * @see Calc_Value::createFromString
     * @return string
     */
    public function exportToString()
    {
        return $this->digitalValue . ';' . $this->relativeUncertainty;
    }

    /**
     * Creates a Value from a string representation
     * @see Calc_Value::exportToString
     * @param string $str
     * @throws InvalidArgumentException Invalid string
     * @return Calc_Value
     */
    public static function createFromString($str)
    {
        if (strpos($str, ';') === false) {
            throw new InvalidArgumentException("Invalid string");
        }

        list($digitalValue, $relativeUncertainty) = explode(';', $str);

        return new static($digitalValue, $relativeUncertainty);
    }

    /**
     * For debug purposes only
     * @return string
     */
    public function __toString()
    {
        if ($this->relativeUncertainty) {
            return "$this->digitalValue ± $this->relativeUncertainty %";
        } elseif ($this->digitalValue) {
            return (string) $this->digitalValue;
        }

        return '';
    }
}
