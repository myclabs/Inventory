<?php
/**
 * @author  matthieu.napoli
 * @author  hugo.charbonnier
 * @author  yoann.croizer
 * @package Calc
 */

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
     * @var Unit_API
     */
    public $unit = null;

    /**
     * Value.
     *
     * @var Calc_Value
     */
    public $value = null;


    /**
     * @param Unit_API   $unit  Unité, optionnel
     * @param Calc_Value $value Valeur, optionnel
     */
    public function __construct(Unit_API $unit = null, Calc_Value $value = null)
    {
        $this->unit = $unit ? : new Unit_API();
        $this->value = $value ? : new Calc_Value();
    }

    /**
     * Permet de comparer deux unitValue entres elles.
     *
     * @param Calc_UnitValue $uvToCompare
     * @param string         $operator
     *
     * @return bool $result
     */
    public function toCompare(Calc_UnitValue $uvToCompare, $operator)
    {
        // Si la valeur à laquelle on compare est nulle.
        if (is_null($this->value->digitalValue)) {
            $unitValue1 = null;
        } else {
            $unitValue1 = (float) $this->value->digitalValue * $this->unit->getConversionFactor();
        }

        // Si l'utilisateur n'a pas entré de valeur.
        if (is_null($uvToCompare->value->digitalValue)) {
            $unitValue2 = null;
        } else {
            $unitValue2 = (float) $uvToCompare->value->digitalValue * $uvToCompare->unit->getConversionFactor();
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

}
