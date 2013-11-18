<?php
/**
 * @package Orga
 * @subpackage Model
 */

use Doctrine\Common\Collections\Collection;
use Doctrine\Common\Collections\ArrayCollection;

/**
 * Classe faisant le lien entre les rapport des granularités et des cellules.
 * @author valentin.claras
 * @package Orga
 * @subpackage Model
 */
class Orga_Model_GranularityReport extends Core_Model_Entity implements Core_Event_ObserverInterface
{
    /**
     * Identifiant unique du Granularity.
     * @var int
     */
    protected $id;

    /**
     * Report du Organization de DW concerné.
     *
     * @var DW_Model_Report
     */
    protected $granularityDWReport;

    /**
     * @var Collection|DW_Model_Report[]
     */
    protected $cellDWReports;


    public function __construct(DW_Model_Report $granularityDWReport)
    {
        /** @var \DI\Container $container */
        $container = Zend_Registry::get('container');
        /** @var Orga_Service_ETLStructure $etlStructureService */
        $etlStructureService = $container->get('Orga_Service_ETLStructure');

        $this->cellDWReports = new ArrayCollection();

        $this->granularityDWReport = $granularityDWReport;
        $etlStructureService->createCellsDWReportFromGranularityReport($this);
    }

    /**
     * @param string          $event
     * @param DW_Model_Report $subject
     * @param array           $arguments
     */
    public static function applyEvent($event, $subject, $arguments = [])
    {
        switch ($event) {
            case DW_Model_Report::EVENT_SAVE:
                try {
                    // Nécessaire pour détecter d'où est issu le Report.
                    if ($subject->getCube()->getId() !== null) {
                        Orga_Model_Granularity::loadByDWCube($subject->getCube());
                        $granularityReport = new self($subject);
                        $granularityReport->save();
                    }
                } catch (Core_Exception_NotFound $e) {
                    // Le Report n'est pas issue d'un Cube de Granularity.
                }
                break;
            case DW_Model_Report::EVENT_UPDATED:
                try {
                    /** @var \DI\Container $container */
                    $container = Zend_Registry::get('container');
                    /** @var Orga_Service_ETLStructure $etlStructureService */
                    $etlStructureService = $container->get('Orga_Service_ETLStructure');

                    $etlStructureService->updateCellsDWReportFromGranularityReport(
                        self::loadByGranularityDWReport($subject)
                    );
                } catch (Core_Exception_NotFound $e) {
                    // Le Report n'est pas issue d'un Cube de DW de Granularity.
                }
                break;
            case DW_Model_Report::EVENT_DELETE:
                try {
                    $granularityReport = self::loadByGranularityDWReport($subject);
                    $granularityReport->delete();
                } catch (Core_Exception_NotFound $e) {
                    // Le Report n'est pas issue d'un Cube de Granularity.
                    foreach (self::loadList() as $granularityReport) {
                        /** @var self $granularityReport */
                        if ($granularityReport->hasCellDWReport($subject)) {
                            $granularityReport->removeCellDWReport($subject);
                        }
                    }
                }
                break;
        }
    }

    /**
     * Charge le GranularityReport correspondant à un Report de DW.
     * 
     * @param DW_Model_Report $dWReport
     *
     * @return Orga_Model_GranularityReport
     */
    public static function loadByGranularityDWReport($dWReport)
    {
        return self::getEntityRepository()->loadBy(array('granularityDWReport' => $dWReport));
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
     * @return DW_Model_Report
     */
    public function getGranularityDWReport()
    {
        return $this->granularityDWReport;
    }

    /**
     * Ajoute un Report de DW pour un Cell au GranularityReport.
     *
     * @param DW_Model_Report $cellDWReport
     */
    public function addCellDWReport(DW_Model_Report $cellDWReport)
    {
        if (!($this->hasCellDWReport($cellDWReport))) {
            $this->cellDWReports->add($cellDWReport);
        }
    }

    /**
     * Vérifie si le GranularityReport possède le Report de DW pour un Cell donné.
     *
     * @param DW_Model_Report $cellDWReport
     *
     * @return boolean
     */
    public function hasCellDWReport(DW_Model_Report $cellDWReport)
    {
        return $this->cellDWReports->contains($cellDWReport);
    }

    /**
     * Retire le Report de DW pour un Cell donné des Report de DW de Cell du GranularityReport.
     *
     * @param DW_Model_Report $cellDWReport
     */
    public function removeCellDWReport($cellDWReport)
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
     * @return DW_Model_Report[]
     */
    public function getCellDWReports()
    {
        return $this->cellDWReports->toArray();
    }
}
