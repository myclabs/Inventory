<?php

namespace AF\Domain\Algorithm\Selection\TextKey;

use AF\Domain\Algorithm\Input\StringInput;
use AF\Domain\Algorithm\InputSet;
use AF\Domain\Algorithm\Selection\TextKeySelectionAlgo;
use Core_Exception_InvalidArgument;
use Core_Exception_NotFound;

/**
 * @author matthieu.napoli
 * @author hugo.charbonnier
 * @author yoann.croizer
 */
class InputSelectionAlgo extends TextKeySelectionAlgo
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
     * @param InputSet $inputSet
     * @throws Core_Exception_NotFound
     * @throws Core_Exception_InvalidArgument
     * @return string
     */
    public function execute(InputSet $inputSet)
    {
        /** @var $input StringInput */
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
