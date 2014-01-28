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
     * @param float|null $digitalValue
     * @param float|null $relativeUncertainty
     */
    public function __construct(UnitAPI $unit = null, $digitalValue = null, $relativeUncertainty = null)
    {
        $this->unit = $unit ? : new UnitAPI();
        $this->value = new Calc_Value($digitalValue, $relativeUncertainty);
    }

    /**
     * Copy the UnitValue to a new object and changes its digital value
     * @param float|null $digitalValue
     * @return Calc_UnitValue
     */
    public function copyWithNewValue($digitalValue = null)
    {
        return new self($this->getUnit(), $digitalValue, $this->getRelativeUncertainty());
    }

    /**
     * Copy the UnitValue to a new object and changes its relative uncertainty
     * @param float|null $relativeUncertainty
     * @return Calc_UnitValue
     */
    public function copyWithNewUncertainty($relativeUncertainty = null)
    {
        return new self($this->getUnit(), $this->getDigitalValue(), $relativeUncertainty);
    }

    /**
     * Convert the value to a different unit
     * @param UnitAPI $unit
     * @return Calc_UnitValue
     */
    public function convertTo(UnitAPI $unit)
    {
        $digitalValue = $this->value->getDigitalValue();
        if (is_null($digitalValue)) {
            $newDigitalValue = null;
        } else {
            $newDigitalValue = (float) $digitalValue * $this->unit->getConversionFactor($unit->getRef());
        }

        return new Calc_UnitValue($unit, $newDigitalValue, $this->value->getRelativeUncertainty());
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
            $unitValue2 = $unitValue2 * $uvToCompare->unit->getConversionFactor($this->unit);
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

        return $equals && ($this->getRelativeUncertainty() === $uvToCompare->getRelativeUncertainty());
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
     * @return float|null
     */
    public function getRelativeUncertainty()
    {
        return $this->value->getRelativeUncertainty();
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

        return new static(new UnitAPI($unitRef), $value->getDigitalValue(), $value->getRelativeUncertainty());
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
