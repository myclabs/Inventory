<?php
/**
 * @author  matthieu.napoli
 * @author  cyril.perraud
 * @package Algo
 */
use Keyword\Domain\Keyword;
use Techno\Application\Service\Techno_Service_Techno;
use Techno\Domain\Family\Dimension;

/**
 * Classe qui permet de récupérer les coordonnées d'un élément d'une famille
 * de techno à l'aide d'une liste de keyword et d'une ref de famille.
 * @package Algo
 */
abstract class Algo_Model_ParameterCoordinate extends Core_Model_Entity
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
     * @param Algo_Model_InputSet|null $inputSet
     * @return Keyword
     */
    public abstract function getMemberKeyword(Algo_Model_InputSet $inputSet = null);

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
            /** @var \DI\Container $container */
            $container = Zend_Registry::get('container');
            /** @var Techno_Service_Techno $technoService */
            $technoService = $container->get('Techno_Service_Techno');
            $meaning = $technoService->getMeaning($this->refDimensionMeaning);
            $this->dimension = $this->getAlgoParameter()->getFamily()->getDimensionByMeaning($meaning);
        }
        return $this->dimension;
    }

    /**
     * @param Dimension $dimension
     */
    public function setDimension(Dimension $dimension)
    {
        $this->refDimensionMeaning = $dimension->getMeaning()->getKeyword()->getRef();
        $this->dimension = $dimension;
    }

    /**
     * @return string
     */
    public function getDimensionRefMeaning()
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
     * @return Algo_ConfigError[]
     */
    public function checkConfiguration()
    {
        return [];
    }

}
