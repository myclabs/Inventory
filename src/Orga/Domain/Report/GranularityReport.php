<?php

namespace Orga\Domain\Report;

use Core_Model_Entity;
use Doctrine\Common\Collections\Collection;
use Doctrine\Common\Collections\ArrayCollection;
use DW\Domain\Report;

/**
 * GranularityReport
 *
 * @author valentin.claras
 */
class GranularityReport extends Core_Model_Entity
{
    /**
     * @var int
     */
    protected $id;

    /**
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
     * @param Report $dWReport
     * @return GranularityReport
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
     * @return Report
     */
    public function getGranularityDWReport()
    {
        return $this->granularityDWReport;
    }

    /**
     * @param Report $cellDWReport
     */
    public function addCellDWReport(Report $cellDWReport)
    {
        if (!($this->hasCellDWReport($cellDWReport))) {
            $this->cellDWReports->add($cellDWReport);
        }
    }

    /**
     * @param Report $cellDWReport
     * @return boolean
     */
    public function hasCellDWReport(Report $cellDWReport)
    {
        return $this->cellDWReports->contains($cellDWReport);
    }

    /**
     * @param Report $cellDWReport
     */
    public function removeCellDWReport(Report $cellDWReport)
    {
        if ($this->hasCellDWReport($cellDWReport)) {
            $this->cellDWReports->removeElement($cellDWReport);
        }
    }

    /**
     * @return bool
     */
    public function hasCellDWReports()
    {
        return !$this->cellDWReports->isEmpty();
    }

    /**
     * @return Report[]
     */
    public function getCellDWReports()
    {
        return $this->cellDWReports->toArray();
    }
}
