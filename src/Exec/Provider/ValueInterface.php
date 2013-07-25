<?php
/**
 * @author     valentin.claras
 * @package    Exec
 * @subpackage Provider
 */

namespace Exec\Provider;

/**
 * Interface à implémenter pour récupérer une valeur d'un Input.
 *
 * @package    Exec
 * @subpackage Provider
 */
interface ValueInterface
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
