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
class Algo_Model_Condition_Elementary_Select_Multi extends Algo_Model_Condition_Elementary_Select
{

    /**
     * {@inheritdoc}
     */
    public function execute(Algo_Model_InputSet $inputSet)
    {
        // On récupère l'input
        /** @var $input Algo_Model_Input_StringCollection */
        $input = $inputSet->getInputByRef($this->inputRef);
        if (!$input) {
            throw new Core_Exception_NotFound("Il n'y a pas d'input avec le ref " . $this->inputRef);
        }
        $value = $input->getValue();
        switch ($this->relation) {
            case Algo_Model_Condition_Elementary::RELATION_CONTAINS:
                return in_array($this->value, $value);
            case Algo_Model_Condition_Elementary::RELATION_NOTCONTAINS:
                return !in_array($this->value, $value);
            default:
                throw new Core_Exception_InvalidArgument("Relation incorrecte, doit être RELATION_CONTAINS"
                                                             . " ou RELATION_NOTCONTAINS");
        }
    }

}
