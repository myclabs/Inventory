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
class Algo_Model_Condition_Elementary_Boolean extends Algo_Model_Condition_Elementary
{

    /**
     * {@inheritdoc}
     * Valeur par défaut : égal
     */
    protected $relation = self::RELATION_EQUAL;

    /**
     * @var bool
     */
    protected $value;

    /**
     * Execute
     * @param Algo_Model_InputSet $inputSet
     * @return bool
     */
    public function execute(Algo_Model_InputSet $inputSet)
    {
        // On récupère l'input
        /** @var $input Algo_Model_Input_Boolean */
        $input = $inputSet->getInputByRef($this->inputRef);
        if (!$input) {
            throw new Core_Exception_NotFound("Il n'y a pas d'input avec le ref " . $this->inputRef);
        }
        $value = $input->getValue();

        // On effectue la comparaison en fonction de la relation
        $relation = $this->relation;
        switch ($relation) {
            case Algo_Model_Condition_Elementary::RELATION_EQUAL:
                return $value === (bool) $this->value;
            case Algo_Model_Condition_Elementary::RELATION_NOTEQUAL:
                return $value !== (bool) $this->value;
            default:
                throw new Core_Exception_InvalidArgument("Relation non gérée");
        }
    }

    /**
     * @return boolean
     */
    public function getValue()
    {
        return $this->value;
    }

    /**
     * @param boolean $value
     */
    public function setValue($value)
    {
        $this->value = $value;
    }

}
