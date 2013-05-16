<?php
/**
 * @author ronan.gorain
 * @author matthieu.napoli
 * @package Techno
 */

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;

/**
 * Classe Category
 * @package Techno
 */
class Techno_Model_Category extends Core_Model_Entity
{

    use Core_Strategy_Ordered;

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
     * @var Techno_Model_Category|null
     */
    protected $parentCategory;

    /**
     * @var Techno_Model_Category[]|Collection
     */
    protected $childCategories;

    /**
     * @var Techno_Model_Family[]|Collection
     */
    protected $families;


    /**
     * Constructeur
     */
    public function __construct()
    {
        $this->childCategories = new ArrayCollection();
        $this->families = new ArrayCollection();
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
     * @return Techno_Model_Category|null Si null, la catégorie est à la racine
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
     * @param Techno_Model_Category|null $parentCategory
     */
    public function setParentCategory(Techno_Model_Category $parentCategory = null)
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
     * @return Techno_Model_Category[]
     */
    public function getChildCategories()
    {
        return $this->childCategories;
    }

    /**
     * Ajoute une sous-catégorie
     * @param Techno_Model_Category $category
     */
    public function addChildCategory(Techno_Model_Category $category) {
        if (! $this->childCategories->contains($category)) {
            $this->childCategories->add($category);
            $category->setParentCategory($this);
        }
    }

    /**
     * Retire une sous-catégorie
     * @param Techno_Model_Category $category
     */
    public function removeChildCategory(Techno_Model_Category $category) {
        if ($this->childCategories->contains($category)) {
            $this->childCategories->removeElement($category);
        }
    }

    /**
     * Renvoie les AF de la catégorie
     * @return Techno_Model_Family[]
     */
    public function getFamilies()
    {
        return $this->families;
    }

    /**
     * Ajoute un AF
     * @param Techno_Model_Family $family
     */
    public function addFamily(Techno_Model_Family $family) {
        if (! $this->families->contains($family)) {
            $this->families->add($family);
            $family->setCategory($this);
        }
    }

    /**
     * Retire un AF de la catégorie
     * @param Techno_Model_Family $family
     */
    public function removeFamily(Techno_Model_Family $family) {
        if ($this->families->contains($family)) {
            $this->families->removeElement($family);
        }
    }

    /**
     * Charge la liste des catégories racines
     * @return self[]
     */
    public static function loadRootCategories()
    {
        $query = new Core_Model_Query();
        $query->filter->addCondition(self::QUERY_PARENT_CATEGORY, null, Core_Model_Filter::OPERATOR_NULL);
        $query->order->addOrder(self::QUERY_POSITION);

        return self::loadList($query);
    }

    /**
     * Fonction appelé avant un persist de l'objet (défini dans le mapper).
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
     * Fonction appelé avant un update de l'objet (défini dans le mapper).
     */
    public function preUpdate()
    {
        $this->checkHasPosition();
    }

    /**
     * Fonction appelé avant un delete de l'objet (défini dans le mapper).
     */
    public function preDelete()
    {
        $this->deletePosition();
    }

    /**
     * Fonction appelé après un load de l'objet (défini dans le mapper).
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
