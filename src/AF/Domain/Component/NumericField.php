<?php

namespace AF\Domain\Component;

use AF\Domain\InputSet\InputSet;
use AF\Domain\AFConfigurationError;
use AF\Domain\Input\NumericFieldInput;
use AF\Domain\Algorithm\Numeric\NumericInputAlgo;
use Calc_UnitValue;
use Calc_Value;
use Core_Exception_NotFound;
use Unit\UnitAPI;

/**
 * Gestion des champs de type Numeric (champs de saisie).
 *
 * @author matthieu.napoli
 * @author hugo.charbonnier
 * @author thibaud.rolland
 */
class NumericField extends Field
{
    /**
     * L'unité associée à la valeur numérique.
     * @var UnitAPI
     */
    protected $unit;

    /**
     * Est-ce que l'utilisateur peut choisir l'unité.
     * @var bool
     */
    protected $unitSelection = true;

    /**
     * Flag indiquant si le champs de saisie doit être associé à un champs de saisie d'incertitude.
     * @var boolean
     */
    protected $withUncertainty = true;

    /**
     * Valeur par défaut du champ.
     * @var Calc_Value
     */
    protected $defaultValue;

    /**
     * Indique si on doit afficher ou non un rappel de la valeur par défaut.
     * @var bool
     */
    protected $defaultValueReminder = false;

    /**
     * Indique si le champ est requis ou non.
     * @var bool
     */
    protected $required = true;


    public function __construct()
    {
        parent::__construct();
        $this->defaultValue = new Calc_Value();
    }

    /**
     * {@inheritdoc}
     */
    public function initializeNewInput(InputSet $inputSet)
    {
        $input = $inputSet->getInputForComponent($this);

        if ($input === null) {
            $input = new NumericFieldInput($inputSet, $this);
            $inputSet->setInputForComponent($this, $input);
        }

        $defaultValue = new Calc_UnitValue(
            $this->unit,
            $this->defaultValue->getDigitalValue(),
            $this->defaultValue->getUncertainty()
        );

        $input->setValue($defaultValue);
    }

    /**
     * {@inheritdoc}
     */
    public function checkConfig()
    {
        $errors = parent::checkConfig();
        // On vérifie que l'unité associée au champs numérique est valide.
        if (! $this->unit->exists()) {
            $errors[] = new AFConfigurationError(
                "L'unité '{$this->unit->getRef()}' associée au champ '$this->ref' n'existe pas.",
                true,
                $this->getAf()
            );
        }
        return $errors;
    }

    /**
     * {@inheritdoc}
     */
    public function getNbRequiredFields(InputSet $inputSet = null)
    {
        if (!$this->getRequired()) {
            return 0;
        }

        if ($inputSet) {
            $input = $inputSet->getInputForComponent($this);
            // Si la saisie est cachée : 0 champs requis
            if ($input && $input->isHidden()) {
                return 0;
            }
        }

        return 1;
    }

    /**
     * {@inheritdoc}
     */
    public function setRef($ref)
    {
        $oldRef = $this->ref;
        parent::setRef($ref);
        // Modifie également le ref de l'algo associé et l'association entre eux
        try {
            $af = $this->getAf();
            if ($af) {
                $algo = $af->getAlgoByRef($oldRef);
                if ($algo instanceof NumericInputAlgo) {
                    $algo->setInputRef($ref);
                    $algo->setRef($ref);
                    $algo->save();
                }
            }
        } catch (Core_Exception_NotFound $e) {
        }
    }

    /**
     * @return UnitAPI
     */
    public function getUnit()
    {
        return $this->unit;
    }

    /**
     * @param UnitAPI $unit
     */
    public function setUnit(UnitAPI $unit)
    {
        $this->unit = $unit;
        // Modifie également l'unité de l'algo associé et l'association entre eux
        try {
            $af = $this->getAf();
            if ($af) {
                $algo = $af->getAlgoByRef($this->getRef());
                if ($algo instanceof NumericInputAlgo) {
                    $algo->setUnit($unit);
                    $algo->save();
                }
            }
        } catch (Core_Exception_NotFound $e) {
        }
    }

    /**
     * @return bool
     */
    public function getWithUncertainty()
    {
        return $this->withUncertainty;
    }

    /**
     * @param bool $withUncertainty
     */
    public function setWithUncertainty($withUncertainty)
    {
        $this->withUncertainty = (bool) $withUncertainty;
    }

    /**
     * @param Calc_Value $value
     */
    public function setDefaultValue(Calc_Value $value)
    {
        $this->defaultValue = $value;
    }

    /**
     * @return Calc_Value
     */
    public function getDefaultValue()
    {
        return $this->defaultValue;
    }

    /**
     * Get the defaultValueReminder attribute.
     * @return bool
     */
    public function getDefaultValueReminder()
    {
        return $this->defaultValueReminder;
    }

    /**
     * @param bool $defaultValueReminder
     */
    public function setDefaultValueReminder($defaultValueReminder)
    {
        $this->defaultValueReminder = (bool) $defaultValueReminder;
    }

    /**
     * @return bool
     */
    public function getRequired()
    {
        return $this->required;
    }

    /**
     * @param bool $required
     */
    public function setRequired($required)
    {
        $this->required = (bool) $required;
    }

    /**
     * @return boolean
     */
    public function hasUnitSelection()
    {
        return is_null($this->unitSelection) ? true : $this->unitSelection;
    }

    /**
     * @param boolean $unitChoice
     */
    public function setUnitSelection($unitChoice)
    {
        $this->unitSelection = (boolean) $unitChoice;
    }
}
