<?php

namespace AF\Domain\AF\Input\Select;

use AF\Domain\AF\Component\NumericField;
use AF\Domain\AF\InputSet\InputSet;
use AF\Domain\AF\Input\Input;
use AF\Domain\AF\Component\Component;
use AF\Domain\AF\Component\Select\SelectOption;
use AF\Domain\Algorithm\Input\StringCollectionInput;
use Core_Exception_InvalidArgument;
use Doctrine\Common\Collections\Collection;
use Doctrine\Common\Collections\ArrayCollection;

/**
 * @author matthieu.napoli
 * @author yoann.croizer
 */
class SelectMultiInput extends Input implements StringCollectionInput
{
    /**
     * All selected options ref
     * @var string[]|Collection
     */
    protected $value;


    /**
     * @param InputSet  $inputSet
     * @param \AF\Domain\AF\Component\Component $component
     */
    public function __construct(InputSet $inputSet, Component $component)
    {
        parent::__construct($inputSet, $component);
        $this->value = new ArrayCollection();
    }

    /**
     * @return string[] Array of selected options ref
     */
    public function getValue()
    {
        return $this->value->toArray();
    }

    /**
     * @param SelectOption[] $value Array of selected options
     * @throws Core_Exception_InvalidArgument Value must be an array of SelectOption
     */
    public function setValue($value)
    {
        $this->value = new ArrayCollection();
        foreach ($value as $option) {
            if ($option instanceof  SelectOption) {
                $this->value->add($option->getRef());
            } else {
                throw new Core_Exception_InvalidArgument('Value must be an array of SelectOption');
            }
        }
    }

    /**
     * @param SelectMultiInput $input
     */
    public function setValueFrom(SelectMultiInput $input)
    {
        $this->value = new ArrayCollection();
        foreach ($input->value as $ref) {
            $this->value->add($ref);
        }
    }

    /**
     * @return int Nombre de champs remplis dans le composant
     */
    public function getNbRequiredFieldsCompleted()
    {
        if (!$this->isHidden()) {
            /** @var $component NumericField */
            $component = $this->getComponent();
            if ($component && $component->getRequired() && count($this->value) > 0) {
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
        return count($this->value);
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

        if ($input instanceof SelectMultiInput) {
            return $this->getValue() === $input->getValue();
        }

        return false;
    }
}
