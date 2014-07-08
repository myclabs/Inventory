<?php

namespace AF\Domain\Input\Select;

use AF\Domain\Component\NumericField;
use AF\Domain\Input\Input;
use AF\Domain\Component\Select\SelectOption;
use AF\Domain\Algorithm\Input\StringInput;
use AF\Domain\Input\InputErrorField;
use AF\Domain\Input\InputErrorMessage;

/**
 * @author matthieu.napoli
 * @author yoann.croizer
 */
class SelectSingleInput extends Input implements StringInput, InputErrorField
{
    use InputErrorMessage;

    /**
     * Selected option's ref
     * @var string|null
     */
    protected $value;

    /**
     * @return string|null Option ref
     */
    public function getValue()
    {
        return $this->value;
    }

    /**
     * @param SelectOption|null $value
     */
    public function setValue(SelectOption $value = null)
    {
        if ($value) {
            $this->value = $value->getRef();
        } else {
            $this->value = null;
        }
    }

    /**
     * @param SelectSingleInput $input
     */
    public function setValueFrom(SelectSingleInput $input)
    {
        $this->value = $input->value;
    }

    /**
     * @return int Nombre de champs remplis dans le composant
     */
    public function getNbRequiredFieldsCompleted()
    {
        if (!$this->isHidden()) {
            /** @var $component NumericField */
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
     * {@inheritdoc}
     */
    public function equals(Input $input)
    {
        $equals = parent::equals($input);
        if (!$equals) {
            return false;
        }

        if ($input instanceof SelectSingleInput) {
            return $this->getValue() === $input->getValue();
        }

        return false;
    }
}
