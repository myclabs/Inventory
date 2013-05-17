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
     * Returns an input by its ref
     * @param string $ref
     * @return Algo_Model_Input|null
     */
    public function getInputByRef($ref);

}
