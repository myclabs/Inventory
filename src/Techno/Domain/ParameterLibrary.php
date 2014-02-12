<?php

namespace Techno\Domain;

use Core_Model_Entity;
use Core_Model_Entity_Translatable;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;

/**
 * Bibliothèque de paramètres.
 *
 * @author matthieu.napoli
 */
class ParameterLibrary extends Core_Model_Entity
{
    use Core_Model_Entity_Translatable;

    /**
     * @var int
     */
    protected $id;

    /**
     * @var string
     */
    protected $label;

    /**
     * Les catégories sont sous forme d'arbre, ceci contient uniquement les catégories racines.
     *
     * @var Category[]|Collection
     */
    protected $rootCategories;

    /**
     * @param string $label
     */
    public function __construct($label)
    {
        $this->label = $label;
        $this->rootCategories = new ArrayCollection();
    }

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @return string
     */
    public function getLabel()
    {
        return $this->label;
    }

    /**
     * @return Category[]
     */
    public function getRootCategories()
    {
        return $this->rootCategories->toArray();
    }

    public function addRootCategory(Category $category)
    {
        $this->rootCategories->add($category);
    }

    public function removeRootCategory(Category $category)
    {
        $this->rootCategories->removeElement($category);
    }
}
