<?php
/**
 * @author valentin.claras
 * @author yoann.croizer
 * @author hugo.charbonnier
 * @package Exec
 */

/**
 * Interface à implémenter pour récupérer une valeur d'un Input.
 *
 * @package    Exec
 */
interface Exec_Interface_ValueProvider
{
    /**
     * Vérifie la valeur associée à la ref donnée en vue de l'execution.
     *
     * @param string $ref
     *
     * @return array
     */
    public function checkValueForExecution($ref);

    /**
     * Renvoi la valeur associée à la ref donnée.
     *
     * @param string $ref
     *
     * @return mixed
     */
    public function getValueForExecution($ref);
}
