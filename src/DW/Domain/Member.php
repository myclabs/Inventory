<?php
/**
 * @author valentin.claras
 */

namespace DW\Domain;

use Core\Translation\TranslatedString;
use Core_Exception_InvalidArgument;
use Core_Exception_NotFound;
use Core_Model_Entity;
use Core_Strategy_Ordered;
use Doctrine\Common\Collections\Collection;
use Doctrine\Common\Collections\ArrayCollection;

/**
 * @package    DW
 * @subpackage Domain
 */
class Member extends Core_Model_Entity
{
    use Core_Strategy_Ordered;

    // Constantes de tris et de filtres.
    const QUERY_REF = 'ref';
    const QUERY_LABEL = 'label';
    const QUERY_POSITION = 'position';
    const QUERY_AXIS = 'axis';


    /**
     * @var string
     */
    protected $id = null;

    /**
     * @var string
     */
    protected $ref = null;

    /**
     * @var TranslatedString
     */
    protected $label = null;

    /**
     * @var Axis
     */
    protected $axis;

    /**
     * @var Collection|Member[]
     */
    protected $directParents = null;

    /**
     * @var Collection|Member[]
     */
    protected $directChildren = null;

    /**
     * @var Collection|Result[]
     */
    private $results = null;


    public function __construct(Axis $axis)
    {
        $this->label = new TranslatedString();
        $this->directParents = new ArrayCollection();
        $this->directChildren = new ArrayCollection();
        $this->results = new ArrayCollection();

        $this->axis = $axis;
        $this->setPosition();
        $axis->addMember($this);
    }

    /**
     * @return array
     */
    protected function getContext()
    {
        return ['axis' => $this->axis];
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
     * @return string
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param string $ref
     */
    public function setRef($ref)
    {
        $this->ref = $ref;
    }

    /**
     * @return String
     */
    public function getRef()
    {
        return $this->ref;
    }

    /**
     * @param TranslatedString $label
     */
    public function setLabel(TranslatedString $label)
    {
        $this->label = $label;
    }

    /**
     * @return TranslatedString
     */
    public function getLabel()
    {
        return $this->label;
    }

    /**
     * @return Axis
     */
    public function getAxis()
    {
        return $this->axis;
    }

    /**
     * @param Member $newDirectParentMemberForAxis
     * @throws Core_Exception_InvalidArgument
     */
    public function setDirectParentForAxis(Member $newDirectParentMemberForAxis)
    {
        if (!($this->hasDirectParent($newDirectParentMemberForAxis))) {
            if ($newDirectParentMemberForAxis->getAxis()->getDirectNarrower() !== $this->getAxis()) {
                throw new Core_Exception_InvalidArgument('A direct parent Member needs to comes from a broader axis');
            }
            try {
                $oldDirectParentMemberForAxis = $this->getParentForAxis($newDirectParentMemberForAxis->getAxis());
                $this->directParents->removeElement($oldDirectParentMemberForAxis);
                if ($oldDirectParentMemberForAxis->hasDirectChild($this)) {
                    $oldDirectParentMemberForAxis->directChildren->removeElement($this);
                }
            } catch (Core_Exception_NotFound $e) {
                // Pas d'ancien membre parent pour cet axe.
            }
            $this->directParents->add($newDirectParentMemberForAxis);
            if (!($newDirectParentMemberForAxis->hasDirectChild($this))) {
                $newDirectParentMemberForAxis->directChildren->add($this);
            }
        }
    }

    /**
     * @param Member $directParentMemberForAxis
     */
    public function removeDirectParentForAxis(Member $directParentMemberForAxis)
    {
        if ($this->hasDirectParent($directParentMemberForAxis)) {
            $this->directParents->removeElement($directParentMemberForAxis);
            if ($directParentMemberForAxis->hasDirectChild($this)) {
                $directParentMemberForAxis->directChildren->removeElement($this);
            }
        }
    }

    /**
     * @param Member $parentMember
     * @return boolean
     */
    public function hasDirectParent(Member $parentMember)
    {
        return $this->directParents->contains($parentMember);
    }

    /**
     * @return bool
     */
    public function hasDirectParents()
    {
        return !$this->directParents->isEmpty();
    }

    /**
     * @return Member[]
     */
    public function getDirectParents()
    {
        return $this->directParents->toArray();
    }

    /**
     * @return Member[]
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
     * @param Axis $axis
     * @throws Core_Exception_NotFound
     * @return Member
     */
    public function getParentForAxis(Axis $axis)
    {
        foreach ($this->directParents as $directParent) {
            if ($directParent->getAxis() === $axis) {
                return $directParent;
            } elseif ($axis->isBroaderThan($directParent->getAxis())) {
                return $directParent->getParentForAxis($axis);
            }
        }
        throw new Core_Exception_NotFound('There is no parent member for the given axis.');
    }

    /**
     * @param Member $childMember
     * @return boolean
     */
    public function hasDirectChild(Member $childMember)
    {
        return $this->directChildren->contains($childMember);
    }

    /**
     * @return bool
     */
    public function hasDirectChildren()
    {
        return !$this->directChildren->isEmpty();
    }

    /**
     * @return Member[]
     */
    public function getDirectChildren()
    {
        return $this->directChildren->toArray();
    }

    /**
     * @return Member[]
     */
    public function getAllChildren()
    {
        $children = [];
        foreach ($this->directChildren as $directChild) {
            $children[] = $directChild;
            foreach ($directChild->getAllChildren() as $recursiveChildren) {
                $children[] = $recursiveChildren;
            }
        }
        return $children;
    }

    /**
     * @param Axis $axis
     * @return Member[]
     */
    public function getChildrenForAxis(Axis $axis)
    {
        if ($this->getAxis()->getDirectNarrower() === $axis) {
            return $this->getDirectChildren();
        } else {
            $children = [];
            foreach ($this->directChildren as $directChild) {
                $children = array_merge($children, $directChild->getChildrenForAxis($axis));
            }
            return array_unique($children, SORT_REGULAR);
        }
    }
}
