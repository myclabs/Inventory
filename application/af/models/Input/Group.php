<?php
/**
 * @author     matthieu.napoli
 * @package    AF
 * @subpackage Input
 */

/**
 * Input Element for a group
 * @package    AF
 * @subpackage Input
 */
class AF_Model_Input_Group extends AF_Model_Input implements Algo_Model_Input
{
    /**
     * {@inheritdoc}
     */
    public function getNbRequiredFieldsCompleted()
    {
        return 0;
    }

    /**
     * {@inheritdoc}
     */
    public function hasValue()
    {
        return false;
    }
}
