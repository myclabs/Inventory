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
     * Supprime l'ensemble des résultats de la Cell donnée.
     *
     * @param Cell $cell
     */
    public function clearDWResultsFromCell(Cell $cell)
    {
        $cell->deleteDWResults();
    }

    /**
     * Peuple les cubes de DW alimentés par et avec les résultats de la Cell donnée.
     *
     * @param Cell $cell
     */
    public function populateDWResultsFromCell(Cell $cell)
    {
        $cell->createDWResults();
    }

    /**
     * Supprime l'ensemble des résultats du Cube de DW de la Cell donnée.
     *
     * @param Cell $cell
     */
    public function clearDWResultsForCell(Cell $cell)
    {
        foreach ($cell->getPopulatingCells() as $populatingCell) {
            $populatingCell->deleteDWResultsForDWCube($cell->getDWCube());
        }
    }

    /**
     * Peuple le cube de DW de la Cell donnée avec les résultats de l'ensemble des inputs enfants.
     *
     * @param Cell $cell
     */
    public function populateDWResultsForCell(Cell $cell)
    {
        foreach ($cell->getPopulatingCells() as $populatingCell) {
            $populatingCell->createDWResultsForDWCube($cell->getDWCube());
        }
    }

    /**
     * Peuple le cube de DW de la Cell donnée avec les résultats de l'ensemble des inputs enfants.
     *
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
