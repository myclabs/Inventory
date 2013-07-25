<?php
/**
 * @author     matthieu.napoli
 * @package    Exec
 * @subpackage Provider
 */
namespace Exec\Provider;

use Unit\UnitAPI;

/**
 * Interface à implémenter pour récupérer une valeur d'un Input.
 * @package    Exec
 * @subpackage Provider
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
