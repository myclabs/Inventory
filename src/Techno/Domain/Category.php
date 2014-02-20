<?php

namespace Techno\Domain;

use Core_Exception_UndefinedAttribute;
use Core_Model_Entity;
use Core_Model_Entity_Translatable;
use Core_Model_Filter;
use Core_Model_Query;
use Core_Strategy_Ordered;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Techno\Domain\Family\Family;

/**
 * Catégories de familles.
 *
 * @author ronan.gorain
 * @author matthieu.napoli
 */
class Category extends Core_Model_Entity
{
    use Core_Strategy_Ordered;
    use Core_Model_Entity_Translatable;

    const QUERY_POSITION = 'position';
    const QUERY_PARENT_CATEGORY = 'parentCategory';

    /**
     * id de la catégorie
     * @var int
     */
    protected $id;

    /**
     * label de la catégorie
     * @var string
     */
    protected $label;

    /**
     * @var Category|null
     */
    protected $parentCategory;

    /**
     * @var Category[]|Collection
     */
    protected $childCategories;

    /**
     * @var Family[]|Collection
     */
    protected $families;


    /**
     * @param string|null $label
     */
    public function __construct($label = null)
    {
        $this->childCategories = new ArrayCollection();
        $this->families = new ArrayCollection();
        $this->label = $label;
    }

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Retourne le nom de la catégorie
     * @return string
     */
    public function getLabel()
    {
        return $this->label;
    }

    /**
     * Modifie le nom de la catégorie
     * @param string $newLabel
     */
    public function setLabel($newLabel)
    {
        $this->label = $newLabel;
    }

    /**
     * @return Category|null Si null, la catégorie est à la racine
     */
    public function getParentCategory()
    {
        return $this->parentCategory;
    }

    /**
     * Retourne true si la catégorie est une catégorie racine
     * @return bool
     */
    public function isRootCategory()
    {
        return $this->parentCategory === null;
    }

    /**
     * Change la catégorie parente
     * @param Category|null $parentCategory
     */
    public function setParentCategory(Category $parentCategory = null)
    {
        if ($this->parentCategory !== $parentCategory) {
            $this->deletePosition();
            if ($this->parentCategory) {
                $this->parentCategory->removeChildCategory($this);
            }

            $this->parentCategory = $parentCategory;

            if ($parentCategory) {
                $parentCategory->addChildCategory($this);
            }
            $this->setPosition();
        }
    }

    /**
     * Renvoie les sous-catégories
     * @return Category[]
     */
    public function getChildCategories()
    {
        return $this->childCategories;
    }

    /**
     * Ajoute une sous-catégorie
     * @param Category $category
     */
    public function addChildCategory(Category $category)
    {
        if (!$this->childCategories->contains($category)) {
            $this->childCategories->add($category);
            $category->setParentCategory($this);
        }
    }

    /**
     * Retire une sous-catégorie
     * @param Category $category
     */
    public function removeChildCategory(Category $category)
    {
        if ($this->childCategories->contains($category)) {
            $this->childCategories->removeElement($category);
        }
    }

    /**
     * Renvoie les Family de la catégorie
     * @return Family[]
     */
    public function getFamilies()
    {
        return $this->families;
    }

    /**
     * Renvoi les Family des catégories enfantes et de la catégorie.
     * @return Family[]
     */
    public function getAllFamilies()
    {
        $families = [];

        foreach ($this->getChildCategories() as $childCategory) {
            $families = array_merge($families, $childCategory->getAllFamilies());
        }
        $families = array_merge($families, $this->families->toArray());

        return $families;
    }

    /**
     * Ajoute un AF
     * @param Family $family
     */
    public function addFamily(Family $family)
    {
        if (!$this->families->contains($family)) {
            $this->families->add($family);
            $family->setCategory($this);
        }
    }

    /**
     * Retire un AF de la catégorie
     * @param Family $family
     */
    public function removeFamily(Family $family)
    {
        if ($this->families->contains($family)) {
            $this->families->removeElement($family);
        }
    }

    /**
     * Charge la liste des catégories racines
     * @return Category[]
     */
    public static function loadRootCategories()
    {
        $query = new Core_Model_Query();
        $query->filter->addCondition(self::QUERY_PARENT_CATEGORY, null, Core_Model_Filter::OPERATOR_NULL);
        $query->order->addOrder(self::QUERY_POSITION);

        return self::loadList($query);
    }

    /**
     * Fonction appelée avant un persist de l'objet (défini dans le mapper).
     */
    public function preSave()
    {
        try {
            $this->checkHasPosition();
        } catch (Core_Exception_UndefinedAttribute $e) {
            $this->setPosition();
        }
    }

    /**
     * Fonction appelée avant un update de l'objet (défini dans le mapper).
     */
    public function preUpdate()
    {
        $this->checkHasPosition();
    }

    /**
     * Fonction appelée avant un delete de l'objet (défini dans le mapper).
     */
    public function preDelete()
    {
        $this->deletePosition();
    }

    /**
     * Fonction appelée après un load de l'objet (défini dans le mapper).
     */
    public function postLoad()
    {
        $this->updateCachePosition();
    }

    /**
     * Renvoie les valeurs du contexte pour la position
     * @return array
     */
    protected function getContext()
    {
        return [
            'parentCategory' => $this->parentCategory,
        ];
    }
}
