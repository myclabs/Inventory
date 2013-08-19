<?php
/**
 * @package Orga
 * @subpackage Service
 */
/**
 * Classe permettant de peupler DW
 * @author valentin.claras
 * @package Orga
 * @subpackage Service
 */
class Orga_Service_ETLData
{

    /**
     * Supprime l'ensemble des résultats de la Cell donnée.
     *
     * @param Orga_Model_Cell $cell
     */
    public function clearDWResultsFromCell($cell)
    {
        $cell->deleteDWResults();
    }

    /**
     * Peuple les cubes de DW alimentés par et avec les résultats de la Cell donnée.
     *
     * @param Orga_Model_Cell $cell
     */
    public function populateDWResultsFromCell($cell)
    {
        $cell->createDWResults();
    }

    /**
     * Supprime l'ensemble des résultats du Cube de DW de la Cell donnée.
     *
     * @param Orga_Model_Cell $cell
     */
    public function clearDWResultsForCell($cell)
    {
        foreach ($cell->getPopulatingCells() as $populatingCell) {
            $populatingCell->deleteDWResultsForDWCube($cell->getDWCube());
        }
    }

    /**
     * Peuple le cube de DW de la Cell donnée avec les résultats de l'ensemble des inputs enfants.
     *
     * @param Orga_Model_Cell $cell
     */
    public function populateDWResultsForCell($cell)
    {
        foreach ($cell->getPopulatingCells() as $populatingCell) {
            $populatingCell->createDWResultsForDWCube($cell->getDWCube());
        }
    }

    /**
     * Peuple le cube de DW de la Cell donnée avec les résultats de l'ensemble des inputs enfants.
     *
     * @param Orga_Model_Cell $cell
     */
    public function calculateResultsForCellAndChildren($cell)
    {
        $granularity = $cell->getGranularity();

        foreach ($cell->getGranularity()->getOrganization()->getInputGranularities() as $inputGranularity) {
            if ($inputGranularity === $cell->getGranularity()) {
                $this->calculateCellResults($cell);
            } else if ($inputGranularity->isNarrowerThan($granularity)) {
                foreach ($cell->getChildCellsForGranularity($inputGranularity) as $childCell) {
                    $this->calculateCellResults($childCell);
                }
            }
        }
    }

    /**
     * @param Orga_Model_Cell $cell
     */
    private function calculateCellResults(Orga_Model_Cell $cell)
    {
        try {
            $inputSet = $cell->getAFInputSetPrimary();
        } catch (Core_Exception_UndefinedAttribute $e) {
            // Pas de saisie.
            return;
        }

        $inputGranularity = $cell->getGranularity();

        $inputSet->updateCompletion();
        if ($inputSet->isInputComplete()) {
            if ($inputGranularity->getRef() === $inputGranularity->getInputConfigGranularity()->getRef()) {
                $af = $cell->getCellsGroupForInputGranularity($inputGranularity)->getAF();
            } else {
                $af = $cell->getParentCellForGranularity(
                    $inputGranularity->getInputConfigGranularity()
                )->getCellsGroupForInputGranularity($inputGranularity)->getAF();
            }
            // Exécute l'AF et calcule les totaux
            $af->execute($inputSet);
            $inputSet->getOutputSet()->calculateTotals();
        }
    }

}