<?php

namespace AF\Domain\Output;

use Classification\Domain\IndicatorAxis;
use Classification\Domain\AxisMember;
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
     * @param IndicatorAxis $axis
     * @param AxisMember    $member
     */
    public function __construct(IndicatorAxis $axis, AxisMember $member)
    {
        $this->setAxis($axis);
        $this->setMember($member);
    }

    /**
     * @return IndicatorAxis
     */
    public function getAxis()
    {
        return IndicatorAxis::loadByRef($this->refAxis);
    }

    /**
     * @param IndicatorAxis $axis
     */
    public function setAxis(IndicatorAxis $axis)
    {
        $this->refAxis = $axis->getRef();
    }

    /**
     * @return AxisMember
     */
    public function getMember()
    {
        return AxisMember::loadByRefAndAxis($this->refMember, $this->getAxis());
    }

    /**
     * @param AxisMember $member
     */
    public function setMember(AxisMember $member)
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
