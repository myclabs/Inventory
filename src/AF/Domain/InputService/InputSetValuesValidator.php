<?php

namespace AF\Domain\InputService;

use AF\Domain\Component\Component;
use AF\Domain\Input\InputErrorField;
use AF\Domain\Input\TextFieldInput;
use AF\Domain\Input\NumericFieldInput;
use AF\Domain\Input\CheckboxInput;
use AF\Domain\Input\Select\SelectSingleInput;
use AF\Domain\Input\Select\SelectMultiInput;
use AF\Domain\Input\SubAF\RepeatedSubAFInput;
use AF\Domain\Input\SubAF\NotRepeatedSubAFInput;
use AF\Domain\InputSet\InputSet;
use AF\Domain\Input\Input;

/**
 * Helper vérifiant la bonne saisie des valeurs d'un InputSet.
 *
 * @author valentin.claras
 * @author matthieu.napoli
 */
class InputSetValuesValidator
{
    /**
     * @var InputSet
     */
    private $inputSet;

    /**
     * @param InputSet $inputSet  InputSet à valider
     */
    public function __construct(InputSet $inputSet)
    {
        $this->inputSet = $inputSet;
    }

    /**
     * Lance la validation de l'InputSet.
     */
    public function validate()
    {
        foreach ($this->inputSet->getInputs() as $input) {
            $this->validateInput($input);
        }
    }

    /**
     * Compares 2 items and returns if there are differences
     * @param Input $input
     * @return boolean
     */
    protected function validateInput(Input $input)
    {
        switch (true) {
            // SubAF non répété
            case $input instanceof NotRepeatedSubAFInput:
                /** @var NotRepeatedSubAFInput $input */
                $this->validateNotRepeatedSubAFInput($input);
                break;
            // SubAF répété
            case $input instanceof RepeatedSubAFInput:
                /** @var RepeatedSubAFInput $input */
                $this->validateRepeatedSubAFInput($input);
                break;
            case $input instanceof NumericFieldInput:
                /** @var NumericFieldInput $input */
                $this->validateNumericFieldInput($input);
                break;
            case $input instanceof SelectSingleInput:
            case $input instanceof SelectMultiInput:
            case $input instanceof CheckboxInput:
            case $input instanceof TextFieldInput:
                /** @var InputErrorField $input */
                $this->validateValueInput($input);
                break;
        }
    }

    /**
     * @param NotRepeatedSubAFInput $input
     */
    protected function validateNotRepeatedSubAFInput(NotRepeatedSubAFInput $input)
    {
        // Lance une mise à jour du sous-inputSet
        $subUpdater = new InputSetValuesValidator($input->getValue());
        $subUpdater->validate();
    }

    /**
     * @param RepeatedSubAFInput $input
     */
    protected function validateRepeatedSubAFInput(RepeatedSubAFInput $input)
    {
        foreach ($input->getValue() as $value) {
            // Lance une comparaison des listes de sous-InputSet
            $comparator = new InputSetValuesValidator($value);
            $comparator->validate();
        }
    }

    /**
     * @param NumericFieldInput $input
     */
    protected function validateNumericFieldInput(NumericFieldInput $input)
    {
        if ($input->isHidden()) {
            $input->setError();
            return;
        }

        $value = null;
        $uncertainty = '';
        if ($input->getValue() !== null) {
            $value = $input->getValue()->getDigitalValue();
            $uncertainty = $input->getValue()->getUncertainty();
        }
        if ((!$input->getComponent()->getRequired()) && ($value === null)) {
            return;
        } else if ($input->getComponent()->getRequired() && ($value === null)) {
            $input->setError(__('AF', 'inputInput', 'emptyRequiredField'));
        } else if (!preg_match('#^-?[0-9]*[.,]?[0-9]*$#', $value)) {
            $input->setError(__('UI', 'formValidation', 'invalidNumber'));
        } else if (!preg_match('#^[0-9]*$#', $uncertainty)) {
            $input->setError(__('UI', 'formValidation', 'invalidUncertainty'));
        }
    }

    /**
     * @param InputErrorField $input
     */
    protected function validateValueInput(InputErrorField $input)
    {
        if ($input->isHidden()) {
            $input->setError();
            return;
        }

        /** @var Component $component */
        $component = $input->getComponent();
        if ($component->getNbRequiredFields() > 0 && !$component->getRequired()
            && (($input->getValue() === null) || ($input->getValue() === []))) {
            $input->setError(__('AF', 'inputInput', 'emptyRequiredField'));
        } else if (($input->getValue() === null) || ($input->getValue() === [])) {
            $input->setError(__('AF', 'inputInput', 'emptyRequiredField'));
        }
    }
}
