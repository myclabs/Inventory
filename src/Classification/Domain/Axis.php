<?php

namespace Classification\Domain;

use Core_Exception_UndefinedAttribute;
use Core_Model_Entity;
use Core_Model_Entity_Translatable;
use Core_Model_Filter;
use Core_Model_Query;
use Core_Strategy_Ordered;
use Doctrine\Common\Collections\Collection;
use Doctrine\Common\Collections\ArrayCollection;

/**
 * Un axe de classification.
 *
 * @author valentin.claras
 * @author simon.rieu
 */
class Axis extends Core_Model_Entity
{
    use Core_Strategy_Ordered;
    use Core_Model_Entity_Translatable;

    // Constantes de tris et de filtres.
    const QUERY_REF = 'ref';
    const QUERY_LABEL = 'label';
    const QUERY_POSITION = 'position';
    const QUERY_NARROWER = 'directNarrower';

    /**
     * @var int
     */
    protected $id;

    /**
     * @var ClassificationLibrary
     */
    protected $library;

    /**
     * Ref unique de l'Axis.
     *
     * @var int
     */
    protected $ref;

    /**
     * Libellé.
     *
     * @var string
     */
    protected $label;

    /**
     * Axe plus fins.
     *
     * @var Axis
     */
    protected $directNarrower;

    /**
     * Axes plus grossiers.
     *
     * @var Collection|Axis[]
     */
    protected $directBroaders;

    /**
     * Membres de l'axe.
     *
     * @var Collection|AxisMember[]
     */
    protected $members;


    public function __construct(ClassificationLibrary $library)
    {
        $this->directBroaders = new ArrayCollection();
        $this->members = new ArrayCollection();
        $this->library = $library;
    }

    /**
     * Permet de charger un axe par son ref.
     *
     * @param string $ref
     *
     * @return Axis $axis
     */
    public static function loadByRef($ref)
    {
        return self::getEntityRepository()->loadBy(array('ref' => $ref));
    }

    /**
     * Charge l'ensemble des axes dans l'ordre de parcours récursif dernière visite.
     *
     * @return Axis[]
     */
    public static function loadListOrderedAsAscendantTree()
    {
        $axes = [];

        $queryRoots = new Core_Model_Query();
        $queryRoots->filter->addCondition(self::QUERY_NARROWER, null, Core_Model_Filter::OPERATOR_NULL);
        foreach (Axis::loadList($queryRoots) as $rootAxis) {
            /** @var Axis $rootAxis */
            foreach ($rootAxis->getAllBroaders() as $recursiveBroader) {
                $axes[] = $recursiveBroader;
            }
            $axes[] = $rootAxis;
        }

        return $axes;
    }

    /**
     * Modifie la référence de l'axe.
     *
     * @param String $ref
     */
    public function setRef($ref)
    {
        $this->ref = $ref;
    }

    /**
     * Retourne la référence de l'axe.
     *
     * @return String
     */
    public function getRef()
    {
        return $this->ref;
    }

    /**
     * Modifie le label de l'axe.
     *
     * @param string $label
     */
    public function setLabel($label)
    {
        $this->label = $label;
    }

    /**
     * Retourne le label de l'axe.
     *
     * @return string
     */
    public function getLabel()
    {
        return $this->label;
    }

    /**
     * Modifie l'axe plus fin que cet axe.
     *
     * @param Axis|null $narrowerAxis
     */
    public function setDirectNarrower(Axis $narrowerAxis = null)
    {
        if ($this->directNarrower !== $narrowerAxis) {
            if ($this->directNarrower !== null) {
                foreach ($this->members as $member) {
                    foreach ($member->getDirectChildren() as $childMember) {
                        if ($childMember->getAxis() === $this->directNarrower) {
                            $member->removeDirectChild($childMember);
                        }
                    }
                }
                $this->directNarrower->removeDirectBroader($this);
            }
            $this->deletePosition();
            $this->directNarrower = $narrowerAxis;
            $this->setPosition();
            if ($narrowerAxis !== null) {
                $narrowerAxis->addDirectBroader($this);
            }
        }
    }

