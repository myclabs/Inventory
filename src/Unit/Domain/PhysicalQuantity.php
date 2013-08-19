<?php
/**
 * @author  valentin.claras
 * @author  hugo.charbonnier
 * @author  yoann.croizer
 * @package Unit
 */

namespace Unit\Domain;

use Core_Model_Entity;
use Core_Model_Entity_Translatable;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Unit\Domain\PhysicalQuantity\Component;
use Unit\Domain\Unit\StandardUnit;

/**
 * Grandeur Physique
 * @package    Unit
 * @subpackage Model
 */
class PhysicalQuantity extends Core_Model_Entity
{

    use Core_Model_Entity_Translatable;

    // Constantes de tri et filtres.
    const QUERY_ID = 'id';
    const QUERY_NAME = 'name';
    const QUERY_ISBASE = 'isBase';
    const QUERY_BASEPHYSICALQUANTITY = '_composedPhysicalQuantities';


    /**
     * Identifiant d'une grandeur physique
     * @var int
     */
    protected $id;

    /**
     * Référent textuel d'une gandeur physique
     * @var String
     */
    protected $ref;

    /**
     *
     * Nom d'une grandeur physique
     * @var String
     */
    protected $name;

    /**
     * Symbole d'une grandeur physique
     * @var String
     */
    protected $symbol;

    /**
     *
     * Permet de savoir s'il s'agit d'une grandeur physique de base
     * si c'est à true, ou d'une grandeur physique dérivee si c'est à false.
     * @var bool
     */
    protected $isBase = true;

    /**
     * Unité de référence de la grandeur physique.
     * @var StandardUnit
     */
    protected $referenceUnit = null;

    /**
     * Permet de connaitre la composition d'une grandeur physique derivee en grandeur physique de base.
     * @var Collection
     */
    protected $physicalQuantityComponents;


    /**
     * Construit l'objet et l'enregistre dans l'EntityManager.
     */
    public function __construct()
    {
        $this->physicalQuantityComponents = new ArrayCollection();
    }

    /**
     * Fonction appelée avant un delete (spécifié dans le mapper).
     *
     * @return void
     */
    public function preDelete()
    {
        $this->deletePhysicalQuantityComponents();
    }

    /**
     * Supprime les physicalQuantityComponent
     *
     * @return void
     */
    protected function deletePhysicalQuantityComponents()
    {
        foreach ($this->physicalQuantityComponents as $physicalQuantityComponent) {
            $physicalQuantityComponent->delete();
        }
    }

    /**
     * Charge une Grandeur physique par son Reférent textuel.
     * @param String $ref
     * @return \Unit\Domain\PhysicalQuantity
     */
    public static function loadByRef($ref)
    {
        return self::getEntityRepository()->loadBy(array('ref' => $ref));
    }

    /**
     * Défini le référent textuel de la grandeur physique.
     * @param string $ref
     */
    public function setRef($ref)
    {
        $this->ref = $ref;
    }

    /**
     * Renvoi le référent textuel de la grandeur textuel.
     * @return string
     */
    public function getRef()
    {
        return $this->ref;
    }

    /**
     * Défini le nom de la grandeur physique.
     * @param string $name
     */
    public function setName($name)
    {
        $this->name = $name;
    }

    /**
     * Renvoi le nom de la grandeur textuel.
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Défini le symbole de la grandeur physique.
     * @param string $symbol
     */
    public function setSymbol($symbol)
    {
        $this->symbol = $symbol;
    }

    /**
     * Renvoi le symbole de la grandeur textuel.
     * @return string
     */
    public function getSymbol()
    {
        return $this->symbol;
    }

    /**
     * Défini si la grandeur physique est une base ou une dérivée.
     * @param bool $isBase
     */
    public function setIsBase($isBase)
    {
        $this->isBase = $isBase;
    }

    /**
     * Informe si la grandeur physique est une base ou non.
     * @return boolean
     */
    public function isBase()
    {
        return $this->isBase;
    }

    /**
     * Change l'unité de référence de la Grandeur Physique.
     * @param StandardUnit $unit La nouvelle unité de la GrandeurPhysiqueBase.
     */
    public function setReferenceUnit(StandardUnit $unit = null)
    {
        $this->referenceUnit = $unit;
    }

    /**
     * Retourne l'unite de réference associée à la Grandeur Physique.
     * @return StandardUnit
     */
    public function getReferenceUnit()
    {
        return $this->referenceUnit;
    }

    /**
     * Ajoute une ligne au tableau  $_composedPhysicalQuantities
     * @param \Unit\Domain\PhysicalQuantity $basePhysicalQuantity
     * @param int                                      $exponent
     */
    public function addPhysicalQuantityComponent(PhysicalQuantity $basePhysicalQuantity, $exponent)
    {
        if ($this->getKey() === array()) {
            throw new \Core_Exception_NotFound('PhysicalQuantity must be flushed before a Component can be added');
        }
        if ($basePhysicalQuantity->isBase() === false) {
            throw new \Core_Exception_InvalidArgument('Only Base PhysicalQuantity can be added as Component');
        }
        $physicalQuantityComponent = new Component();
        $physicalQuantityComponent->setDerivedPhysicalQuantity($this);
        $physicalQuantityComponent->setBasePhysicalQuantity($basePhysicalQuantity);
        $physicalQuantityComponent->setExponent($exponent);
        self::getEntityManager()->persist($physicalQuantityComponent);
        $this->physicalQuantityComponents->add($physicalQuantityComponent);
    }


    /**
     * Récupère la composition en grandeurs physiques de base d'une grandeur physique
     * @return \Unit\Domain\PhysicalQuantity[]
     */
    public function getPhysicalQuantityComponents()
    {
        return $this->physicalQuantityComponents->toArray();
    }

}