<?php
/**
 * @author valentin.claras
 */

namespace DW\Domain;

use Core\Translation\TranslatedString;
use Core_Exception_InvalidArgument;
use Core_Exception_NotFound;
use Core_Exception_TooMany;
use Core_Model_Entity;
use Core_Strategy_Ordered;
use Doctrine\Common\Collections\Collection;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Criteria;

/**
 * @package    DW
 * @subpackage Domain
 */
class Axis extends Core_Model_Entity
{
    use Core_Strategy_Ordered;

    // Constantes de tris et de filtres.
    const QUERY_REF = 'ref';
    const QUERY_LABEL = 'label';
    const QUERY_POSITION = 'position';
    const QUERY_NARROWER = 'directNarrower';
    const QUERY_CUBE = 'cube';


    /**
     * @var int
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
     * @var Cube
     */
    protected $cube = null;

    /**
     * @var Axis
     */
    protected $directNarrower = null;

    /**
     * @var Collection|Axis[]
     */
    protected $directBroaders = null;

    /**
     * @var Collection|Member[]
     */
    protected $members = null;


    public function __construct(Cube $cube)
    {
        $this->label = new TranslatedString();
        $this->directBroaders = new ArrayCollection();
        $this->members = new ArrayCollection();

        $this->cube = $cube;
        $this->cube->addAxis($this);
        $this->setPosition();
    }

    /**
     * @return array
     */
    protected function getContext()
    {
        return ['cube' => $this->cube, 'directNarrower' => $this->directNarrower];
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
     * @return Cube
     */
    public function getCube()
    {
        return $this->cube;
    }

    /**
     * @param Axis $narrowerAxis
     * @throws \Core_Exception_InvalidArgument
     */
    public function setDirectNarrower(Axis $narrowerAxis = null)
    {
        if ($narrowerAxis->isBroaderThan($this)) {
            throw new Core_Exception_InvalidArgument('The given axis can\'t be broader than this.');
        }

        if ($this->directNarrower !== $narrowerAxis) {
            if ($this->directNarrower !== null) {
                $this->directNarrower->removeDirectBroader($this);
            }
            $this->deletePosition();
            $this->directNarrower = $narrowerAxis;
            if ($narrowerAxis !== null) {
                $narrowerAxis->addDirectBroader($this);
            }
            $this->setPosition();
        }
    }

    /**
     * @return Axis
     */
    public function getDirectNarrower()
    {
        return $this->directNarrower;
    }

    /**
     * @return Axis[]
     */
    public function getAllNarrowers()
    {
        $narrowers = [];
        $axis = $this;
        while ($axis->getDirectNarrower() !== null) {
            $narrowers[] = $axis->getDirectNarrower();
            $axis = $axis->getDirectNarrower();
        }
        return $narrowers;
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
    public function removeDirectBroader($broaderAxis)
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
        return !$this->directBroaders->isEmpty();
    }

    /**
     * @return Axis[]
     */
    public function getDirectBroaders()
    {
        $directBroaders = $this->directBroaders->toArray();

        uasort(
            $directBroaders,
            function (Axis $a, Axis $b) {
                return $a->getPosition() - $b->getPosition();
            }
        );

        return $directBroaders;
    }

    /**
     * @return Axis[]
     */
    public function getAllBroadersFirstOrdered()
    {
        $broaders = [];
        foreach ($this->directBroaders as $directBroader) {
            $broaders[] = $directBroader;
            foreach ($directBroader->getAllBroadersFirstOrdered() as $recursiveBroader) {
                $broaders[] = $recursiveBroader;
            }
        }
        return $broaders;
    }

    /**
     * @return Axis[]
     */
    public function getAllBroadersLastOrdered()
    {
        $broaders = [];
        foreach ($this->directBroaders as $directBroader) {
            foreach ($directBroader->getAllBroadersLastOrdered() as $recursiveBroader) {
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
    public function isNarrowerThan(Axis $axis)
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
     * @param Axis $axis
     * @return bool
     */
    public function isTransverseWith($axis)
    {
        if ($axis->isBroaderThan($this) || $this->isBroaderThan($axis) || ($axis === $this)) {
            return false;
        }
        return true;
    }

    /**
     * @param Member $member
     * @throws Core_Exception_InvalidArgument
     */
    public function addMember(Member $member)
    {
        if ($member->getAxis() !== $this) {
            throw new Core_Exception_InvalidArgument();
        }

        if (!$this->hasMember($member)) {
            $this->members->add($member);
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
     * @param string $ref
     * @throws Core_Exception_NotFound
     * @throws Core_Exception_TooMany
     * @return Member
     */
    public function getMemberByRef($ref)
    {
        $criteria = Criteria::create();
        $criteria->where($criteria->expr()->eq('ref', $ref));
        $member = $this->members->matching($criteria)->toArray();

        if (empty($member)) {
            throw new Core_Exception_NotFound('No "Member" matching "' . $ref . '".');
        } else {
            if (count($member) > 1) {
                throw new Core_Exception_TooMany('Too many "Member" matching "' . $ref . '".');
            }
        }

        return array_pop($member);
    }

    /**
     * @param Member $member
     */
    public function removeMember($member)
    {
        if ($this->hasMember($member)) {
            $this->members->removeElement($member);
        }
    }

    /**
     * @return bool
     */
    public function hasMembers()
    {
        return !$this->members->isEmpty();
    }

    /**
     * @return Member[]
     */
    public function getMembers()
    {
        return $this->members->toArray();
    }
}
