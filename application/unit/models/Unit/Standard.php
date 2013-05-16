<?php
/**
 * Classe Unit_Model_Unit_Standard
 * @author valentin.claras
 * @author hugo.charbonnier
 * @author yoann.croizer
 * @package Unit
 */

/**
 * Unité standard
 * @package Unit
 * @subpackage Model
 */
class Unit_Model_Unit_Standard extends Unit_Model_Unit
{
    // Constantes de tri et filtres.
    const QUERY_MULTIPLIER = 'multiplier';
    const QUERY_PHYSICALQUANTITY = 'physicalQuantity';
    const QUERY_UNITSYSTEM = 'unitSystem';

    /**
     * Coefficient mutliplicateur d'une unité standard.
     * Permet par exemple de savoir le rapport entre km et m.
     * @var int
     */
    protected $multiplier = null;

    /**
     * Identifiant de la gandeur physique associée à l'unité standard.
     * @var Unit_Model_PhysicalQuantity
     */
    protected $physicalQuantity = null;

    /**
     * Identifiant du système d'unité associé à l'unité standard.
     * @var Unit_Model_Unit_System
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
        return Unit_Model_Unit::getActivePoolName();
    }
    /**
     * Retourne l'objet Unit à partir de son référent textuel.
     * @param string $ref
     * @return Unit_Model_Unit_Standard
     */
    public static function loadByRef($ref)
    {
        return parent::loadByRef($ref);
    }

    /**
     * Défini le coefficient multiplicateur de l'unité.
     * @param int $multiplier
     */
    public function setMultiplier($multiplier)
    {
        $this->multiplier = $multiplier;
    }

    /**
     * Renvoie le coefficient multiplicateur.
     * @throws Core_Exception_UndefinedAttribute
     * @return int
     */
    public function getMultiplier()
    {
        if ($this->multiplier === null) {
            throw new Core_Exception_UndefinedAttribute('Multiplier has not be defined');
        }
        return $this->multiplier;
    }

    /**
     * Definit la grandeur Physique associé à l'unité.
     * @param Unit_Model_PhysicalQuantity $physicalQuantity
     */
    public function setPhysicalQuantity(Unit_Model_PhysicalQuantity $physicalQuantity)
    {
        $this->physicalQuantity = $physicalQuantity;
    }

    /**
     * Renvoie la Grandeur physique Derivée associé
     * @throws Core_Exception_UndefinedAttribute
     * @return Unit_Model_PhysicalQuantity
     */
    public function getPhysicalQuantity()
    {
        if ($this->physicalQuantity == null) {
            throw new Core_Exception_UndefinedAttribute('Physical Quantity has not be defined');
        }
        return $this->physicalQuantity;
    }

    /**
     * Definit le systeme d'unité associé à l'unité.
     * @param Unit_Model_Unit_System $unitSystem
     */
    public function setUnitSystem(Unit_Model_Unit_System $unitSystem)
    {
        $this->unitSystem= $unitSystem;
    }

    /**
     * Renvoie le SystemeUnite associé.
     * @throws Core_Exception_UndefinedAttribute
     * @return Unit_Model_Unit_System
     */
    public function getUnitSystem()
    {
        if ($this->unitSystem == null) {
            throw new Core_Exception_UndefinedAttribute('System Unit has not be defined');
        }
        return $this->unitSystem;
    }

    /**
     * Renvoie l'unité de reference par rapport à l'unité
     * @return Unit_Model_Unit_Standard
     */
    public function getReferenceUnit()
    {
        return $this->getPhysicalQuantity()->getReferenceUnit();
    }

    /**
     * Renvoi le facteur de Conversion de l'unité
     * @param Unit_Model_Unit $unit
     * @return float
     */
    public function getConversionFactor(Unit_Model_Unit $unit)
    {
        if ($this->getPhysicalQuantity()->getKey() != $unit->getPhysicalQuantity()->getKey()) {
            throw new Core_Exception_InvalidArgument('Units need to have same PhysicalQuantity.');
        }
        return $this->getMultiplier() / $unit->getMultiplier();
    }

    /**
     * Retourne un tableau contenant la conversion de l'unité standard en unités normalisées
     * @return array De la forme ('unit' => Unit_Model_Unit_Standard, 'exponent' => int).
     */
    public function getNormalizedUnit()
    {
        $tabResults = array();

        /* @var $physicalQuantityComponent Unit_Model_PhysicalQuantity_Component */
        foreach ($this->getPhysicalQuantity()->getPhysicalQuantityComponents() as $physicalQuantityComponent) {
            $tabResults[] = array(
                    'unit' => $physicalQuantityComponent->getBasePhysicalQuantity()->getReferenceUnit(),
                    'exponent' => $physicalQuantityComponent->getExponent()
                );
        }

        return $tabResults;
    }

}