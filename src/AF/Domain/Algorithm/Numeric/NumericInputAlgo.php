<?php

namespace AF\Domain\Algorithm\Numeric;

use AF\Domain\Algorithm\ConfigError;
use AF\Domain\Algorithm\Input\NumericInput;
use AF\Domain\Algorithm\InputSet;
use Calc_UnitValue;
use Core_Exception_NotFound;
use Unit\UnitAPI;

/**
 * @author matthieu.napoli
 * @author hugo.charbonnier
 * @author yoann.croizer
 */
class NumericInputAlgo extends NumericAlgo
{
    /**
     * ref de l'input numérique associé
     * @var string
     */
    protected $inputRef;

    /**
     * L'unité associée au champs numérique.
     * @var UnitAPI
     */
    protected $unit;

    /**
     * Exécution de l'algorithme
     * @param InputSet $inputSet
     * @return Calc_UnitValue
     */
    public function execute(InputSet $inputSet)
    {
        /** @var $input NumericInput */
        $input = $inputSet->getInputByRef($this->inputRef);
        if (!$input) {
            return new Calc_UnitValue($this->unit, 0, 0);
        }
        return $input->getValue();
    }

    /**
     * Méthode utilisée au niveau de AF pour vérifier la configuration des algorithmes.
     * @return ConfigError[]
     */
    public function checkConfig()
    {
        $errors = parent::checkConfig();
        $unit = $this->getUnit();
        try {
            $unit->getNormalizedUnit();
        } catch (Core_Exception_NotFound $e) {
            $configError = new ConfigError();
            $configError->isFatal(true);
            $configError->setMessage("L'unité '" . $unit->getRef() . "' associée à l'algorithme '"
                . $this->ref . "' n'existe pas.");
            $errors[] = $configError;
        }
        if ((!isset($this->inputRef)) || ($this->inputRef === '')) {
            $configError = new ConfigError();
            $configError->isFatal(true);
            $configError->setMessage("L'algorithme '" . $this->ref . "' n'est associé à aucun champs.");
            $errors[] = $configError;
        }
        return $errors;
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
