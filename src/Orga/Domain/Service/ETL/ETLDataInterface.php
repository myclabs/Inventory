<?php
namespace Orga\Domain\Service\ETL;

use Orga\Domain\Cell;

/**
 * Classe permettant de peupler DW.
 *
 * @author valentin.claras
 */
interface ETLDataInterface
{
    /**
     * @param Cell $cell
     */
    public function clearDWCubesFromCellDWResults(Cell $cell);

    /**
     * @param Cell $cell
     */
    public function populateDWCubesWithCellInputResults(Cell $cell);

    /**
     * @param Cell $cell
     */
    public function clearCellDWCubeFromDWResults(Cell $cell);

    /**
     * @param Cell $cell
     */
    public function populateCellDWCubeWithInputResults(Cell $cell);

    /**
     * @param Cell $cell
     */
    public function calculateResultsForCellAndChildren(Cell $cell);
}
