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
     * Valeur numérique.
     * @var float|null
     */
    private $digitalValue;

    /**
     * Incertitude relative.
     * @var float|null
     */
    private $relativeUncertainty;

    /**
     * @param float|null $numericValue
     * @param float|null $uncertainty
     */
    public function __construct($numericValue = null, $uncertainty = null)
    {
        $this->digitalValue = is_numeric($numericValue) ? floatval($numericValue) : null;
        $this->relativeUncertainty = is_numeric($uncertainty) ? intval($uncertainty) : null;
    }

    /**
     * Copy the value to a new object and changes its digital value
     * @param float|null $numericValue
     * @return Calc_Value
     */
    public function copyWithNewValue($numericValue = null)
    {
        return new self($numericValue, $this->relativeUncertainty);
    }

    /**
     * Copy the Value to a new object and changes its uncertainty
     * @param float|null $uncertainty
     * @return Calc_Value
     */
    public function copyWithNewUncertainty($uncertainty = null)
    {
        return new self($this->getDigitalValue(), $uncertainty);
    }

    /**
     * @return float|null
     */
    public function getDigitalValue()
    {
        return $this->digitalValue;
    }

    /**
     * @deprecated Préferer getUncertainty()
     * @return float|null
     */
    public function getRelativeUncertainty()
    {
        return $this->relativeUncertainty;
    }

    /**
     * @return float|null
     */
    public function getUncertainty()
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

        list($numericValue, $uncertainty) = explode(';', $str);

        return new static($numericValue, $uncertainty);
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
