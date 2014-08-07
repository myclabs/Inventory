<?php
namespace Orga\Domain\Service\ETL;

use Orga\Domain\Cell;
use Orga\Domain\Service\Cell\Input\CellInputService;

/**
 * Classe permettant de peupler DW.
 *
 * @author valentin.claras
 */
class ETLDataService implements ETLDataInterface
{
    /**
     * @var CellInputService
     */
    private $inputService;


    public function __construct(CellInputService $inputService)
    {
        $this->inputService = $inputService;
    }

    /**
     * @param Cell $cell
     */
    public function clearDWCubesFromCellDWResults(Cell $cell)
    {
        $cell->deleteDWResults();
    }

    /**
     * @param Cell $cell
     */
    public function populateDWCubesWithCellInputResults(Cell $cell)
    {
        $cell->createDWResults();
    }

    /**
     * @param Cell $cell
     */
    public function clearCellDWCubeFromDWResults(Cell $cell)
    {
        foreach ($cell->getPopulatingCells() as $populatingCell) {
            $populatingCell->deleteDWResultsForDWCube($cell->getDWCube());
        }
    }

    /**
     * @param Cell $cell
     */
    public function populateCellDWCubeWithInputResults(Cell $cell)
    {
        foreach ($cell->getPopulatingCells() as $populatingCell) {
            $populatingCell->createDWResultsForDWCube($cell->getDWCube());
        }
    }

    /**
     * @param Cell $cell
     */
    public function calculateResultsForCellAndChildren(Cell $cell)
    {
        $granularity = $cell->getGranularity();

        foreach ($cell->getGranularity()->getWorkspace()->getInputGranularities() as $inputGranularity) {
            if ($inputGranularity === $cell->getGranularity()) {
                $this->calculateCellResults($cell);
            } elseif ($inputGranularity->isNarrowerThan($granularity)) {
                foreach ($cell->getChildCellsForGranularity($inputGranularity) as $childCell) {
                    $this->calculateCellResults($childCell);
                }
            }
        }
    }

    /**
     * @param Cell $cell
     */
    private function calculateCellResults(Cell $cell)
    {
        $inputSet = $cell->getAFInputSetPrimary();
        if ($inputSet === null) {
            // Pas de saisie.
            return;
        }

        $inputGranularity = $cell->getGranularity();
        if ($inputGranularity->getRef() === $inputGranularity->getInputConfigGranularity()->getRef()) {
            $af = $cell->getSubCellsGroupForInputGranularity($inputGranularity)->getAF();
        } else {
            $af = $cell->getParentCellForGranularity($inputGranularity->getInputConfigGranularity())
                ->getSubCellsGroupForInputGranularity($inputGranularity)->getAF();
        }

        $this->inputService->updateResults($cell, $inputSet, $af);
    }
}
