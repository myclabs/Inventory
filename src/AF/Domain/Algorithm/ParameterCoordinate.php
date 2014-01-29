<?php

namespace AF\Domain\Algorithm;

use AF\Domain\Algorithm\ConfigError;
use Algo_Model_Numeric_Parameter;
use Core_Model_Entity;
use Techno\Domain\Family\Dimension;

/**
 * Classe qui permet de récupérer les coordonnées d'un élément d'une famille
 * de techno à l'aide d'une liste de keyword et d'une ref de famille.
 *
 * @author matthieu.napoli
 * @author cyril.perraud
 */
abstract class ParameterCoordinate extends Core_Model_Entity
{
    /**
     * @var int
     */
    protected $id;

    /**
     * Inverse side of the relationship
     * @var Algo_Model_Numeric_Parameter
     */
    protected $algoParameter;

    /**
     * @var string
     */
    protected $refDimensionMeaning;

    /**
     * Lazy loading @see $refDimensionMeaning
     * @var Dimension
     */
    protected $dimension;


    /**
     * Renvoie le membre de famille associé au parameterCoordinate
     * @param InputSet|null $inputSet
     * @return string
     */
    abstract public function getMember(InputSet $inputSet = null);

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @return Dimension
     */
    public function getDimension()
    {
        if (!$this->dimension) {
            $this->dimension = $this->getAlgoParameter()->getFamily()->getDimension($this->refDimensionMeaning);
        }
        return $this->dimension;
    }

    /**
     * @param string $dimensionRef
     */
    public function setDimensionRef($dimensionRef)
    {
        $this->refDimensionMeaning = $dimensionRef;
        $this->dimension = null;
    }

    /**
     * @return string
     */
    public function getDimensionRef()
    {
        return $this->refDimensionMeaning;
    }

    /**
     * @return Algo_Model_Numeric_Parameter
     */
    public function getAlgoParameter()
    {
        return $this->algoParameter;
    }

    /**
     * @param Algo_Model_Numeric_Parameter $algoParameter
     */
    public function setAlgoParameter(Algo_Model_Numeric_Parameter $algoParameter)
    {
        if ($this->algoParameter !== $algoParameter) {
            $this->algoParameter = $algoParameter;
            $algoParameter->addParameterCoordinates($this);
        }
    }

    /**
     * @return ConfigError[]
     */
    public function checkConfiguration()
    {
        return [];
    }
}
