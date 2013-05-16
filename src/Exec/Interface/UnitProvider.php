<?php
/**
 * @author  matthieu.napoli
 * @package Exec
 */

/**
 * Interface à implémenter pour récupérer une valeur d'un Input.
 *
 * @package Exec
 */
interface Exec_Interface_UnitProvider
{

    /**
     * Renvoi l'unité associée à la ref donnée.
     *
     * @param string $ref
     *
     * @return Unit_API
     */
    public function getUnitForExecution($ref);

}
