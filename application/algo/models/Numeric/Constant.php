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
class Algo_Model_Numeric_Constant extends Algo_Model_Numeric
{

    /**
     * @var Calc_UnitValue
     */
    protected $unitValue;


    /**
     * Constructeur
     */
    public function __construct()
    {
        parent::__construct();
        $this->unitValue = new Calc_UnitValue();
    }

    /**
     * Exécution de l'algorithme
     * @param Algo_Model_InputSet $inputSet
     * @return Calc_UnitValue
     */
    public function execute(Algo_Model_InputSet $inputSet)
    {
        return $this->unitValue;
    }

    /**
     * Méthode utilisée au niveau de AF pour vérifier la configuration des algorithmes.
     * @return Algo_ConfigError[]
     */
    public function checkConfig()
    {
        $errors = parent::checkConfig();
        // On vérifie que l'unité associée à cet algorithme existe bien.
        try {
            $this->unitValue->unit->getNormalizedUnit();
        } catch (Core_Exception_NotFound $e) {
            $configError = new Algo_ConfigError();
            $configError->isFatal(true);
            $configError->setMessage("L'unité '" . $this->unitValue->unit->getRef() . "' associée à l'algorithme '"
                                         . $this->ref . "', n'existe pas.");
            $errors[] = $configError;
        }
        // On vérifie que la valeur associée à cet algorithme existe bien
        if (!isset($this->unitValue->value)) {
            $configError = new Algo_ConfigError();
            $configError->isFatal(true);
            $configError->setMessage("Aucune valeur n'est associée à l'algorithme '" . $this->ref);
            $errors[] = $configError;
        }
        return $errors;
    }

    /**
     * Méthode permettant de récupérer l'unité associée à un algorithme.
     * Cette méthode est en particulier utilisée lors du controle de la configuration des algos.
     *
     * @return UnitAPI
     */
    public function getUnit()
    {
        return $this->unitValue->unit;
    }

    /**
     * @param Calc_UnitValue $unitValue
     */
    public function setUnitValue(Calc_UnitValue $unitValue)
    {
        $this->unitValue = $unitValue;
    }

    /**
     * @return Calc_UnitValue
     */
    public function getUnitValue()
    {
        return $this->unitValue;
    }

}
