<?php

namespace AF\Domain\Component;

use AF\Domain\InputSet\InputSet;
use AF\Domain\Input\CheckboxInput;

/**
 * @author matthieu.napoli
 * @author yoann.croizer
 */
class Checkbox extends Field
{
    /**
     * The default value indicate if the check box is checked or not.
     * @var bool
     */
    protected $defaultValue = false;

    /**
     * {@inheritdoc}
     */
    public function initializeNewInput(InputSet $inputSet)
    {
        $input = $inputSet->getInputForComponent($this);

        if ($input === null) {
            $input = new CheckboxInput($inputSet, $this);
            $inputSet->setInputForComponent($this, $input);
        }

        $input->setValue($this->defaultValue);
    }

    /**
     * {@inheritdoc}
     */
    public function getNbRequiredFields(InputSet $inputSet = null)
    {
        return 0;
    }

    /**
     * Get the defaultValue of the checkbox element.
     * @return bool
     */
    public function getDefaultValue()
    {
        return $this->defaultValue;
    }

    /**
     * Set the value, true for check, false else.
     * @param bool $defaultValue
     */
    public function setDefaultValue($defaultValue)
    {
        $this->defaultValue = (bool) $defaultValue;
    }
}
