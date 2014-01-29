<?php

namespace AF\Domain\AF\Input;

use AF\Domain\AF\Component\TextField;
use AF\Domain\AF\InputSet\InputSet;
use AF\Domain\AF\Component;
use AF\Domain\Algorithm\Input\NumericInput;

/**
 * Input Element for text fields.
 *
 * @author matthieu.napoli
 * @author yoann.croizer
 */
class TextFieldInput extends Input implements NumericInput
{
    /**
     * @var string
     */
    protected $value;

    /**
     * @param InputSet  $inputSet
     * @param Component $component
     */
    public function __construct(InputSet $inputSet, Component $component)
    {
        parent::__construct($inputSet, $component);
    }

    /**
     * @return int Nombre de champs remplis dans le composant
     */
    public function getNbRequiredFieldsCompleted()
    {
        if (!$this->isHidden()) {
            /** @var $component TextField */
            $component = $this->getComponent();
            if ($component && $component->getRequired() && $this->value != null) {
                return 1;
            }
        }
        return 0;
    }

    /**
     * {@inheritdoc}
     */
    public function hasValue()
    {
        return $this->value != null;
    }

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
        $this->value = trim($value);
    }

    /**
     * {@inheritdoc}
     */
    public function equals(Input $input)
    {
        $equals = parent::equals($input);
        if (!$equals) {
            return false;
        }

        if ($input instanceof TextFieldInput) {
            return $this->getValue() == $input->getValue();
        }

        return false;
    }
}
