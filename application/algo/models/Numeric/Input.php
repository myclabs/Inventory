<?php
/**
 * @author  matthieu.napoli
 * @author  hugo.charbonnier
 * @author  yoann.croizer
 * @package Algo
 */

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
     * @var Unit_API
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
        if (!$input) {
            throw new Core_Exception_NotFound("Il n'y a pas d'input avec le ref " . $this->inputRef);
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
        try {
            $unit->getNormalizedUnit();
        } catch (Core_Exception_NotFound $e) {
            $configError = new Algo_ConfigError();
            $configError->isFatal(true);
            $configError->setMessage("L'unité '" . $unit->getRef() . "' associée à l'algorithme '"
                                         . $this->ref . "' n'existe pas.");
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
     * @return Unit_API
     */
    public function getUnit()
    {
        return $this->unit;
    }

    /**
     * @param Unit_API $unit
     */
    public function setUnit(Unit_API $unit)
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
