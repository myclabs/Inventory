<?php
/**
 * Classe DiscreteUnit
 * @author  valentin.claras
 * @author  hugo.charbonnier
 * @author  yoann.croizer
 * @package Unit
 */

namespace Unit\Domain\Unit;

/**
 * Unité Discrete
 * @package    Unit
 * @subpackage Model
 */
class DiscreteUnit extends Unit
{
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
        return Unit::getActivePoolName();
    }

    /**
     * Retourne l'objet Unit à partir de son référent textuel.
     * @param string $ref
     * @return \Unit\Domain\Unit\DiscreteUnit
     */
    public static function loadByRef($ref)
    {
        return parent::loadByRef($ref);
    }

    /**
     * Renvoi l'unité de reference (elle-meme).
     * @return \Unit\Domain\Unit\DiscreteUnit
     */
    public function getReferenceUnit()
    {
        return $this;
    }

    /**
     * Renvoi le facteur de Conversion de l'unité
     * @param Unit $unit
     * @throws \Core_Exception_InvalidArgument Units need to be the same
     * @return int 1
     */
    public function getConversionFactor(Unit $unit)
    {
        if ($this->getKey() != $unit->getKey()) {
            throw new \Core_Exception_InvalidArgument('Units need to be the same');
        }
        return 1;
    }

    /**
     * {@inheritdoc}
     */
    public function getEquivalentUnits()
    {
        return [];
    }

}