<?php

namespace AccountingForm\Domain\Processing;

use AccountingForm\Domain\ArrayValueSet;
use AccountingForm\Domain\Value\NumericValue;
use AccountingForm\Domain\ValueSet;
use AF\Domain\Algorithm\AlgoConfigurationError;

/**
 * Defines a constant.
 *
 * @author matthieu.napoli
 */
class ConstantDefinition implements ProcessingStep
{
    /**
     * @var string
     */
    protected $keyName;

    /**
     * @var NumericValue
     */
    private $value;

    public function __construct($keyName, NumericValue $value)
    {
        $this->keyName = $keyName;
        $this->value = $value;
    }

    /**
     * {@inheritdoc}
     */
    public function execute(ValueSet $input)
    {
        $output = new ArrayValueSet();

        $output->set($this->keyName, $this->value);

        return $output;
    }

    /**
     * {@inheritdoc}
     */
    public function validate()
    {
        $errors = [];

        // On vérifie que l'unité associée à cet algorithme existe bien.
        if (! $this->value->getUnit()->exists()) {
            $errors[] = new AlgoConfigurationError(
                sprintf("L'unité '%s' n'existe pas", $this->value->getUnit()->getRef())
            );
        }

        // On vérifie que la valeur associée à cet algorithme existe bien
        if (! is_numeric($this->value->getNumericValue())) {
            $errors[] = new AlgoConfigurationError("Aucune valeur numérique n'a été définie");
        }

        return $errors;
    }

    /**
     * @return string
     */
    public function getKeyName()
    {
        return $this->keyName;
    }

    /**
     * @param string $keyName
     */
    public function setKeyName($keyName)
    {
        $this->keyName = (string) $keyName;
    }

    /**
     * @return NumericValue
     */
    public function getValue()
    {
        return $this->value;
    }

    /**
     * @param NumericValue $value
     */
    public function setValue(NumericValue $value)
    {
        $this->value = $value;
    }
}
