<?php
/**
 * Classe DW_Model_Member
 * @author valentin.claras
 * @author cyril.perraud
 * @package    DW
 * @subpackage Model
 */

use Doctrine\Common\Collections\Collection;
use Doctrine\Common\Collections\ArrayCollection;

/**
 * Definit un membre d'un axe.
 * @package    DW
 * @subpackage Model
 */
class DW_Model_Member extends Core_Model_Entity
{
    use Core_Strategy_Ordered;
    use Core_Model_Entity_Translatable;

    // Constantes de tris et de filtres.
    const QUERY_REF = 'ref';
    const QUERY_LABEL = 'label';
    const QUERY_POSITION = 'position';
    const QUERY_AXIS = 'axis';


    /**
     * Identifiant unique du Member.
     *
     * @var string
     */
    protected  $id = null;

    /**
     * Référence unique (au sein d'un Axis) du Member.
     *
     * @var string
     */
    protected $ref = null;

    /**
     * Label du Member.
     *
     * @var string
     */
    protected $label = null;

    /**
     * Axis auqel appartient le Member.
     *
     * @var DW_Model_Axis
     */
    protected $axis;

    /**
     * Collection des Member parents du Member courant.
     *
     * @var Collection|DW_Model_Member[]
     */
    protected $directParents = null;

    /**
     * Collection des Member enfants du Member courant.
     *
     * @var Collection|DW_Model_Member[]
     */
    protected $directChildren = null;

    /**
     * Collection des Result utilisant ce Member.
     *
     * @var Collection|DW_Model_Result[]
     */
    private $results = null;


    /**
     * Constructeur de la classe Member.
     *
     * @param DW_Model_Axis $axis
     */
    public function __construct(DW_Model_Axis $axis)
    {
        $this->directParents = new ArrayCollection();
        $this->directChildren = new ArrayCollection();
        $this->results = new ArrayCollection();

        $this->axis = $axis;
        $this->setPosition();
        $axis->addMember($this);
    }

