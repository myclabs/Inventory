<?php

namespace AF\Domain;

use Core_Model_Entity;
use Core_Model_Entity_Translatable;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;

/**
 * Bibliothèque d'AF.
 *
 * @author matthieu.napoli
 */
class AFLibrary extends Core_Model_Entity
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
     * @var Category[]|Collection
     */
    protected $categories;

    /**
     * @var AF[]|Collection
     */
    protected $afList;

    /**
     * @param string $label
     */
    public function __construct($label)
    {
        $this->label = $label;
        $this->categories = new ArrayCollection();
        $this->afList = new ArrayCollection();
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
     * @return AF[]
     */
    public function getAFList()
    {
        return $this->afList->toArray();
    }

    public function addAF(AF $af)
    {
        $this->afList->add($af);
    }

    public function removeAF(AF $af)
    {
        $this->afList->removeElement($af);
    }

    public function addCategory(Category $category)
    {
        $this->categories->add($category);
    }

    public function removeCategory(Category $category)
    {
        $this->categories->removeElement($category);
    }
}
