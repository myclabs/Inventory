<?php
/**
 * @author     matthieu.napoli
 * @package    AF
 * @subpackage Output
 */

/**
 * @package    AF
 * @subpackage Output
 */
class AF_Model_Output_Index extends Core_Model_Entity
{

    /**
     * @var int
     */
    protected $id;

    /**
     * Reference of the classif axis
     * @var string
     */
    protected $refAxis;

    /**
     * Reference of the classif member
     * @var string
     */
    protected $refMember;

    /**
     * Variable nécessaire pour faire la relation inverse et faire marcher le delete cascade
     * À supprimer quand le bug dans Doctrine aura disparu
     * @var AF_Model_Output_Element[]
     */
    protected $outputElements;


    /**
     * @param Classif_Model_Axis   $axis
     * @param Classif_Model_Member $member
     */
    public function __construct(Classif_Model_Axis $axis, Classif_Model_Member $member)
    {
        $this->setAxis($axis);
        $this->setMember($member);
    }

    /**
     * @return Classif_Model_Axis
     */
    public function getAxis()
    {
        return Classif_Model_Axis::loadByRef($this->refAxis);
    }

    /**
     * @param Classif_Model_Axis $classifAxis
     */
    public function setAxis(Classif_Model_Axis $classifAxis)
    {
        $this->refAxis = $classifAxis->getRef();
    }

    /**
     * @return Classif_Model_Member
     */
    public function getMember()
    {
        return Classif_Model_Member::loadByRefAndAxis($this->refMember, $this->getAxis());
    }

    /**
     * @param Classif_Model_Member $classifMember
     */
    public function setMember(Classif_Model_Member $classifMember)
    {
        $this->refMember = $classifMember->getRef();
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
