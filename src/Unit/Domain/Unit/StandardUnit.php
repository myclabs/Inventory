<?php
/**
 * @author  valentin.claras
 * @author  hugo.charbonnier
 * @author  yoann.croizer
 * @package Unit
 */

namespace Unit\Domain\Unit;

use Core_Model_Query;
use Unit\Domain\PhysicalQuantity;
use Unit\Domain\PhysicalQuantity\Component;
use Unit\Domain\UnitSystem;

/**
 * Unité standard
 * @package    Unit
 * @subpackage Model
 */
class StandardUnit extends Unit
{
    // Constantes de tri et filtres.
    const QUERY_MULTIPLIER = 'multiplier';
    const QUERY_PHYSICALQUANTITY = 'physicalQuantity';
    const QUERY_UNITSYSTEM = 'unitSystem';

    /**
     * Coefficient mutliplicateur d'une unité standard.
     * Permet par exemple de savoir le rapport entre km et m.
     * @var float
     */
    protected $multiplier = null;

    /**
     * Identifiant de la gandeur physique associée à l'unité standard.
     * @var PhysicalQuantity
     */
    protected $physicalQuantity = null;

    /**
     * Identifiant du système d'unité associé à l'unité standard.
     * @var UnitSystem
     */
    protected $unitSystem = null;


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
     * @return \Unit\Domain\Unit\StandardUnit
     */
    public static function loadByRef($ref)
    {
        return parent::loadByRef($ref);
    }

    /**
     * Défini le coefficient multiplicateur de l'unité.
     * @param float $multiplier
     */
    public function setMultiplier($multiplier)
    {
        $this->multiplier = $multiplier;
    }

    /**
     * Renvoie le coefficient multiplicateur.
     * @throws \Core_Exception_UndefinedAttribute
     * @return float
     */
    public function getMultiplier()
    {
        if ($this->multiplier === null) {
            throw new \Core_Exception_UndefinedAttribute('Multiplier has not be defined');
        }
        return $this->multiplier;
    }

    /**
     * Definit la grandeur Physique associé à l'unité.
     * @param PhysicalQuantity $physicalQuantity
     */
    public function setPhysicalQuantity(PhysicalQuantity $physicalQuantity)
    {
        $this->physicalQuantity = $physicalQuantity;
    }

    /**
     * Renvoie la Grandeur physique Derivée associé
     * @throws \Core_Exception_UndefinedAttribute
     * @return PhysicalQuantity
     */
    public function getPhysicalQuantity()
    {
        if ($this->physicalQuantity == null) {
            throw new \Core_Exception_UndefinedAttribute('Physical Quantity has not be defined');
        }
        return $this->physicalQuantity;
    }

    /**
     * Definit le systeme d'unité associé à l'unité.
     * @param \Unit\Domain\UnitSystem $unitSystem
     */
    public function setUnitSystem(UnitSystem $unitSystem)
    {
        $this->unitSystem = $unitSystem;
    }

    /**
     * Renvoie le SystemeUnite associé.
     * @throws \Core_Exception_UndefinedAttribute
     * @return UnitSystem
     */
    public function getUnitSystem()
    {
        if ($this->unitSystem == null) {
            throw new \Core_Exception_UndefinedAttribute('System Unit has not be defined');
        }
        return $this->unitSystem;
    }

    /**
     * Renvoie l'unité de reference par rapport à l'unité
     * @return \Unit\Domain\Unit\StandardUnit
     */
    public function getReferenceUnit()
    {
        return $this->getPhysicalQuantity()->getReferenceUnit();
    }

    /**
     * Renvoi le facteur de Conversion de l'unité
     * @param Unit $unit
     * @return float
     */
    public function getConversionFactor(Unit $unit)
    {
        if ($this->getPhysicalQuantity()->getKey() != $unit->getPhysicalQuantity()->getKey()) {
            throw new \Core_Exception_InvalidArgument('Units need to have same PhysicalQuantity.');
        }
        return $this->getMultiplier() / $unit->getMultiplier();
    }

    /**
     * Retourne un tableau contenant la conversion de l'unité standard en unités normalisées
     * @return array De la forme ('unit' => StandardUnit, 'exponent' => int).
     */
    public function getNormalizedUnit()
    {
        $tabResults = array();

        /* @var $physicalQuantityComponent Component */
        foreach ($this->getPhysicalQuantity()->getPhysicalQuantityComponents() as $physicalQuantityComponent) {
            $tabResults[] = array(
                'unit'     => $physicalQuantityComponent->getBasePhysicalQuantity()->getReferenceUnit(),
                'exponent' => $physicalQuantityComponent->getExponent()
            );
        }

        return $tabResults;
    }

    /**
     * {@inheritdoc}
     */
    public function getEquivalentUnits()
    {
        $units = $this->getPhysicalQuantity()->getUnits();

        // Filtre l'unité courante de la liste
        return array_filter(
            $units,
            function (Unit $unit) {
                return $unit !== $this;
            }
        );
    }

}
