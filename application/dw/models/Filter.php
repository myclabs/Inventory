<?php
/**
 * @author valentin.claras
 * @author cyril.perraud
 * @package DW
 * @subpackage Model
 */

use Doctrine\Common\Collections\Collection;
use Doctrine\Common\Collections\ArrayCollection;

/**
 * Classe permettant de gérer un filtre sur un rapport.
 *
 * @package DW
 * @subpackage Model
 */
class DW_Model_Filter extends Core_Model_Entity
{
    // Constantes de tris et de filtres.
    const QUERY_REPORT = 'report';


    /**
     * Identifiant unique du Filter.
     *
     * @var int
     */
    protected $id = null;

    /**
     * Report dans lequel s'applique le Filter.
     *
     * @var DW_Model_Report
     */
    protected $report = null;

    /**
     * Axis sur lequel s'exerce le Filter.
     *
     * @var DW_Model_Axis
     */
    protected $axis = null;

    /**
     * Ensembles de Member filtrés.
     *
     * @var Collection|DW_Model_Member[]
     */
    protected $members = null;


    /**
     * Constructeur de l'objet
     *
     * @param DW_Model_Report $report
     * @param DW_Model_Axis $axis
     */
    public function __construct(DW_Model_Report $report, DW_Model_Axis $axis)
    {
        $this->members = new ArrayCollection();

        $this->report = $report;
        $this->report->addFilter($this);
        $this->axis = $axis;
    }

    /**
     * Récupération du Report.
     *
     * @return DW_Model_Report
     */
    public function getReport()
    {
        return $this->report;
    }

    /**
     * Renvoie l'Axis sur lequel s'exerce le Filter.
     */
    public function getAxis()
    {
        return $this->axis;
    }

    /**
     * Ajoute un Member au Report.
     *
     * @param DW_Model_Member $member
     */
    public function addMember(DW_Model_Member $member)
    {
        if (!($this->hasMember($member))) {
            $this->members->add($member);
        }
    }

    /**
     * Vérifie si le Filter possède le Member donné.
     *
     * @param DW_Model_Member $member
     *
     * @return boolean
     */
    public function hasMember(DW_Model_Member $member)
    {
        return $this->members->contains($member);
    }

    /**
     * Retire un Member de ceux utilisés par le Report.
     *
     * @param DW_Model_Member $member
     */
    public function removeMember($member)
    {
        if ($this->hasMember($member)) {
            $this->members->removeElement($member);
        }
    }

    /**
     * Vérifie si l Report possède au moins un Member.
     *
     * @return bool
     */
    public function hasMembers()
    {
        return !$this->members->isEmpty();
    }

    /**
     * Renvoie un tableau contenant tous les Member du Report.
     *
     * @return DW_Model_Member[]
     */
    public function getMembers()
    {
        return $this->members->toArray();
    }

}