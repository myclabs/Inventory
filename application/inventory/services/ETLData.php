<?php
/**
 * @package Inventory
 * @subpackage Service
 */
/**
 * Classe permettant de peupler DW
 * @author valentin.claras
 * @package Inventory
 * @subpackage Service
 *
 */
class Inventory_Service_ETLData extends Core_Service
{

    /**
     * Supprime l'ensemble des résultats du CellDataProvider donné.
     *
     * @param Inventory_Model_CellDataProvider $cellDataProvider
     */
    protected function clearDWResultsFromCellDataProviderService($cellDataProvider)
    {
        $cellDataProvider->deleteDWResults();
    }

    /**
     * Peuple les cubes de DW alimentés par et avec les résultats du CellDataProvider donné.
     *
     * @param Inventory_Model_CellDataProvider $cellDataProvider
     */
    protected function populateDWResultsFromCellDataProviderService($cellDataProvider)
    {
        try {
            $cellDataProvider->createDWResults();
        } catch (Core_Exception_UndefinedAttribute $e) {
            // Pas de saisie sur le CellDataProvider.
        }
    }

    /**
     * Supprime l'ensemble des résultats du cube de DW du CellDataProvider donné.
     *
     * @param Inventory_Model_CellDataProvider $cellDataProvider
     */
    protected function clearDWResultsForCellDataProviderService($cellDataProvider)
    {
        foreach ($cellDataProvider->getPopulatingCellDataProviders() as $populatingCellDataProvider) {
            $populatingCellDataProvider->deleteDWResultsForCube($cellDataProvider->getDWCube());
        }
    }

    /**
     * Peuple le cube de DW du CellDataProvider donné avec les résultats de l'ensemble des inputs enfants.
     *
     * @param Inventory_Model_CellDataProvider $cellDataProvider
     */
    protected function populateDWResultsForCellDataProviderService($cellDataProvider)
    {
        foreach ($cellDataProvider->getPopulatingCellDataProviders() as $populatingCellDataProvider) {
            $populatingCellDataProvider->createDWResultsForCube($cellDataProvider->getDWCube());
        }
    }

    /**
     * Peuple le cube de DW du CellDataProvider donné avec les résultats de l'ensemble des inputs enfants.
     *
     * @param Inventory_Model_CellDataProvider $cellDataProvider
     */
    protected function calculateResultsForCellDataProviderAndChildrenService($cellDataProvider)
    {
        $cell = $cellDataProvider->getOrgaCell();
        $orgaGranularity = $cell->getGranularity();

        foreach ($cellDataProvider->getProject()->getAFGranularities() as $aFGranularities) {
            $inputOrgaGranularity = $aFGranularities->getAFInputOrgaGranularity();

            if ($inputOrgaGranularity === $cell->getGranularity()) {

                try {
                    $inputSet = $cellDataProvider->getAFInputSetPrimary();
                    if ($inputSet->isInputComplete()) {
                        $this->clearDWResultsFromCellDataProvider($cellDataProvider);
                    }
                    $inputSet->updateCompletion();
                    if ($inputSet->isInputComplete()) {
                        $af = $aFGranularities->getCellsGroupDataProviderForContainerCellDataProvider(
                            Inventory_Model_CellDataProvider::loadByOrgaCell(
                                $cellDataProvider->getOrgaCell()->getParentCellForGranularity(
                                    $aFGranularities->getAFConfigOrgaGranularity()
                                )
                            )
                        )->getAF();
                        // Exécute l'AF et calcule les totaux
                        $af->execute($inputSet);
                        $inputSet->getOutputSet()->calculateTotals();
                        $this->populateDWResultsFromCellDataProvider($cellDataProvider);
                    }
                } catch (Core_Exception_UndefinedAttribute $e) {
                    // Pas de saisie.
                }

            } elseif ($inputOrgaGranularity->isNarrowerThan($orgaGranularity)) {
                foreach ($cellDataProvider->getChildCellsForGranularity($inputOrgaGranularity)
                         as $childCellDataProvider
                ) {
                    try {
                        $inputSet = $childCellDataProvider->getAFInputSetPrimary();
                        if ($inputSet->isInputComplete()) {
                            $this->clearDWResultsFromCellDataProvider($childCellDataProvider);
                        }
                        $inputSet->updateCompletion();
                        if ($inputSet->isInputComplete()) {
                            $af = $aFGranularities->getCellsGroupDataProviderForContainerCellDataProvider(
                                Inventory_Model_CellDataProvider::loadByOrgaCell(
                                    $childCellDataProvider->getOrgaCell()->getParentCellForGranularity(
                                        $aFGranularities->getAFConfigOrgaGranularity()
                                    )
                                )
                            )->getAF();
                            // Exécute l'AF et calcule les totaux
                            $af->execute($inputSet);
                            $inputSet->getOutputSet()->calculateTotals();
                            $this->populateDWResultsFromCellDataProvider($childCellDataProvider);
                        }
                    } catch (Core_Exception_UndefinedAttribute $e) {
                        // Pas de saisie.
                    }
                }

            }
        }
    }

}