<?php
/**
 * @package Simulation
 * @subpackage Service
 */

/**
 * Classe permettant de peupler DW
 * @author valentin.claras
 * @package Simulation
 * @subpackage Service
 */
class Simulation_Service_ETLData
{
    /**
     * Supprime l'ensemble des résultats du scenario donné.
     *
     * @param Simulation_Model_Scenario $scenario
     */
    public function clearDWResultsFromScenario($scenario)
    {
        $scenario->deleteDWResults();
    }

    /**
     * Peuple le cube de DW du set avec les résultats issues de l'inputSetPrimary.
     *
     * @param Simulation_Model_Scenario $scenario
     */
    public function populateDWResultsFromScenario($scenario)
    {
        $scenario->createDWResults();
    }
}