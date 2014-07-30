<?php

namespace Orga\Domain\Report;

use Core_Model_Entity;
use DW\Domain\Report;
use User\Domain\User;

/**
 * CellReport
 *
 * @author valentin.claras
 */
class CellReport extends Core_Model_Entity
{
    /**
     * @var int
     */
    protected $id;

    /**
     * @var Report
     */
    protected $cellDWReport;

    /**
     * @var User
     */
    protected $owner;


    public function __construct(Report $cellDWReport, User $owner)
    {
        $this->cellDWReport = $cellDWReport;
        $this->owner = $owner;
    }

    /**
     * @param Report $cellDWReport
     * @return CellReport
     */
    public static function loadByCellDWReport(Report $cellDWReport)
    {
        return self::getEntityRepository()->loadBy(['cellDWReport' => $cellDWReport]);
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
    public function getCellDWReport()
    {
        return $this->cellDWReport;
    }

    /**
     * @return User
     */
    public function getOwner()
    {
        return $this->owner;
    }
}