    /**
     * Retourne l'axe plus fin que cet axe si il en existe un.
     *
     * @return Axis|null
     */
    public function getDirectNarrower()
    {
        return $this->directNarrower;
    }

    /**
     * Ajoute un axe donné aux axes plus grossiers directs.
     *
     * @param Axis $broaderAxis
     */
    public function addDirectBroader(Axis $broaderAxis)
    {
        if (!($this->hasDirectBroader($broaderAxis))) {
            $this->directBroaders->add($broaderAxis);
            $broaderAxis->setDirectNarrower($this);
        }
    }

    /**
     * Vérifie si l'axe donné est bien un axe plus grossier direct.
     *
     * @param Axis $broaderAxis
     *
     * @return boolean
     */
    public function hasDirectBroader(Axis $broaderAxis)
    {
        return $this->directBroaders->contains($broaderAxis);
    }

    /**
     * Supprime l'axe donné des axes plus grossiers directs.
     *
     * @param Axis $broaderAxis
     */
    public function removeDirectBroader(Axis $broaderAxis)
    {
        if ($this->hasDirectBroader($broaderAxis)) {
            $this->directBroaders->removeElement($broaderAxis);
            $broaderAxis->setDirectNarrower();
        }
    }

    /**
     * Indique si l'axe possède des axes grossiers directs.
     *
     * @return bool
     */
    public function hasDirectBroaders()
    {
        return (count($this->directBroaders) > 0) ? true : false;
    }

    /**
     * Retourne l'ensemble des axes grossiers directs.
     *
     * @return Axis[]
     */
    public function getDirectBroaders()
    {
        return $this->directBroaders->toArray();
    }

    /**
     * Retourne récursivement, tous les axes plus grossiers.
     *
     * @return Axis[]
     */
    public function getAllBroaders()
    {
        $broaders = array();
        foreach ($this->directBroaders as $directBroader) {
            foreach ($directBroader->getAllBroaders() as $recursiveBroader) {
                $broaders[] = $recursiveBroader;
            }
            $broaders[] = $directBroader;
        }
        return $broaders;
    }

    /**
     * Vérifie si l'axe courant est plus fin que l'axe donné.
     *
     * @param Axis $axis
     *
     * @return bool
     */
    public function isNarrowerThan($axis)
    {
        $directNarrower = $axis->getDirectNarrower();
        return (($this == $directNarrower) || ((null !== $directNarrower) && $this->isNarrowerThan($directNarrower)));
    }

    /**
     * Vérifie si l'axe courant est plus grossier que l'axe donné.
     *
     * @param Axis $axis
     *
     * @return bool
     */
    public function isBroaderThan($axis)
    {
        return $axis->isNarrowerThan($this);
    }

    /**
     * Ajoute un membre à l'axe.
     *
     * @param AxisMember $member
     */
    public function addMember(AxisMember $member)
    {
        if (!($this->hasMember($member))) {
            $this->members->add($member);
            $member->setAxis($this);
        }
    }

    /**
     * Vérifie si le membre passé fait partie de l'axe.
     *
     * @param AxisMember $member
     *
     * @return boolean
     */
    public function hasMember(AxisMember $member)
    {
        return $this->members->contains($member);
    }

    /**
     * Supprime le membre donné.
     *
     * @param AxisMember $member
     */
    public function removeMember($member)
    {
        if ($this->hasMember($member)) {
            $this->members->removeElement($member);
            $member->setAxis(null);
        }
    }

    /**
     * Indique si l'axe possède des membres.
     *
     * @return bool
     */
    public function hasMembers()
    {
        return count($this->members) > 0;
    }

    /**
     * Retourne les membres de l'axe.
     *
     * @return AxisMember[]
     */
    public function getMembers()
    {
        return $this->members->toArray();
    }

    /**
     * Indique si l'axe est racine, c'est à dire si il n'a pas d'axe plus fin.
     *
     * @return bool
     */
    public function isRoot()
    {
        return $this->directNarrower === null;
    }

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
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
     * @return ClassificationLibrary
     */
    public function getLibrary()
    {
        return $this->library;
    }

    /**
     * @return array
     */
    protected function getContext()
    {
        return ['directNarrower' => $this->directNarrower];
    }
}
