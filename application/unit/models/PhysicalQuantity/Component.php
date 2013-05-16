<?php
/**
 * Classe Unit_Model_PhysicalQuantity_Component
 * @author valentin.claras
 * @package Unit
 */

/**
 * Composant des grandeurs physique.
 * @package Unit
 * @subpackage Model
 */
class Unit_Model_PhysicalQuantity_Component extends Core_Model_Entity
{
    // Constantes de tri et filtres.
    const QUERY_PHYSICALQUANTITY_DERIVED = 'derivedPhysicalQuantity';
    const QUERY_PHYSICALQUANTITY_BASE = 'basePhysicalQuantity';
    const QUERY_EXPONENT = 'exponent';


    /**
     * Grandeur physique possédant la grandeur physique de base.
     * @var Unit_Model_PhysicalQuantity
     */
    protected $derivedPhysicalQuantity;

    /**
     * Grandeur physique de base possédée par la grandeur physique dérivée.
     * @var Unit_Model_PhysicalQuantity
     */
    protected $basePhysicalQuantity;

    /**
     * Exposant que possède la grandeur physique de base au sein de la grandeur physique dérivée.
     * @var int
     */
    protected $exponent;


    /**
     * Définit la pool d'objet active.
     *  Il s'agit de l'entityManager correspondant.
     *
     * @param string $poolName
     *
     * @throws Core_Exception_Database
     *
     * @return void
     */
    public static function getActivePoolName($poolName='default')
    {
        return Unit_Model_PhysicalQuantity::getActivePoolName();
    }

    /**
     * Défini la grandeur physique dérivée.
     * @param Unit_Model_PhysicalQuantity $derivedPhysicalQuantity
     */
    public function setDerivedPhysicalQuantity(Unit_Model_PhysicalQuantity $derivedPhysicalQuantity)
    {
        $this->derivedPhysicalQuantity = $derivedPhysicalQuantity;
    }

    /**
     * Renvoi la grandeur physique dérivée.
     * @return Unit_Model_PhysicalQuantity
     */
    public function getDerivedPhysicalQuantity()
    {
        return $this->derivedPhysicalQuantity;
    }

    /**
     * Défini la grandeur physique de base.
     * @param Unit_Model_PhysicalQuantity $basePhysicalQuantity
     */
    public function setBasePhysicalQuantity(Unit_Model_PhysicalQuantity $basePhysicalQuantity)
    {
        $this->basePhysicalQuantity = $basePhysicalQuantity;
    }

    /**
     * Renvoi la grandeur physique de base.
     * @return Unit_Model_PhysicalQuantity
     */
    public function getBasePhysicalQuantity()
    {
        return $this->basePhysicalQuantity;
    }

    /**
     * Défini l'exposant auquel est associé la grandeur physique de base dans la grandeur physique dérivée.
     * @param int $exponent
     */
    public function setExponent($exponent)
    {
        $this->exponent = $exponent;
    }

    /**
     * Renvoi l'exposant auquel est associé la grandeur physique de base dans la grandeur physique dérivée.
     * @return int
     */
    public function getExponent()
    {
        return $this->exponent;
    }

}