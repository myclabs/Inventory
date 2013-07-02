<?php
/**
 * Classe Unit_API
 * @author valentin.claras
 * @author hugo.charbonnier
 * @author yoann.croizer
 * @package Unit
 * @subpackage API
 */

/**
 * API
 * @package Unit
 * @subpackage API
 */
class Unit_API
{
    /**
     * Référent textuel d'une unité
     *  si le référent contient un "." c'et qu'il s'agit d'une unité composée.
     * @var String
     */
    protected $ref;


    /**
     * Constructeur.
     * @param String $ref
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
        } catch (Core_Exception_InvalidArgument $e) {
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
     * @return Unit_API unitSymbol
     */
    public function getSymbol()
    {
        $composedUnit = new Unit_ComposedUnit($this->ref);
        return $composedUnit->getSymbol();
    }


    /**
     * Vérifie qu'une ref est compatible avec l'unité.
     * @param string $ref
     * @return bool
     */
    public function isEquivalent($ref)
    {
        $composedUnit = new Unit_ComposedUnit($this->ref);
        return $composedUnit->isEquivalent(new Unit_ComposedUnit($ref));
    }

    /**
     * Cette méthode est utilisé soit pour récupérer le facteur de conversion :
     * -> d'une unité par rapport à son unité de référence ( param à null )
     * -> d'une unité par rapport à l'unité passé en paramètre ( dans le cas ou celles ci
     *     sont équivalentes)
     *
     * @param string $refUnit
     * @throws Unit_Exception_IncompatibleUnits
     * @return float Le facteur de conversion.
     */
    public function getConversionFactor($refUnit = null)
    {
        $unit1   = new Unit_ComposedUnit($this->ref);
        $factor1 = $unit1->getConversionFactor();

        // Dans le cas ou on veut passer l'unité apellé dans l'unité passée en paramètre
        if (isset($refUnit)) {
            if ($this->isEquivalent($refUnit)) {
                $unit2   = new Unit_ComposedUnit($refUnit);
                $factor2 = $unit2->getConversionFactor();
                $result  = $factor2 / $factor1;
            } else {
                throw new Unit_Exception_IncompatibleUnits("Unit {$this->ref} is incompatible with $refUnit");
            }
        }
        // Dans le cas ou on veut récupérer le facteur de conversion de l'unité de base
        else {
            $result = $factor1;
        }
        return $result;
    }

    /**
     * Sert à multiplier des unités. Renvoie une chaine de caractère qui
     *  correspond à une unité composéé d'unités de référence de grandeur
     *   physique de base (univoque).
     * @param array $components
     * @return Unit_API $api
     */
    public static function multiply($components)
    {
        $unit = new Unit_ComposedUnit();
        $result = $unit->multiply($components);
        $api = new Unit_API($result->getRef());
        return $api;
    }

    /**
     * Sert à ajouter des unités entre elles. Renvoi une unité composée
     *  d'unités de référence de grandeur physique de base.
     * @param array $components
     * @return Unit_API
     */
    public static function calculateSum($components)
    {
        $unit = new Unit_ComposedUnit();
        $result = $unit->calculateSum($components);
        $api = new Unit_API($result->getRef());
        return $api;
    }

    /**
     * Renvoie la liste des refs des unités compatibles, càd de même grandeur physique.
     * @return array
     */
    public function getSamePhysicalQuantityUnits()
    {
        $unit = Unit_Model_Unit_Standard::loadByRef($this->getRef());

        $queryCompatibleUnits = new Core_Model_Query();
        $queryCompatibleUnits->filter->addCondition(
                Unit_Model_Unit_Standard::QUERY_PHYSICALQUANTITY,
                $unit->getPhysicalQuantity()
            );

        $refs = array();

        foreach (Unit_Model_Unit_Standard::loadList($queryCompatibleUnits) as $standardUnit) {
            $refs[] = new Unit_API($standardUnit->getRef());
        }

        return $refs;
    }


    /**
     * Renvoie l'unité normalisée associée à une unité.
     * @return Unit_API
     */
    public function getNormalizedUnit()
    {
        $unit = new Unit_ComposedUnit($this->ref);
        return new Unit_API($unit->getNormalizedUnit()->getRef());
    }

    /**
     * Retourne l'inverse de l'unité
     * @return Unit_API
     */
    public function reverse()
    {
        $composedUnit = new Unit_ComposedUnit($this->getRef());
        $composedUnit->reverseUnit();
        return new Unit_API($composedUnit->getRef());
    }

}
