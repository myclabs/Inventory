<?php

namespace AF\Domain\Algorithm\Numeric;

use AF\Domain\Algorithm\AlgoConfigurationError;
use AF\Domain\Algorithm\InputSet;
use Calc_UnitValue;
use Core_Exception_NotFound;
use Unit\UnitAPI;

/**
 * @author matthieu.napoli
 * @author hugo.charbonnier
 * @author yoann.croizer
 */
class NumericConstantAlgo extends NumericAlgo
{
    /**
     * @var Calc_UnitValue
     */
    protected $unitValue;

    public function __construct()
    {
        parent::__construct();
        $this->unitValue = new Calc_UnitValue();
    }

    /**
     * Exécution de l'algorithme
     * @param InputSet $inputSet
     * @return Calc_UnitValue
     */
    public function execute(InputSet $inputSet)
    {
        return $this->unitValue;
    }

    /**
     * Méthode utilisée au niveau de AF pour vérifier la configuration des algorithmes.
     * @return AlgoConfigurationError[]
     */
    public function checkConfig()
    {
        $errors = parent::checkConfig();
        // On vérifie que l'unité associée à cet algorithme existe bien.
        if (! $this->unitValue->getUnit()->exists()) {
            $configError = new AlgoConfigurationError();
            $configError->isFatal(true);
            $configError->setMessage("L'unité '" . $this->unitValue->getUnit()->getRef() . "' associée à l'algorithme '"
                . $this->ref . "', n'existe pas.");
            $errors[] = $configError;
        }
        // On vérifie que la valeur associée à cet algorithme existe bien
        if (! is_numeric($this->unitValue->getDigitalValue())) {
            $configError = new AlgoConfigurationError();
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
        return $this->unitValue->getUnit();
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
