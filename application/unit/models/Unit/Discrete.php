<?php
/**
 * Classe Unit_Model_Unit_Discrete
 * @author valentin.claras
 * @author hugo.charbonnier
 * @author yoann.croizer
 * @package Unit
 */

/**
 * Unité Discrete
 * @package Unit
 * @subpackage Model
 */
class Unit_Model_Unit_Discrete extends Unit_Model_Unit
{
    /**
     * Constructeur
     */
    public function __construct()
    {
        // Le Symbole n'etant pas utile dans les unités discretes, il vaut le nom.
        $this->symbol = & $this->name;
    }

    /**
     * Renvoie la référence de la pool active.
     *  Il s'agit de l'entityManager correspondant.
     *
     * @return string
     */
    public static function getActivePoolName()
    {
        return Unit_Model_Unit::getActivePoolName();
    }

    /**
     * Retourne l'objet Unit à partir de son référent textuel.
     * @param string $ref
     * @return Unit_Model_Unit_Discrete
     */
    public static function loadByRef($ref)
    {
        return parent::loadByRef($ref);
    }

    /**
     * Renvoi l'unité de reference (elle-meme).
     * @return Unit_Model_Unit_Discrete
     */
    public function getReferenceUnit()
    {
        return $this;
    }

    /**
     * Renvoi le facteur de Conversion de l'unité
     * @param Unit_Model_Unit $unit
     * @throws Core_Exception_InvalidArgument Units need to be the same
     * @return int 1
     */
    public function getConversionFactor(Unit_Model_Unit $unit)
    {
        if ($this->getKey() != $unit->getKey()) {
            throw new Core_Exception_InvalidArgument('Units need to be the same');
        }
        return 1;
    }

}