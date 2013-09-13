<?php
/**
 * @author  matthieu.napoli
 * @package Algo
 */

/**
 * @package Algo
 */
interface Algo_Model_InputSet
{

    /**
     * Retourne la saisie d'un élément à partir de son ref
     * @param string $ref
     * @return Algo_Model_Input|null
     */
    public function getInputByRef($ref);

    /**
     * Retourne une valeur définie par le contexte à partir de son nom
     * @param string $name
     * @return mixed|null
     */
    public function getContextValue($name);

}
