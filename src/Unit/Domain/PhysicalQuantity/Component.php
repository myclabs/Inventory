<?php
/**
 * Classe Component
 * @author  valentin.claras
 * @package Unit
 */

namespace Unit\Domain\PhysicalQuantity;

use Core_Model_Entity;
use Unit\Domain\PhysicalQuantity;

/**
 * Composant des grandeurs physique.
 * @package    Unit
 * @subpackage Model
 */
class Component extends Core_Model_Entity
{
    // Constantes de tri et filtres.
    const QUERY_PHYSICALQUANTITY_DERIVED = 'derivedPhysicalQuantity';
    const QUERY_PHYSICALQUANTITY_BASE = 'basePhysicalQuantity';
    const QUERY_EXPONENT = 'exponent';


    /**
     * Grandeur physique possédant la grandeur physique de base.
     * @var PhysicalQuantity
     */
    protected $derivedPhysicalQuantity;

    /**
     * Grandeur physique de base possédée par la grandeur physique dérivée.
     * @var PhysicalQuantity
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
     * @throws \Core_Exception_Database
     *
     * @return string
     */
    public static function getActivePoolName($poolName = 'default')
    {
        return PhysicalQuantity::getActivePoolName();
    }

    /**
     * Défini la grandeur physique dérivée.
     * @param PhysicalQuantity $derivedPhysicalQuantity
     */
    public function setDerivedPhysicalQuantity(PhysicalQuantity $derivedPhysicalQuantity)
    {
        $this->derivedPhysicalQuantity = $derivedPhysicalQuantity;
    }

    /**
     * Renvoi la grandeur physique dérivée.
     * @return PhysicalQuantity
     */
    public function getDerivedPhysicalQuantity()
    {
        return $this->derivedPhysicalQuantity;
    }

    /**
     * Défini la grandeur physique de base.
     * @param PhysicalQuantity $basePhysicalQuantity
     */
    public function setBasePhysicalQuantity(PhysicalQuantity $basePhysicalQuantity)
    {
        $this->basePhysicalQuantity = $basePhysicalQuantity;
    }

    /**
     * Renvoi la grandeur physique de base.
     * @return PhysicalQuantity
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