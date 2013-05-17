<?php
/**
 * @author     matthieu.napoli
 * @package    Algo
 * @subpackage Condition
 */

/**
 * @package    Algo
 * @subpackage Condition
 */
abstract class Algo_Model_Condition_Elementary_Select extends Algo_Model_Condition_Elementary
{

    /**
     * Filtre sur la valeur
     */
    const QUERY_VALUE = 'value';

    /**
     * @var string
     */
    protected $value;


    /**
     * @return string
     */
    public function getValue()
    {
        return $this->value;
    }

    /**
     * @param string $value
     */
    public function setValue($value)
    {
        $this->value = $value;
    }

}
