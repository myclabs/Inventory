<?php
/**
 * Classe Classif_Model_Member
 * @author     valentin.claras
 * @author     simon.rieu
 * @package    Classif
 * @subpackage Model
 */

/**
 * Un membre d'un axe relié à d'autres membres.
 *
 * @package    Classif
 * @subpackage Model
 */
class Classif_Model_Member extends Core_Model_Entity
{

    use Core_Strategy_Ordered;
    use Core_Model_Entity_Translatable;

    // Constantes de tris et de filtres.
    const QUERY_REF = 'ref';
    const QUERY_LABEL = 'label';
    const QUERY_AXIS = 'axis';
    const QUERY_POSITION = 'position';


    /**
     * Identifiant unique du Member.
     *
     * @var int $id
     */
    protected $id;

    /**
     * Ref unique du Member.
     *
     * @var string
     */
    protected $ref;

    /**
     * Label du Member.
     *
     * @var string
     */
    protected $label;

    /**
     * Axis auquel appartien le Member.
     *
     * @var Classif_Model_Axis
     */
    protected $axis;

    /**
     * Child directs du Member.
     *
     * @var Doctrine\Common\Collections\Collection
     */
    protected $_directChildren;

    /**
     * Parents directs du Member.
     *
     * @var Doctrine\Common\Collections\Collection
     */
    protected $_directParents;


    /**
     * Constructeur de la classe Member.
     */
    public function __construct()
    {
        $this->_directChildren = new Doctrine\Common\Collections\ArrayCollection();
        $this->_directParents = new Doctrine\Common\Collections\ArrayCollection();
    }

    /**
     * Renvoi les valeurs du contexte pour l'objet.
     * .
     * @return array
     */
    protected function getContext()
    {
        return array('axis' => $this->getAxis());
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
     * Charge un Member par son ref et son Axis.
     *
     * @param string             $ref
     * @param Classif_Model_Axis $axis
     *
     * @return Classif_Model_Member
     */
    public static function loadByRefAndAxis($ref, Classif_Model_Axis $axis)
    {
        return self::getEntityRepository()->loadBy(array('ref' => $ref, 'axis' => $axis));
    }

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Modifie la référence du Member.
     *
     * @param string $ref
     */
    public function setRef($ref)
    {
        $this->ref = $ref;
    }

    /**
     * retourne la ref du membre
     */
    public function getRef()
    {
        return $this->ref;
    }

    /**
     * Modifie le label du Member.
     *
     * @param string $label
     */
    public function setLabel($label)
    {
        $this->label = $label;
    }

    /**
     * Retourne le label du Member.
     *
     * @return string
     */
    public function getLabel()
    {
        return $this->label;
    }

    /**
     * Modifie l'Axis du Member.
     *
     * @param Classif_Model_Axis|null $axis
     */
    public function setAxis(Classif_Model_Axis $axis = null)
    {
        if ($this->axis !== $axis) {
            if ($this->axis !== null) {
                $this->axis->removeMember($this);
            }
            $this->deletePosition();
            $this->axis = $axis;
            $this->setPosition();
            if ($axis !== null) {
                $axis->addMember($this);
            }
        }
    }

    /**
     * Retourne l'Axis du Member.
     *
     * @return Classif_Model_Axis
     */
    public function getAxis()
    {
        if ($this->axis === null) {
            throw Core_Exception_UndefinedAttribute('The Axis has not been defined yet.');
        }
        return $this->axis;
    }

    /**
     * Ajoute un Member donné aux parents directs du Member.
     *
     * @param Classif_Model_Member $parentMember
     */
    public function addDirectParent(Classif_Model_Member $parentMember)
    {
        if (!($this->hasDirectParent($parentMember))) {
            $this->_directParents->add($parentMember);
            $parentMember->addDirectChild($this);
        }
    }

    /**
     * Vérifie si le Member donné est bien parent direct du Member.
     *
     * @param Classif_Model_Member $parentMember
     *
     * @return boolean
     */
    public function hasDirectParent(Classif_Model_Member $parentMember)
    {
        return $this->_directParents->contains($parentMember);
    }

    /**
     * Supprime le Member donné des parents directs du Member.
     *
     * @param Classif_Model_Member $parentMember
     */
    public function removeDirectParent($parentMember)
    {
        if ($this->hasDirectParent($parentMember)) {
            $this->_directParents->removeElement($parentMember);
            $parentMember->removeDirectChild($this);
        }
    }

    /**
     * Indique si le Member possède des parents directs.
     *
     * @return bool
     */
    public function hasDirectParents()
    {
        return !$this->_directParents->isEmpty();
    }

    /**
     * Retourne l'ensemble des parents directs du Member.
     *
     * @return Classif_Model_Member[]
     */
    public function getDirectParents()
    {
        return $this->_directParents->toArray();
    }

    /**
     * Retourne récursivement, tous les parents du Member.
     *
     * @return Classif_Model_Member[]
     */
    public function getAllParents()
    {
        $parents = $this->_directParents->toArray();
        foreach ($this->_directParents as $directParent) {
            $parents = array_merge($parents, $directParent->getAllParents());
        }
        return $parents;
    }

    /**
     * Ajoute un Member donné aux children directs du Member.
     *
     * @param Classif_Model_Member $childMember
     */
    public function addDirectChild(Classif_Model_Member $childMember)
    {
        if (!($this->hasDirectChild($childMember))) {
            $this->_directChildren->add($childMember);
            $childMember->addDirectParent($this);
        }
    }

    /**
     * Vérifie si le Member donné est bien child direct du Member.
     *
     * @param Classif_Model_Member $childMember
     *
     * @return boolean
     */
    public function hasDirectChild(Classif_Model_Member $childMember)
    {
        return $this->_directChildren->contains($childMember);
    }

    /**
     * Supprime le Member donné des children directs du Member.
     *
     * @param Classif_Model_Member $childMember
     */
    public function removeDirectChild($childMember)
    {
        if ($this->hasDirectChild($childMember)) {
            $this->_directChildren->removeElement($childMember);
            $childMember->removeDirectParent($this);
        }
    }

    /**
     * Indique si le Member possède des children directs.
     *
     * @return bool
     */
    public function hasDirectChildren()
    {
        return !$this->_directChildren->isEmpty();
    }

    /**
     * Retourne l'ensemble des children directs du Member.
     *
     * @return Classif_Model_Member[]
     */
    public function getDirectChildren()
    {
        return $this->_directChildren->toArray();
    }

    /**
     * Retourne récursivement, tous les children du Member.
     *
     * @return Classif_Model_Member[]
     */
    public function getAllChildren()
    {
        $children = $this->_directChildren->toArray();
        foreach ($this->_directChildren as $directChild) {
            $children = array_merge($children, $directChild->getAllChildren());
        }
        return $children;
    }

}
