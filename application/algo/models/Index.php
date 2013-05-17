<?php
/**
 * @author  matthieu.napoli
 * @author  cyril.perraud
 * @author  yoann.croizer
 * @package Algo
 */

/**
 * @package Algo
 */
abstract class Algo_Model_Index extends Core_Model_Entity
{

    /**
     * @var int
     */
    protected $id;

    /**
     * The classif axis
     * @var string|null
     */
    protected $refClassifAxis;

    /**
     * @var Algo_Model_Numeric|null
     */
    protected $algoNumeric;


    /**
     * @param Classif_Model_Axis|null $classifAxis
     * @param Algo_Model_Numeric|null $algoNumeric
     */
    public function __construct(Classif_Model_Axis $classifAxis = null, Algo_Model_Numeric $algoNumeric = null)
    {
        if ($classifAxis) {
            $this->refClassifAxis = $classifAxis->getRef();
        }
        $this->algoNumeric = $algoNumeric;
    }

    /**
     * Return the Classif member associated with the Result index
     * @param Algo_Model_InputSet $inputSet
     * @return Classif_Model_Member|null
     */
    public abstract function getClassifMember(Algo_Model_InputSet $inputSet = null);

    /**
     * @return Classif_Model_Axis|null The classif axis associated to the value index
     */
    public function getClassifAxis()
    {
        if ($this->refClassifAxis === null) {
            return null;
        }
        try {
            return Classif_Model_Axis::loadByRef($this->refClassifAxis);
        } catch (Core_Exception_NotFound $e) {
            return null;
        }
    }

    /**
     * @param Classif_Model_Axis $classifAxis
     */
    public function setClassifAxis(Classif_Model_Axis $classifAxis)
    {
        $this->refClassifAxis = $classifAxis->getRef();
    }

    /**
     * @return Algo_Model_Numeric|null The algo numeric associated to the value index
     */
    public function getAlgoNumeric()
    {
        return $this->algoNumeric;
    }

    /**
     * @param Algo_Model_Numeric $algoNumeric
     */
    public function setAlgoNumeric(Algo_Model_Numeric $algoNumeric)
    {
        if ($algoNumeric != $this->algoNumeric) {
            $this->algoNumeric = $algoNumeric;
            $algoNumeric->addIndex($this);
        }
    }

}
