<?php
/**
 * @author  matthieu.napoli
 * @package AF
 */

use Doctrine\Common\Collections\Collection;
use Doctrine\Common\Collections\ArrayCollection;

/**
 * Classe Category
 * @package AF
 */
class AF_Model_Category extends Core_Model_Entity
{

    use Core_Strategy_Ordered;
    use Core_Model_Entity_Translatable;

    const QUERY_POSITION = 'position';
    const QUERY_PARENT_CATEGORY = 'parentCategory';

    /**
     * @var int
     */
    protected $id;

    /**
     * @var string
     */
    protected $label;

    /**
     * @var AF_Model_Category|null
     */
    protected $parentCategory;

    /**
     * @var AF_Model_Category[]|Collection
     */
    protected $childCategories;

    /**
     * @var AF_Model_AF[]|Collection
     */
    protected $afs;


    /**
     * Constructeur
     */
    public function __construct()
    {
        $this->childCategories = new ArrayCollection();
        $this->afs = new ArrayCollection();
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
     * @return AF_Model_Category|null Si null, la catégorie est à la racine
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
     * @param AF_Model_Category|null $parentCategory
     */
    public function setParentCategory(AF_Model_Category $parentCategory = null)
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
     * @return AF_Model_Category[]
     */
    public function getChildCategories()
    {
        return $this->childCategories;
    }

    /**
     * Ajoute une sous-catégorie
     * @param AF_Model_Category $category
     */
    public function addChildCategory(AF_Model_Category $category) {
        if (! $this->childCategories->contains($category)) {
            $this->childCategories->add($category);
            $category->setParentCategory($this);
        }
    }

    /**
     * Retire une sous-catégorie
     * @param AF_Model_Category $category
     */
    public function removeChildCategory(AF_Model_Category $category) {
        if ($this->childCategories->contains($category)) {
            $this->childCategories->removeElement($category);
        }
    }

    /**
     * Renvoie les AF de la catégorie
     * @return AF_Model_AF[]
     */
    public function getAFs()
    {
        return $this->afs;
    }

    /**
     * Ajoute un AF
     * @param AF_Model_AF $af
     */
    public function addAF(AF_Model_AF $af) {
        if (! $this->afs->contains($af)) {
            $this->afs->add($af);
            $af->setCategory($this);
        }
    }

    /**
     * Retire un AF de la catégorie
     * @param AF_Model_AF $af
     */
    public function removeAF(AF_Model_AF $af) {
        if ($this->afs->contains($af)) {
            $this->afs->removeElement($af);
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
