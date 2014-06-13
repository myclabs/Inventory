<?php
/**
 * @package Orga
 * @subpackage Model
 */

use DW\Domain\Report;
use User\Domain\User;

/**
 * Classe faisant le lien entre un rapport personalisé d'une cellule d'orga et son possesseur.
 * @author valentin.claras
 * @package Orga
 * @subpackage Model
 */
class Orga_Model_CellReport extends Core_Model_Entity
{
    /**
     * Identifiant unique du CellReport.
     *
     * @var int
     */
    protected $id;

    /**
     * Report du Cube de DW de la cellule concerné.
     *
     * @var Report
     */
    protected $cellDWReport;

    /**
     * Utilisateur ayant crée le rapport.
     *
     * @var User
     */
    protected $owner;


    public function __construct(Report $cellDWReport, User $owner)
    {
        $this->cellDWReport = $cellDWReport;
        $this->owner = $owner;
    }

    /**
     * Charge le CellReport correspondant à un Report de DW.
     *
     * @param Report $cellDWReport
     *
     * @return Orga_Model_CellReport
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
     * Renvoie le Report de DW.
     *
     * @return Report
     */
    public function getCellDWReport()
    {
        return $this->cellDWReport;
    }

    /**
     * Renvoie le possesseur du Report de DW.
     *
     * @return User
     */
    public function getOwner()
    {
        return $this->owner;
    }
}
