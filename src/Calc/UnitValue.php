<?php
/**
 * @author  matthieu.napoli
 * @author  hugo.charbonnier
 * @author  yoann.croizer
 * @package Calc
 */

use Unit\UnitAPI;

/**
 * Opérande de type unité/valeur.
 *
 * @package Calc
 */
class Calc_UnitValue
{
    // Constantes de classe.
    const RELATION_EQUAL = '==';
    const RELATION_NOTEQUAL = '!=';
    const RELATION_GT = '>>';
    const RELATION_LT = '<<';
    const RELATION_GE = '>=';
    const RELATION_LE = '<=';

    /**
     * Unité.
     *
     * @var UnitAPI
     */
    private $unit;

    /**
     * Value.
     *
     * @var Calc_Value
     */
    private $value;


    /**
     * @param UnitAPI    $unit
     * @param float|null $numericValue
     * @param float|null $uncertainty
     */
    public function __construct(UnitAPI $unit = null, $numericValue = null, $uncertainty = null)
    {
        $this->unit = $unit ? : new UnitAPI();
        $this->value = new Calc_Value($numericValue, $uncertainty);
    }

    /**
     * Copy the UnitValue to a new object and changes its numeric value
     * @param float|null $numericValue
     * @return Calc_UnitValue
     */
    public function copyWithNewValue($numericValue = null)
    {
        return new self($this->getUnit(), $numericValue, $this->getUncertainty());
    }

    /**
     * Copy the UnitValue to a new object and changes its uncertainty
     * @param float|null $uncertainty
     * @return Calc_UnitValue
     */
    public function copyWithNewUncertainty($uncertainty = null)
    {
        return new self($this->getUnit(), $this->getDigitalValue(), $uncertainty);
    }

    /**
     * Convert the value to a different unit
     * @param UnitAPI $unit
     * @return Calc_UnitValue
     */
    public function convertTo(UnitAPI $unit)
    {
        $numericValue = $this->value->getDigitalValue();
        if (is_null($numericValue)) {
            $newNumericValue = null;
        } else {
            $newNumericValue = (float) $numericValue / $this->unit->getConversionFactor($unit->getRef());
        }

        return new Calc_UnitValue($unit, $newNumericValue, $this->value->getUncertainty());
    }

    /**
     * Permet de comparer deux unitValue entres elles.
     *
     * Ne compare pas l'incertitude.
     *
     * @param Calc_UnitValue $uvToCompare
     * @param string         $operator
     *
     * @throws Core_Exception_InvalidArgument Unknow operation
     * @return bool $result
     */
    public function toCompare(Calc_UnitValue $uvToCompare, $operator)
    {
        // Si la valeur à laquelle on compare est nulle.
        if (is_null($this->value->getDigitalValue())) {
            $unitValue1 = null;
        } else {
            $unitValue1 = (float) $this->value->getDigitalValue();
        }

        // Si l'utilisateur n'a pas entré de valeur.
        if (is_null($uvToCompare->value->getDigitalValue())) {
            $unitValue2 = null;
        } else {
            $unitValue2 = (float) $uvToCompare->value->getDigitalValue();
        }

        // Conversion dans la même unité pour comparaison
        if ((! is_null($unitValue1)) && (! is_null($unitValue2))) {
            $unitValue2 = $unitValue2 / $uvToCompare->unit->getConversionFactor($this->unit);
        }

        switch ($operator) {
            case self::RELATION_GE:
                $result = $unitValue1 >= $unitValue2;
                break;
            case self::RELATION_GT:
                $result = $unitValue1 > $unitValue2;
                break;
            case self::RELATION_LE:
                $result = $unitValue1 <= $unitValue2;
                break;
            case self::RELATION_LT:
                $result = $unitValue1 < $unitValue2;
                break;
            case self::RELATION_EQUAL:
                $result = $unitValue1 === $unitValue2;
                break;
            case self::RELATION_NOTEQUAL:
                $result = $unitValue1 !== $unitValue2;
                break;
            default:
                throw new Core_Exception_InvalidArgument('Unknow operation.');
        }

        return $result;
    }

    /**
     * Retourne true si les objets sont égaux. Compare aussi l'incertitude.
     *
     * @param Calc_UnitValue $uvToCompare
     *
     * @return bool $result
     */
    public function equals(Calc_UnitValue $uvToCompare)
    {
        $equals = $this->toCompare($uvToCompare, self::RELATION_EQUAL);

        return $equals && ($this->getUncertainty() === $uvToCompare->getUncertainty());
    }

    /**
     * @return UnitAPI
     */
    public function getUnit()
    {
        return $this->unit;
    }

    /**
     * @return float|null
     */
    public function getDigitalValue()
    {
        return $this->value->getDigitalValue();
    }

    /**
     * @deprecated Utiliser getUncertainty()
     * @return float|null
     */
    public function getRelativeUncertainty()
    {
        return $this->getUncertainty();
    }

    /**
     * @return float|null
     */
    public function getUncertainty()
    {
        return $this->value->getUncertainty();
    }

    /**
     * Export the object to a string representation
     * @see Calc_UnitValue::createFromString
     * @return string
     */
    public function exportToString()
    {
        return $this->value->exportToString() . '|' . $this->unit->getRef();
    }

    /**
     * Creates a UnitValue from a string representation
     * @see Calc_UnitValue::exportToString
     * @param string $str
     * @throws InvalidArgumentException Invalid string
     * @return Calc_UnitValue
     */
    public static function createFromString($str)
    {
        if (strpos($str, '|') === false) {
            throw new InvalidArgumentException("Invalid string");
        }

        list($strValue, $unitRef) = explode('|', $str);

        $value = Calc_Value::createFromString($strValue);

        return new static(new UnitAPI($unitRef), $value->getDigitalValue(), $value->getUncertainty());
    }

    /**
     * For debug purposes only
     * @return string
     */
    public function __toString()
    {
        return "$this->value $this->unit";
    }
}