    /**
     * Renvoi les valeurs du contexte pour l'objet.
     * .
     * @return array
     */
    protected function getContext()
    {
        return array('axis' => $this->axis);
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
     * Charge l'objet en fonction de sa ref et son Axis.
     *
     * @param string $ref
     * @param DW_Model_Axis $axis
     *
     * @return DW_Model_Member
     */
    public static function loadByRefAndAxis($ref, DW_Model_Axis $axis)
    {
        return $axis->getMemberByRef($ref);
    }

    /**
     * Renvoie l'id du Member.
     *
     * @return string
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Définit la référence du Member.
     *
     * @param string $ref
     */
    public function setRef($ref)
    {
        $this->ref = $ref;
    }

    /**
     * Renvois la référence du Member.
     *
     * @return String
     */
    public function getRef()
    {
        return $this->ref;
    }

    /**
     * Définit le label du Member.
     *
     * @param String $label
     */
    public function setLabel($label)
    {
        $this->label = $label;
    }

    /**
     * Renvois le label du Member.
     *
     * @return String
     */
    public function getLabel()
    {
        return $this->label;
    }

    /**
     * Renvoie l'Axis du Member.
     *
     * @return DW_Model_Axis
     */
    public function getAxis()
    {
        return $this->axis;
    }

    /**
     * Ajoute un Member donné aux parents directs du Member courant.
     *
     * @param DW_Model_Member $parentMember
     */
    public function addDirectParent(DW_Model_Member $parentMember)
    {
        if (!($this->hasDirectParent($parentMember))) {
            $this->directParents->add($parentMember);
            $parentMember->addDirectChild($this);
        }
    }

    /**
     * Vérifie si le Member courant possède le Member donné en tant que parent direct.
     *
     * @param DW_Model_Member $parentMember
     *
     * @return boolean
     */
    public function hasDirectParent(DW_Model_Member $parentMember)
    {
        return $this->directParents->contains($parentMember);
    }

    /**
     * Retire un Member donné des parents directs du Member courant.
     *
     * @param DW_Model_Member $parentMember
     */
    public function removeDirectParent($parentMember)
    {
        if ($this->hasDirectParent($parentMember)) {
            $this->directParents->removeElement($parentMember);
            $parentMember->removeDirectChild($this);
        }
    }

    /**
     * Vérifie que le Member courant possède au moins un Member parent direct.
     *
     * @return bool
     */
    public function hasDirectParents()
    {
        return !$this->directParents->isEmpty();
    }

    /**
     * Renvoie un tableau des Member parents directs.
     *
     * @return DW_Model_Member[]
     */
    public function getDirectParents()
    {
        return $this->directParents->toArray();
    }

    /**
     * Renvoie un tableau contenant tous les Member parents de Member courant.
     *
     * @return DW_Model_Member[]
     */
    public function getAllParents()
    {
        $parents = array();
        foreach ($this->directParents as $directParent) {
            $parents[] = $directParent;
            foreach ($directParent->getAllParents() as $recursiveParents) {
                $parents[] = $recursiveParents;
            }
        }
        return $parents;
    }

    /**
     * Renvoie le Member parent pour l'Axis donné.
     *
     * @param DW_Model_Axis $axis
     *
     * @throws Core_Exception_InvalidArgument
     *
     * @return DW_Model_Member
     */
    public function getParentForAxis($axis)
    {
        foreach ($this->directParents as $directParent) {
            if ($directParent->getAxis() === $axis) {
                return $directParent;
            } else if ($axis->isBroaderThan($directParent->getAxis())) {
                return $directParent->getParentForAxis($axis);
            }
        }
        throw new Core_Exception_InvalidArgument('There is no paremt member for the given axis.');
    }

    /**
     * Ajoute un Member donné aux enfants directs du Member courant.
     *
     * @param DW_Model_Member $childMember
     */
    public function addDirectChild(DW_Model_Member $childMember)
    {
        if (!($this->hasDirectChild($childMember))) {
            $this->directChildren->add($childMember);
            $childMember->addDirectParent($this);
        }
    }

    /**
     * Vérifie si le Member courant possède le Member donné en tant qu'enfant direct.
     *
     * @param DW_Model_Member $childMember
     *
     * @return boolean
     */
    public function hasDirectChild(DW_Model_Member $childMember)
    {
        return $this->directChildren->contains($childMember);
    }

    /**
     * Retire un Member donné des enfants directs du Member courant.
     *
     * @param DW_Model_Member $childMember
     */
    public function removeDirectChild($childMember)
    {
        if ($this->hasDirectChild($childMember)) {
            $this->directChildren->removeElement($childMember);
            $childMember->removeDirectParent($this);
        }
    }

    /**
     * Vérifie que le Member courant possède au moins un Member enfant direct.
     *
     * @return bool
     */
    public function hasDirectChildren()
    {
        return !$this->directChildren->isEmpty();
    }

    /**
     * Renvoie un tableau des Member enfants directs.
     *
     * @return DW_Model_Member[]
     */
    public function getDirectChildren()
    {
        return $this->directChildren->toArray();
    }

    /**
     * Renvoie un tableau contenant tous les Member enfants de Member courant.
     *
     * @return DW_Model_Member[]
     */
    public function getAllChildren()
    {
        $children = array();
        foreach ($this->directChildren as $directChild) {
            $children[] = $directChild;
            foreach ($directChild->getAllChildren() as $recursiveChildren) {
                $children[] = $recursiveChildren;
            }
        }
        return $children;
    }

    /**
     * Renvoie les Member enfants pour l'Axis donné.
     *
     * @param DW_Model_Axis $axis
     *
     * @return DW_Model_Member[]
     */
    public function getChildrenForAxis($axis)
    {
        if ($this->getAxis()->getDirectNarrower() === $axis) {
            return $this->getDirectChildren();
        } else {
            $children = array();
            foreach ($this->directChildren as $directChild) {
                $children = array_merge($children, $directChild->getChildrenForAxis($axis));
            }
            return array_unique($children, SORT_REGULAR);
        }
    }

}
