<?php

use User\Domain\User;

/**
 * Service Orga.
 *
 * @author valentin.claras
 */
class Orga_Service_Report implements Core_Event_ObserverInterface
{
    /**
     * @var string[]
     */
    private static $copiedReports = [];

    /**
     * @param string $event
     * @param DW_Model_Report $subject
     * @param array $arguments
     * @throws Core_Exception_InvalidArgument
     */
    public static function applyEvent($event, $subject, $arguments = [])
    {
        /** @var Orga_Service_ETLStructure $etlStructureService */
        $etlStructureService = \Core\ContainerSingleton::getContainer()->get('Orga_Service_ETLStructure');

        try {
            Simulation_Model_Set::loadByDWCube($subject->getCube()->getId());
            return;
        } catch (Core_Exception_NotFound $e) {
            // Le Report n'est pas issue d'une simulation.
        }
        switch ($event) {
            case DW_Model_Report::EVENT_SAVE:
                if ($subject->getCube()->getId() == null) {
                    return ;
                }
                try {
                    // Nécessaire pour détecter d'où est issu le Report.
                    Orga_Model_Granularity::loadByDWCube($subject->getCube());
                    $granularityReport = new Orga_Model_GranularityReport($subject);
                    $etlStructureService->createCellsDWReportFromGranularityReport($granularityReport);
                    foreach ($granularityReport->getCellDWReports() as $cellReport) {
                        self::$copiedReports[] = spl_object_hash($cellReport);
                    }
                    $granularityReport->save();
                } catch (Core_Exception_NotFound $e) {
                    if (!in_array(spl_object_hash($subject), self::$copiedReports)) {
                        // Le Report n'est pas issue d'un Cube de Granularity.
                    }
                }
                break;
            case DW_Model_Report::EVENT_UPDATED:
                try {
                    $etlStructureService->updateCellsDWReportFromGranularityReport(
                        Orga_Model_GranularityReport::loadByGranularityDWReport($subject)
                    );
                } catch (Core_Exception_NotFound $e) {
                    // Le Report n'est pas issue d'un Cube de DW de Granularity.
                }
                break;
            case DW_Model_Report::EVENT_DELETE:
                try {
                    $granularityReport = Orga_Model_GranularityReport::loadByGranularityDWReport($subject);
                    $granularityReport->delete();
                } catch (Core_Exception_NotFound $e) {
                    // Le Report n'est pas issue d'un Cube de Granularity.
                    try {
                        $cellReport = Orga_Model_CellReport::loadByCellDWReport($subject);
                        $cellReport->delete();
                    } catch (Core_Exception_NotFound $e) {
                        // Le Report n'est pas issue d'un Utilisateur.
                        foreach (Orga_Model_GranularityReport::loadList() as $granularityReport) {
                            /** @var Orga_Model_GranularityReport $granularityReport */
                            if ($granularityReport->hasCellDWReport($subject)) {
                                $granularityReport->removeCellDWReport($subject);
                            }
                        }
                    }
                }
                break;
        }
    }

}
