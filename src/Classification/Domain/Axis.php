<?php

namespace Classification\Domain;

use Core\Translation\TranslatedString;
use Core_Exception_NotFound;
use Core_Exception_TooMany;
use Core_Exception_UndefinedAttribute;
use Core_Model_Entity;
use Core_Strategy_Ordered;
use Doctrine\Common\Collections\Collection;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Criteria;

/**
 * Axis de Classification.
 *
 * @author valentin.claras
 */
class Axis extends Core_Model_Entity
{
    use Core_Strategy_Ordered;

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
     * @var int
     */
    protected $ref;

    /**
     * @var TranslatedString
     */
    protected $label;

    /**
     * @var Axis
     */
    protected $directNarrower;

    /**
     * @var Collection|Axis[]
     */
    protected $directBroaders;

    /**
     * @var Collection|Member[]
     */
    protected $members;


    public function __construct(ClassificationLibrary $library)
    {
        $this->directBroaders = new ArrayCollection();
        $this->members = new ArrayCollection();
        $this->library = $library;
        $this->label = new TranslatedString();
    }

    /**
     * @param string $ref
     * @return Axis $axis
     */
    public static function loadByRef($ref)
    {
        return self::getEntityRepository()->loadBy(array('ref' => $ref));
    }

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @return ClassificationLibrary
     */
    public function getLibrary()
    {
        return $this->library;
    }

    /**
     * @param String $ref
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
     * @return TranslatedString
     */
    public function getLabel()
    {
        return $this->label;
    }

    /**
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
     * @return Axis|null
     */
    public function getDirectNarrower()
    {
        return $this->directNarrower;
    }

    /**
     * @return bool
     */
    public function isRoot()
    {
        return $this->directNarrower === null;
    }

    /**
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
     * @param Axis $broaderAxis
     * @return boolean
     */
    public function hasDirectBroader(Axis $broaderAxis)
    {
        return $this->directBroaders->contains($broaderAxis);
    }

    /**
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
     * @return bool
     */
    public function hasDirectBroaders()
    {
        return (count($this->directBroaders) > 0) ? true : false;
    }

    /**
     * @return Axis[]
     */
    public function getDirectBroaders()
    {
        return $this->directBroaders->toArray();
    }

    /**
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
     * @param Axis $axis
     * @return bool
     */
    public function isNarrowerThan($axis)
    {
        $directNarrower = $axis->getDirectNarrower();
        return (($this == $directNarrower) || ((null !== $directNarrower) && $this->isNarrowerThan($directNarrower)));
    }

    /**
     * @param Axis $axis
     * @return bool
     */
    public function isBroaderThan($axis)
    {
        return $axis->isNarrowerThan($this);
    }

    /**
     * @param Member $member
     */
    public function addMember(Member $member)
    {
        if (!($this->hasMember($member))) {
            $this->members->add($member);
            $member->setAxis($this);
        }
    }

    /**
     * @param Member $member
     * @return boolean
     */
    public function hasMember(Member $member)
    {
        return $this->members->contains($member);
    }

    /**
     * @param Member $member
     */
    public function removeMember($member)
    {
        if ($this->hasMember($member)) {
            $this->members->removeElement($member);
            $member->setAxis(null);
        }
    }

    /**
     * @return bool
     */
    public function hasMembers()
    {
        return count($this->members) > 0;
    }

    /**
     * @param string $ref
     * @throws \Core_Exception_NotFound
     * @throws \Core_Exception_TooMany
     * @return Member
     */
    public function getMemberByRef($ref)
    {
        $criteria = Criteria::create();
        $criteria->where($criteria->expr()->eq('ref', $ref));
        $member = $this->members->matching($criteria)->toArray();

        if (count($member) === 0) {
            throw new Core_Exception_NotFound('No Axis in Organization matching ref "'.$ref.'".');
        } elseif (count($member) > 1) {
            throw new Core_Exception_TooMany('Too many Axis in Organization matching "'.$ref.'".');
        }

        return array_pop($member);
    }

    /**
     * Retourne les membres de l'axe.
     *
     * @return Member[]
     */
    public function getMembers()
    {
        return $this->members->toArray();
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
        return ['library' => $this->library, 'directNarrower' => $this->directNarrower];
    }
}
