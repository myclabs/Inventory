<?php
/**
 * @author     matthieu.napoli
 * @author     yoann.croizer
 * @author     hugo.charbonnier
 * @package    Algo
 * @subpackage Condition
 */

/**
 * @package    Algo
 * @subpackage Condition
 */
class Algo_Model_Condition_Elementary_Select_Single extends Algo_Model_Condition_Elementary_Select
{

    /**
     * {@inheritdoc}
     */
    public function execute(Algo_Model_InputSet $inputSet)
    {
        // On récupère l'input
        /** @var $input Algo_Model_Input_String */
        $input = $inputSet->getInputByRef($this->inputRef);
        if (!$input) {
            throw new Core_Exception_NotFound("Il n'y a pas d'input avec le ref " . $this->inputRef);
        }
        $value = $input->getValue();

        switch ($this->relation) {
            case Algo_Model_Condition_Elementary::RELATION_EQUAL:
                return ($value == $this->value);
            case Algo_Model_Condition_Elementary::RELATION_NOTEQUAL:
                return !($value == $this->value);
            default:
                throw new Core_Exception_InvalidArgument("Relation incorrecte, doit être RELATION_EQUAL"
                                                             . " ou RELATION_NOTEQUAL");
        }
    }

}
