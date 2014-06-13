<?php
/**
 * @package Orga
 * @subpackage Model
 */

use Doctrine\Common\Collections\Collection;
use Doctrine\Common\Collections\ArrayCollection;
use DW\Domain\Report;

/**
 * Classe faisant le lien entre les rapport des granularités et des cellules.
 * @author valentin.claras
 * @package Orga
 * @subpackage Model
 */
class Orga_Model_GranularityReport extends Core_Model_Entity
{
    /**
     * Identifiant unique du Granularity.
     *
     * @var int
     */
    protected $id;

    /**
     * Report du Cube de DW de la granularité concerné.
     *
     * @var Report
     */
    protected $granularityDWReport;

    /**
     * @var Collection|Report[]
     */
    protected $cellDWReports;


    public function __construct(Report $granularityDWReport)
    {
        $this->granularityDWReport = $granularityDWReport;

        $this->cellDWReports = new ArrayCollection();
    }

    /**
     * Charge le GranularityReport correspondant à un Report de DW.
     *
     * @param Report $dWReport
     *
     * @return Orga_Model_GranularityReport
     */
    public static function loadByGranularityDWReport(Report $dWReport)
    {
        return self::getEntityRepository()->loadBy(['granularityDWReport' => $dWReport]);
    }

    /**
     * @return string
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Renvoie le Report de DW.
     *
     * @return Report
     */
    public function getGranularityDWReport()
    {
        return $this->granularityDWReport;
    }

    /**
     * Ajoute un Report de DW pour un Cell au GranularityReport.
     *
     * @param Report $cellDWReport
     */
    public function addCellDWReport(Report $cellDWReport)
    {
        if (!($this->hasCellDWReport($cellDWReport))) {
            $this->cellDWReports->add($cellDWReport);
        }
    }

    /**
     * Vérifie si le GranularityReport possède le Report de DW pour un Cell donné.
     *
     * @param Report $cellDWReport
     *
     * @return boolean
     */
    public function hasCellDWReport(Report $cellDWReport)
    {
        return $this->cellDWReports->contains($cellDWReport);
    }

    /**
     * Retire le Report de DW pour un Cell donné des Report de DW de Cell du GranularityReport.
     *
     * @param Report $cellDWReport
     */
    public function removeCellDWReport(Report $cellDWReport)
    {
        if ($this->hasCellDWReport($cellDWReport)) {
            $this->cellDWReports->removeElement($cellDWReport);
        }
    }

    /**
     * Vérifie que le GranularityReport possède au moins un Report de DW de Cell.
     *
     * @return bool
     */
    public function hasCellDWReports()
    {
        return !$this->cellDWReports->isEmpty();
    }

    /**
     * Renvoie un tableau des Report de DW de Cell du Granularity.
     *
     * @return Report[]
     */
    public function getCellDWReports()
    {
        return $this->cellDWReports->toArray();
    }
}
