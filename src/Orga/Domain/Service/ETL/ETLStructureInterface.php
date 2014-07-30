<?php

namespace Orga\Domain\Service\ETL;

use Orga\Domain\Cell;
use Orga\Domain\Granularity;

/**
 * ETLStructureInterface
 *
 * @author valentin.claras
 */
interface ETLStructureInterface
{
    /**
     * @param Cell $cell
     */
    public function populateCellDWCube(Cell $cell);

    /**
     * @param Granularity $granularity
     */
    public function populateGranularityDWCube(Granularity $granularity);

    /**
     * @param Cell $cell
     */
    public function resetCellDWCube(Cell $cell);

    /**
     * @param Granularity $granularity
     */
    public function resetGranularityDWCube(Granularity $granularity);
}
