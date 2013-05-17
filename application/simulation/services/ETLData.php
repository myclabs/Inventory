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
 *
 */
class Simulation_Service_ETLData extends Core_Service
{
    /**
     * Renvoie l'instance Singleton de la classe.
     *
     * @return Simulation_Service_ETLStructure
     */
    public static function getInstance()
    {
        return parent::getInstance();
    }


    /**
     * Supprime l'ensemble des résultats du scenario donné.
     *
     * @param Simulation_Model_Scenario $scenario
     */
    public function clearDWResultsFromScenarioService($scenario)
    {
        $scenario->deleteDWResults();
    }

    /**
     * Peuple le cube de DW du set avec les résultats issues de l'inputSetPrimary.
     *
     * @param Simulation_Model_Scenario $scenario
     */
    public function populateDWResultsFromScenarioService($scenario)
    {
        $scenario->createDWResults();
    }

}