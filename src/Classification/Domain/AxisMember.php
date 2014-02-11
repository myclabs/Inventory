<?php

namespace Classification\Domain;

use Core_Exception_UndefinedAttribute;
use Core_Model_Entity;
use Core_Model_Entity_Translatable;
use Core_Strategy_Ordered;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;

/**
 * Un membre d'un axe relié à d'autres membres.
 *
 * @author valentin.claras
 * @author simon.rieu
 */
class AxisMember extends Core_Model_Entity
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
     * Ref unique du membre.
     *
     * @var string
     */
    protected $ref;

    /**
     * Libellé.
     *
     * @var string
     */
    protected $label;

    /**
     * Axe auquel appartien le membre.
     *
     * @var IndicatorAxis
     */
    protected $axis;

    /**
     * Enfants directs du membre.
     *
     * @var Collection|AxisMember[]
     */
    protected $directChildren;

    /**
     * Parents directs du membre.
     *
     * @var Collection|AxisMember[]
     */
    protected $directParents;

    public function __construct()
    {
        $this->directChildren = new ArrayCollection();
        $this->directParents = new ArrayCollection();
    }

    /**
     * Charge un Member par son ref et son Axis.
     *
     * @param string        $ref
     * @param IndicatorAxis $axis
     *
     * @return AxisMember
     */
    public static function loadByRefAndAxis($ref, IndicatorAxis $axis)
    {
        return self::getEntityRepository()->loadBy(['ref' => $ref, 'axis' => $axis]);
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
     * Retourne la ref du membre
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
     * Modifie l'axe du membre.
     *
     * @param IndicatorAxis|null $axis
     */
    public function setAxis(IndicatorAxis $axis = null)
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
     * Retourne l'axe du membre.
     *
     * @throws Core_Exception_UndefinedAttribute
     * @return IndicatorAxis
     */
    public function getAxis()
    {
        if ($this->axis === null) {
            throw new Core_Exception_UndefinedAttribute('The axis has not been defined yet');
        }
        return $this->axis;
    }

    /**
     * Ajoute un membre donné aux parents directs du membre.
     *
     * @param AxisMember $parentMember
     */
    public function addDirectParent(AxisMember $parentMember)
    {
        if (!($this->hasDirectParent($parentMember))) {
            $this->directParents->add($parentMember);
            $parentMember->addDirectChild($this);
        }
    }

    /**
     * Vérifie si le membre donné est bien parent direct du membre.
     *
     * @param AxisMember $parentMember
     *
     * @return boolean
     */
    public function hasDirectParent(AxisMember $parentMember)
    {
        return $this->directParents->contains($parentMember);
    }

    /**
     * Supprime le membre donné des parents directs du membre.
     *
     * @param AxisMember $parentMember
     */
    public function removeDirectParent($parentMember)
    {
        if ($this->hasDirectParent($parentMember)) {
            $this->directParents->removeElement($parentMember);
            $parentMember->removeDirectChild($this);
        }
    }

    /**
     * Indique si le membre possède des parents directs.
     *
     * @return bool
     */
    public function hasDirectParents()
    {
        return !$this->directParents->isEmpty();
    }

    /**
     * Retourne l'ensemble des parents directs du membre.
     *
     * @return AxisMember[]
     */
    public function getDirectParents()
    {
        return $this->directParents->toArray();
    }

    /**
     * Retourne récursivement, tous les parents du membre.
     *
     * @return AxisMember[]
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
     * Ajoute un membre donné aux enfants directs du membre.
     *
     * @param AxisMember $childMember
     */
    public function addDirectChild(AxisMember $childMember)
    {
        if (!($this->hasDirectChild($childMember))) {
            $this->directChildren->add($childMember);
            $childMember->addDirectParent($this);
        }
    }

    /**
     * Vérifie si le membre donné est bien enfant direct du membre.
     *
     * @param AxisMember $childMember
     *
     * @return boolean
     */
    public function hasDirectChild(AxisMember $childMember)
    {
        return $this->directChildren->contains($childMember);
    }

    /**
     * Supprime le membre donné des enfants directs du membre.
     *
     * @param AxisMember $childMember
     */
    public function removeDirectChild($childMember)
    {
        if ($this->hasDirectChild($childMember)) {
            $this->directChildren->removeElement($childMember);
            $childMember->removeDirectParent($this);
        }
    }

    /**
     * Indique si le membre possède des enfants directs.
     *
     * @return bool
     */
    public function hasDirectChildren()
    {
        return !$this->directChildren->isEmpty();
    }

    /**
     * Retourne l'ensemble des enfants directs du membre.
     *
     * @return AxisMember[]
     */
    public function getDirectChildren()
    {
        return $this->directChildren->toArray();
    }

    /**
     * Retourne récursivement tous les enfants du membre.
     *
     * @return AxisMember[]
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
