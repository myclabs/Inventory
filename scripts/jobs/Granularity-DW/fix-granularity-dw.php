<?php
use AF\Domain\Algorithm\Numeric\NumericAlgo;
use AF\Domain\Algorithm\Selection\MainSelectionAlgo;
use Orga\Domain\Granularity;
use Orga\Domain\Report\GranularityReport;

/**
 * Correction des associations Granularity->granularityReport
 */

define('RUN', false);

require_once __DIR__ . '/../../../application/init.php';

/** @var Granularity[] $granularities */
$granularities = Granularity::loadList();

foreach ($granularities as $granularity) {
    echo $granularity->getTag();
    if ($granularity->getCellsGenerateDWCubes()) {
        echo " -> ".$granularity->getDWCube()->getId();
        foreach ($granularity->getDWCube()->getReports() as $report) {
            echo " : ".$report->getId();
            try {
                GranularityReport::loadByGranularityDWReport($report);
            }
            catch (Exception $e) {
                $granularityReport = new GranularityReport($report);
                $granularityReport->save();
                echo "\n---".$e->getMessage();
            }
        }
    }
    echo "\n";
}

echo "Flush" . PHP_EOL;

\Core\ContainerSingleton::getEntityManager()->flush();