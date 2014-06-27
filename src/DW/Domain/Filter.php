<?php
/**
 * @author valentin.claras
 */

namespace DW\Domain;

use Core_Exception_InvalidArgument;
use Core_Model_Entity;
use Doctrine\Common\Collections\Collection;
use Doctrine\Common\Collections\ArrayCollection;

/**
 * @package    DW
 * @subpackage Domain
 */
class Filter extends Core_Model_Entity
{
    // Constantes de tris et de filtres.
    const QUERY_REPORT = 'report';


    /**
     * @var int
     */
    protected $id = null;

    /**
     * @var Report
     */
    protected $report = null;

    /**
     * @var Axis
     */
    protected $axis = null;

    /**
     * @var Collection|Member[]
     */
    protected $members = null;


    /**
     * @param Report $report
     * @param Axis   $axis
     * @throws \Core_Exception_InvalidArgument
     */
    public function __construct(Report $report, Axis $axis)
    {
        $this->members = new ArrayCollection();

        if ($report->getCube() !== $axis->getCube()) {
            throw new Core_Exception_InvalidArgument('The Report and the Axis must come from the same Cube.');
        }

        $this->report = $report;
        $this->report->addFilter($this);
        $this->axis = $axis;
    }

    /**
     * @return string
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @return Report
     */
    public function getReport()
    {
        return $this->report;
    }

    /**
     * @return Axis
     */
    public function getAxis()
    {
        return $this->axis;
    }

    /**
     * @param Member $member
     * @throws \Core_Exception_InvalidArgument
     */
    public function addMember(Member $member)
    {
        if ($member->getAxis() !== $this->getAxis()) {
            throw new Core_Exception_InvalidArgument('The Member must comes from the same Axis than the Filter.');
        }

        if (!($this->hasMember($member))) {
            $this->members->add($member);
            $this->getReport()->updateLastModification();
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
            $this->getReport()->updateLastModification();
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
     * @return Collection|Member[]
     */
    public function getMembers()
    {
        return $this->members;
    }
}
