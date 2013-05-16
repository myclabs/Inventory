<?php
/**
 * @author ronan.gorain
 * @author matthieu.napoli
 * @package Techno
 */

use \Doctrine\Common\Collections\Collection;
use \Doctrine\Common\Collections\ArrayCollection;

/**
 * Classe Component
 * @package Techno
 */
abstract class Techno_Model_Component extends Core_Model_Entity
{

    /**
     * @var int
     */
    protected $id;

    /**
     * Référence de l'unité de base
     * @var string
     */
    protected $refBaseUnit;

    /**
     * Cache
     * @var Unit_API
     */
    protected $baseUnit;

    /**
     * Référence de l'unité du composant
     * @var string
     */
    protected $refUnit;

    /**
     * Cache
     * @var Unit_API
     */
    protected $unit;

    /**
     * Liste des tags du component
     * @var Collection
     */
    protected $tags;

    /**
     * Documentation
     * @var string
     */
    protected $documentation;

    /**
     * Constructeur
     */
    public function __construct()
    {
        $this->tags = new ArrayCollection();
    }

    /**
     * @param Unit_API $baseUnit
     */
    public function setBaseUnit(Unit_API $baseUnit)
    {
        $this->refBaseUnit = $baseUnit->getRef();
        $this->baseUnit = $baseUnit;
    }

    /**
     * @return Unit_API
     * @throws Core_Exception_UndefinedAttribute
     */
    public function getBaseUnit()
    {
        if ($this->refBaseUnit === null) {
            throw new Core_Exception_UndefinedAttribute("The component base unit has not been defined");
        }
        // Lazy loading
        if ($this->baseUnit === null) {
            $this->baseUnit = new Unit_API($this->refBaseUnit);
        }
        return $this->baseUnit;
    }

    /**
     * @param Unit_API $unit
     * @throws Core_Exception_UndefinedAttribute
     * @throws Core_Exception_InvalidArgument
     */
    public function setUnit(Unit_API $unit)
    {
        if ($this->refBaseUnit === null) {
            throw new Core_Exception_UndefinedAttribute("A base unit needs to be set for this component");
        }
        // Vérifie que l'unité est compatible avec l'unité de base
        if (! $unit->isEquivalent($this->refBaseUnit)) {
            throw new Core_Exception_InvalidArgument("The unit given is not compatible"
                . " with the base unit of the component");
        }
        $this->refUnit = $unit->getRef();
        $this->unit = $unit;
    }

    /**
     * @return Unit_API
     * @throws Core_Exception_UndefinedAttribute
     */
    public function getUnit()
    {
        if ($this->refUnit === null) {
            throw new Core_Exception_UndefinedAttribute("The component unit has not been defined");
        }
        // Lazy loading
        if ($this->unit === null) {
            $this->unit = new Unit_API($this->refUnit);
        }
        return $this->unit;
    }

    /**
     * Retourne l'unité de la valeur de l'élément (!= unité de l'élément)
     * @return Unit_API
     * @throws Core_Exception_UndefinedAttribute
     */
    public function getValueUnit()
    {
        return $this->getUnit();
    }

    /**
     * @param string $documentation
     */
    public function setDocumentation($documentation)
    {
        $this->documentation = $documentation;
    }

    /**
     * @return string
     */
    public function getDocumentation()
    {
        return $this->documentation;
    }

    /**
     * Retourne les tags du composant
     * @return Collection|Techno_Model_Tag[]
     */
    public function getTags()
    {
        return $this->tags;
    }

    /**
     * Ajoute un tag
     * @param Techno_Model_Tag $tag
     */
    public function addTag(Techno_Model_Tag $tag)
    {
        $this->tags->add($tag);
    }

    /**
     * Retourne true si le composant a ce tag dans sa liste de tags
     * @param Techno_Model_Tag $tag
     * @return boolean
     */
    public function hasTag(Techno_Model_Tag $tag)
    {
        return $this->tags->contains($tag);
    }

    /**
     * Supprime l'élément de la liste des tags du composant
     * @param Techno_Model_Tag $tag
     */
    public function removeTag(Techno_Model_Tag $tag)
    {
        $this->tags->removeElement($tag);
    }

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

}
