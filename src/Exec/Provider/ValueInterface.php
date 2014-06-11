<?php

namespace Exec\Provider;

/**
 * Interface à implémenter pour récupérer une valeur d'un Input.
 *
 * @author valentin.claras
 */
interface ValueInterface
{
    const RESULT_BOOL = 1;
    const RESULT_STRING = 2;
    const RESULT_NUMERIC = 3;

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
     * @param int    $expectedResult
     *
     * @return mixed
     */
    public function getValueForExecution($ref, $expectedResult);
}
