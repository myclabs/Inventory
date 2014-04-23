<?php

namespace AF\Domain\Output;

use Classification\Domain\Axis;
use Classification\Domain\Member;
use Core_Model_Entity;

/**
 * @author matthieu.napoli
 */
class OutputIndex extends Core_Model_Entity
{
    /**
     * @var int
     */
    protected $id;

    /**
     * Reference of the classification axis
     * @var string
     */
    protected $refAxis;

    /**
     * Reference of the classification member
     * @var string
     */
    protected $refMember;

    /**
     * Variable nécessaire pour faire la relation inverse et faire marcher le delete cascade
     * TODO À supprimer quand le bug dans Doctrine aura disparu
     * @var OutputElement[]
     */
    protected $outputElements;


    /**
     * @param Axis $axis
     * @param Member    $member
     */
    public function __construct(Axis $axis, Member $member)
    {
        $this->setAxis($axis);
        $this->setMember($member);
    }

    /**
     * @return Axis
     */
    public function getAxis()
    {
        return Axis::loadByRef($this->refAxis);
    }

    /**
     * @param Axis $axis
     */
    public function setAxis(Axis $axis)
    {
        $this->refAxis = $axis->getRef();
    }

    /**
     * @return Member
     */
    public function getMember()
    {
        return $this->getAxis()->getMemberByRef($this->refMember);
    }

    /**
     * @param Member $member
     */
    public function setMember(Member $member)
    {
        $this->refMember = $member->getRef();
    }

    /**
     * @return string
     */
    public function getRefAxis()
    {
        return $this->refAxis;
    }

    /**
     * @return string
     */
    public function getRefMember()
    {
        return $this->refMember;
    }
}
