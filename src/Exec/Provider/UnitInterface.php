<?php

namespace Exec\Provider;

use Unit\UnitAPI;

/**
 * Interface à implémenter pour récupérer une valeur d'un Input.
 *
 * @author matthieu.napoli
 */
interface UnitInterface
{
    /**
     * Renvoi l'unité associée à la ref donnée.
     *
     * @param string $ref
     *
     * @return UnitAPI
     */
    public function getUnitForExecution($ref);
}
