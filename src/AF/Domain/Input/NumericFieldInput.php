<?php

namespace AF\Domain\Input;

use AF\Domain\Component\NumericField;
use AF\Domain\InputSet\InputSet;
use AF\Domain\Component\Component;
use AF\Domain\Algorithm\Input\NumericInput;
use Calc_UnitValue;

/**
 * Input for numerics fields.
 *
 * @author matthieu.napoli
 * @author yoann.croizer
 */
class NumericFieldInput extends Input implements NumericInput
{
    /**
     * @var Calc_UnitValue
     */
    protected $value;

    /**
     * @param \AF\Domain\InputSet\InputSet  $inputSet
     * @param Component $component
     */
    public function __construct(InputSet $inputSet, Component $component)
    {
        parent::__construct($inputSet, $component);
        $this->value = new Calc_UnitValue();
    }

    /**
     * @return int Nombre de champs remplis dans le composant
     */
    public function getNbRequiredFieldsCompleted()
    {
        if (!$this->isHidden()) {
            /** @var $component NumericField */
            $component = $this->getComponent();
            if ($component && $component->getRequired() && $this->value->getDigitalValue() !== null) {
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
        return $this->value->getDigitalValue() !== null;
    }

    /**
     * @return Calc_UnitValue
     */
    public function getValue()
    {
        return $this->value;
    }

    /**
     * @param Calc_UnitValue $value
     */
    public function setValue(Calc_UnitValue $value)
    {
        $this->value = $value;
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

        if ($input instanceof NumericFieldInput) {
            return $this->getValue()->equals($input->getValue());
        }

        return false;
    }
}
