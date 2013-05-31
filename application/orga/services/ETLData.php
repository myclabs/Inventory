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
class Orga_Service_ETLData extends Core_Singleton
{
    /**
     * Renvoie l'instance Singleton de la classe.
     *
     * @return Orga_Service_ETLData
     */
    public static function getInstance()
    {
        return parent::getInstance();
    }


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
        try {
            $cell->createDWResults();
        } catch (Core_Exception_UndefinedAttribute $e) {
            // Pas de saisie sur le Cell.
        }
    }

    /**
     * Supprime l'ensemble des résultats du Cube de DW de la Cell donnée.
     *
     * @param Orga_Model_Cell $cell
     */
    public function clearDWResultsForCell($cell)
    {
        foreach ($cell->getPopulatingCells() as $populatingCell) {
            $populatingCell->deleteDWResultsForCube($cell->getDWCube());
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
            $populatingCell->createDWResultsForCube($cell->getDWCube());
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

        foreach ($cell->getGranularity()->getProject()->getInputGranularities() as $inputGranularity) {
            if ($inputGranularity === $cell->getGranularity()) {
                try {
                    $inputSet = $cell->getAFInputSetPrimary();
                    if ($inputSet->isInputComplete()) {
                        $this->clearDWResultsFromCell($cell);
                    }
                    $inputSet->updateCompletion();
                    if ($inputSet->isInputComplete()) {
                        $af = $cell->getParentCellForGranularity(
                            $inputGranularity->getInputConfigGranularity()
                        )->getCellsGroupForInputGranularity($inputGranularity)->getAF();
                        // Exécute l'AF et calcule les totaux
                        $af->execute($inputSet);
                        $inputSet->getOutputSet()->calculateTotals();
                        $this->populateDWResultsFromCell($cell);
                    }
                } catch (Core_Exception_UndefinedAttribute $e) {
                    // Pas de saisie.
                }
            } else if ($inputGranularity->isNarrowerThan($granularity)) {
                foreach ($cell->getChildCellsForGranularity($inputGranularity) as $childCell) {
                    try {
                        $inputSet = $childCell->getAFInputSetPrimary();
                        if ($inputSet->isInputComplete()) {
                            $this->clearDWResultsFromCell($childCell);
                        }
                        $inputSet->updateCompletion();
                        if ($inputSet->isInputComplete()) {
                            $af = $childCell->getParentCellForGranularity(
                                $inputGranularity->getInputConfigGranularity()
                            )->getCellsGroupForInputGranularity($inputGranularity)->getAF();
                            // Exécute l'AF et calcule les totaux
                            $af->execute($inputSet);
                            $inputSet->getOutputSet()->calculateTotals();
                            $this->populateDWResultsFromCell($childCell);
                        }
                    } catch (Core_Exception_UndefinedAttribute $e) {
                        // Pas de saisie.
                    }
                }
            }
        }
    }

}