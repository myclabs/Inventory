<?php
/**
 * @author  matthieu.napoli
 * @author  hugo.charbonnier
 * @author  yoann.croizer
 * @package Algo
 */

/**
 * @package    Algo
 * @subpackage Keyword
 */
class Algo_Model_Selection_TextKey_Input extends Algo_Model_Selection_TextKey
{

    /**
     * @var string
     */
    protected $inputRef;


    /**
     * @param string $ref
     * @throws Core_Exception_InvalidArgument
     */
    public function setRef($ref)
    {
        parent::setRef($ref);
        if ($this->inputRef == null) {
            $this->inputRef = $ref;
        }
    }

    /**
     * Execute
     * @param Algo_Model_InputSet $inputSet
     * @throws Core_Exception_NotFound
     * @throws Core_Exception_InvalidArgument
     * @return string
     */
    public function execute(Algo_Model_InputSet $inputSet)
    {
        /** @var $input Algo_Model_Input_String */
        $input = $inputSet->getInputByRef($this->inputRef);
        if (!$input) {
            throw new Core_Exception_NotFound("Il n'y a pas d'input avec le ref " . $this->inputRef);
        }
        return $input->getValue();
    }

    /**
     * @return string
     */
    public function getInputRef()
    {
        return $this->inputRef;
    }

    /**
     * @param string $inputRef
     */
    public function setInputRef($inputRef)
    {
        $this->inputRef = $inputRef;
    }

}
