<?php

namespace Classification\Domain;

use Core_Exception_UndefinedAttribute;
use Core_Model_Entity;
use Core_Model_Entity_Translatable;
use Core_Strategy_Ordered;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;

/**
 * Member d'un Axis de Classification.
 *
 * @author valentin.claras
 */
class Member extends Core_Model_Entity
{
    use Core_Strategy_Ordered;
    use Core_Model_Entity_Translatable;

    // Constantes de tris et de filtres.
    const QUERY_REF = 'ref';
    const QUERY_LABEL = 'label';
    const QUERY_AXIS = 'axis';
    const QUERY_POSITION = 'position';

    /**
     * @var int $id
     */
    protected $id;

    /**
     * @var string
     */
    protected $ref;

    /**
     * @var string
     */
    protected $label;

    /**
     * @var Axis
     */
    protected $axis;

    /**
     * @var Collection|Member[]
     */
    protected $directChildren;

    /**
     * @var Collection|Member[]
     */
    protected $directParents;


    public function __construct()
    {
        $this->directChildren = new ArrayCollection();
        $this->directParents = new ArrayCollection();
    }

    /**
     * @return int
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
     * @return string
     */
    public function getRef()
    {
        return $this->ref;
    }

    /**
     * @param string $label
     */
    public function setLabel($label)
    {
        $this->label = $label;
    }

    /**
     * @return string
     */
    public function getLabel()
    {
        return $this->label;
    }

    /**
     * @param Axis|null $axis
     */
    public function setAxis(Axis $axis = null)
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
     * @throws Core_Exception_UndefinedAttribute
     * @return Axis
     */
    public function getAxis()
    {
        if ($this->axis === null) {
            throw new Core_Exception_UndefinedAttribute('The axis has not been defined yet');
        }
        return $this->axis;
    }

    /**
     * @param Member $parentMember
     */
    public function addDirectParent(Member $parentMember)
    {
        if (!($this->hasDirectParent($parentMember))) {
            $this->directParents->add($parentMember);
            $parentMember->addDirectChild($this);
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
     * @param Member $parentMember
     */
    public function removeDirectParent($parentMember)
    {
        if ($this->hasDirectParent($parentMember)) {
            $this->directParents->removeElement($parentMember);
            $parentMember->removeDirectChild($this);
        }
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
        $parents = $this->directParents->toArray();
        foreach ($this->directParents as $directParent) {
            $parents = array_merge($parents, $directParent->getAllParents());
        }
        return $parents;
    }

    /**
     * @param Member $childMember
     */
    public function addDirectChild(Member $childMember)
    {
        if (!($this->hasDirectChild($childMember))) {
            $this->directChildren->add($childMember);
            $childMember->addDirectParent($this);
        }
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
     * @param Member $childMember
     */
    public function removeDirectChild($childMember)
    {
        if ($this->hasDirectChild($childMember)) {
            $this->directChildren->removeElement($childMember);
            $childMember->removeDirectParent($this);
        }
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
        $children = $this->directChildren->toArray();
        foreach ($this->directChildren as $directChild) {
            $children = array_merge($children, $directChild->getAllChildren());
        }
        return $children;
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
     * @return array
     */
    protected function getContext()
    {
        return ['axis' => $this->getAxis()];
    }
}
