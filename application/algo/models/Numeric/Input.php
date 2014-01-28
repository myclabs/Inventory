<?php
/**
 * @author  matthieu.napoli
 * @author  hugo.charbonnier
 * @author  yoann.croizer
 * @package Algo
 */
use Unit\UnitAPI;

/**
 * @package    Algo
 * @subpackage Numeric
 */
class Algo_Model_Numeric_Input extends Algo_Model_Numeric
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
     * @param Algo_Model_InputSet $inputSet
     * @return Calc_UnitValue
     */
    public function execute(Algo_Model_InputSet $inputSet)
    {
        /** @var $input Algo_Model_Input_Numeric */
        $input = $inputSet->getInputByRef($this->inputRef);
        if (! $input) {
            return new Calc_UnitValue($this->unit, 0, 0);
        }
        return $input->getValue();
    }

    /**
     * Méthode utilisée au niveau de AF pour vérifier la configuration des algorithmes.
     * @return Algo_ConfigError[]
     */
    public function checkConfig()
    {
        $errors = parent::checkConfig();
        $unit = $this->getUnit();
        if (! $unit->exists()) {
            $configError = new Algo_ConfigError();
            $configError->isFatal(true);
            $configError->setMessage(
                "L'unité '" . $unit->getRef() . "' associée à l'algorithme '" . $this->ref . "' n'existe pas."
            );
            $errors[] = $configError;
        }
        if ((!isset($this->inputRef)) || ($this->inputRef === '')) {
            $configError = new Algo_ConfigError();
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
