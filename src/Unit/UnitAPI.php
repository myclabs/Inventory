<?php

namespace Unit;

use Core\Translation\TranslatedString;
use Core_Exception;
use Unit\Domain\Unit\Unit;
use Unit\IncompatibleUnitsException;
use Unit\Domain\ComposedUnit\ComposedUnit;

/**
 * API
 * @author     valentin.claras
 * @author     hugo.charbonnier
 * @author     yoann.croizer
 */
class UnitAPI
{
    /**
     * Référent textuel d'une unité
     *  si le référent contient un "." c'et qu'il s'agit d'une unité composée.
     * @var string
     */
    protected $ref;

    /**
     * Cache des facteurs de conversion
     *  tableau à deux dimensions : this->ref,ref de l'unité vers laquelle on converti
     *  les valeurs sont les facteurs de conversion
     *  la deuxième clef est égale à la première si le facteur de conversion est vers les unités de base
     * @var array
     */
    protected static $conversionFactors = array();

    /**
     * Cache des équivalence entre unités
     *  tableau à deux dimensions : this->ref,ref de l'unité que l'on test
     *  les valeurs sont des booléens indiquant si les deux unités sont équivalentes
     * @var array
     */
    protected static $equivalentUnits = array();

    /**
     * Cache des symboles des unités
     *  tableau à une dimension : this->ref
     * @var array
     */
    protected static $symbols = array();

    /**
     * Constructeur.
     * @param string $ref
     */
    public function __construct($ref = null)
    {
        $this->ref = (string) $ref;
    }

    /**
     * Renvoie la ref.
     * @return string
     */
    public function __toString()
    {
        return $this->ref;
    }

    /**
     * Vérifie l'existence de l'unité
     *
     * @return bool
     */
    public function exists()
    {
        try {
            $this->getNormalizedUnit();
            return !empty($this->ref);
        } catch (Core_Exception $e) {
            return false;
        }
    }

    /**
     * Renvoi le référent textuel de l'API.
     * @return string
     */
    public function getRef()
    {
        return $this->ref;
    }

    /**
     * getSymbol()
     * Va prendre en paramètre le référent textuel d'une unité et va retourner le symbol de l'unité
     * sous forme de chaîne de caractères. La variable booléenne $html si elle est à "true" permet de
     * transformer par exemple un exposant de la forme m^2 en m<sup>2</sup>.
     *
     * @return TranslatedString
     */
    public function getSymbol()
    {
        if (!array_key_exists($this->ref, self::$symbols)) {
            $composedUnit = new ComposedUnit($this->ref);
            self::$symbols[$this->ref] = $composedUnit->getSymbol();
        }
        return self::$symbols[$this->ref];
    }


    /**
     * Vérifie qu'une ref est compatible avec l'unité.
     * @param string $ref
     * @return bool
     */
    public function isEquivalent($ref)
    {
        if (!array_key_exists($this->ref, self::$equivalentUnits)
            || !array_key_exists((string) $ref, self::$equivalentUnits[$this->ref])) {
            $composedUnit = new ComposedUnit($this->ref);
            self::$equivalentUnits[$this->ref][(string) $ref] = $composedUnit->isEquivalent(new ComposedUnit($ref));
        }
        return self::$equivalentUnits[$this->ref][(string) $ref];
    }

    /**
     * Cette méthode est utilisé soit pour récupérer le facteur de conversion :
     * -> d'une unité par rapport à son unité de référence ( param à null )
     * -> d'une unité par rapport à l'unité passé en paramètre ( dans le cas ou celles ci
     *     sont équivalentes)
     *
     * @param string $refUnit
     * @throws IncompatibleUnitsException
     * @return float Le facteur de conversion.
     */
    public function getConversionFactor($refUnit = null)
    {
        // Dans le cas ou on veut passer l'unité apellé dans l'unité passée en paramètre
        if (isset($refUnit)) {
            if (!array_key_exists($this->ref, self::$conversionFactors)
                || !array_key_exists((string) $refUnit, self::$conversionFactors[$this->ref])
            ) {
                if ($this->isEquivalent($refUnit)) {
                    $unit1 = new ComposedUnit($this->ref);
                    $factor1 = $unit1->getConversionFactor();
                    $unit2 = new ComposedUnit($refUnit);
                    $factor2 = $unit2->getConversionFactor();
                    self::$conversionFactors[$this->ref][(string) $refUnit] = $factor2 / $factor1;
                } else {
                    throw new IncompatibleUnitsException("Unit {$this->ref} is incompatible with $refUnit");
                }
            }
        } else {
            // Dans le cas ou on veut récupérer le facteur de conversion de l'unité de base
            $unit = new ComposedUnit($this->ref);
            return $unit->getConversionFactor();
        }
        return self::$conversionFactors[$this->ref][(string) $refUnit];
    }

    /**
     * Sert à multiplier des unités. Renvoie une chaine de caractère qui
     *  correspond à une unité composéé d'unités de référence de grandeur
     *   physique de base (univoque).
     * @param array $components
     * @return UnitAPI $api
     */
    public static function multiply($components)
    {
        $unit = new ComposedUnit();
        $result = $unit->multiply($components);
        $api = new UnitAPI($result->getRef());
        return $api;
    }

    /**
     * Sert à ajouter des unités entre elles. Renvoi une unité composée
     *  d'unités de référence de grandeur physique de base.
     * @param array $components
     * @return UnitAPI
     */
    public static function calculateSum($components)
    {
        $unit = new ComposedUnit();
        $result = $unit->calculateSum($components);
        $api = new UnitAPI($result->getRef());
        return $api;
    }

    /**
     * Renvoie la liste des refs des unités compatibles, càd de même grandeur physique.
     * @return UnitAPI[]
     */
    public function getCompatibleUnits()
    {
        $unit = new ComposedUnit($this->getRef());

        return array_map(
            function (ComposedUnit $unit) {
                return new UnitAPI($unit->getRef());
            },
            $unit->getCompatibleUnits()
        );
    }


    /**
     * Renvoie l'unité normalisée associée à une unité.
     * @return UnitAPI
     */
    public function getNormalizedUnit()
    {
        $unit = new ComposedUnit($this->ref);
        return new UnitAPI($unit->getNormalizedUnit()->getRef());
    }

    /**
     * Retourne l'inverse de l'unité
     * @return UnitAPI
     */
    public function reverse()
    {
        $composedUnit = new ComposedUnit($this->getRef());
        $composedUnit->reverseUnit();
        return new UnitAPI($composedUnit->getRef());
    }

}
