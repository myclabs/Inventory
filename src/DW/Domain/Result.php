<?php
/**
 * @author valentin.claras
 */

namespace DW\Domain;

use Calc_Value;
use Core_Model_Entity;
use Doctrine\Common\Collections\ArrayCollection;

/**
 * @package    DW
 * @subpackage Domain
 */
class Result extends Core_Model_Entity
{
    // Constantes de tris et de filtres.
    const QUERY_CUBE = 'cube';
    const QUERY_INDICATOR = 'indicator';


    /**
     * @var int
     */
    protected $id;

    /**
     * @var Cube
     */
    protected $cube;

    /**
     * @var Indicator
     */
    protected $indicator;

    /**
     * @var ArrayCollection|Member[]
     */
    protected $members = [];

    /**
     * @var Calc_Value
     */
    protected $value = null;


    public function __construct(Indicator $indicator)
    {
        $this->members = new ArrayCollection();

        $this->cube = $indicator->getCube();
        $this->indicator = $indicator;
    }

    /**
     * @return string
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @return Cube
     */
    public function getCube()
    {
        return $this->cube;
    }

    /**
     * @param Member $member
     */
    public function addMember(Member $member)
    {
        if (!($this->hasMember($member))) {
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
     * @param Member $member
     */
    public function removeMember(Member $member)
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

    /**
     * @param Axis $axis
     * @return Member
     */
    public function getMemberForAxis(Axis $axis)
    {
        foreach ($this->members as $member) {
            if ($member->getAxis() === $axis) {
                return $member;
            }
        }
        return null;
    }

    /**
     * @return Indicator
     */
    public function getIndicator()
    {
        return $this->indicator;
    }

    /**
     * @param Calc_Value $value
     */
    public function setValue(Calc_Value $value)
    {
        $this->value = $value;
    }

    /**
     * @return Calc_Value
     */
    public function getValue()
    {
        return $this->value;
    }
}
