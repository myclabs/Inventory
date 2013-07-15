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
                $this->calculateResults($cell, $inputGranularity);
            } else if ($inputGranularity->isNarrowerThan($granularity)) {
                foreach ($cell->getChildCellsForGranularity($inputGranularity) as $childCell) {
                    $this->calculateResults($childCell, $inputGranularity);
                }
            }
        }
    }

    /**
     * @param Orga_Model_Cell $cell
     * @param Orga_Model_Granularity $inputGranularity
     */
    private function calculateResults(Orga_Model_Cell $cell, Orga_Model_Granularity $inputGranularity)
    {
        try {
            $inputSet = $cell->getAFInputSetPrimary();
            if ($inputSet->isInputComplete()) {
                $this->clearDWResultsFromCell($cell);
            }
            $inputSet->updateCompletion();
            if ($inputSet->isInputComplete()) {
                if ($cell->getGranularity()->getRef() === $inputGranularity->getInputConfigGranularity()->getRef()) {
                    $af = $cell->getCellsGroupForInputGranularity($inputGranularity)->getAF();
                } else {
                    $af = $cell->getParentCellForGranularity(
                        $inputGranularity->getInputConfigGranularity()
                    )->getCellsGroupForInputGranularity($inputGranularity)->getAF();
                }
                // Exécute l'AF et calcule les totaux
                $af->execute($inputSet);
                $inputSet->getOutputSet()->calculateTotals();
                $this->populateDWResultsFromCell($cell);
            }
        } catch (Core_Exception_UndefinedAttribute $e) {
            // Pas de saisie.
        }
    }

}