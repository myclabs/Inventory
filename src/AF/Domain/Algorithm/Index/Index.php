<?php

namespace AF\Domain\Algorithm\Index;

use AF\Domain\Algorithm\InputSet;
use AF\Domain\Algorithm\Numeric\NumericAlgo;
use Classif_Model_Axis;
use Classif_Model_Member;
use Core_Exception_NotFound;
use Core_Model_Entity;

/**
 * @author matthieu.napoli
 * @author cyril.perraud
 * @author yoann.croizer
 */
abstract class Index extends Core_Model_Entity
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
     * @var NumericAlgo|null
     */
    protected $algoNumeric;


    /**
     * @param Classif_Model_Axis|null $classifAxis
     * @param NumericAlgo|null        $algoNumeric
     */
    public function __construct(Classif_Model_Axis $classifAxis = null, NumericAlgo $algoNumeric = null)
    {
        if ($classifAxis) {
            $this->refClassifAxis = $classifAxis->getRef();
        }
        $this->algoNumeric = $algoNumeric;
    }

    /**
     * Return the Classif member associated with the Result index
     * @param InputSet $inputSet
     * @return Classif_Model_Member|null
     */
    abstract public function getClassifMember(InputSet $inputSet = null);

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
     * @return NumericAlgo|null The algo numeric associated to the value index
     */
    public function getAlgoNumeric()
    {
        return $this->algoNumeric;
    }

    /**
     * @param NumericAlgo $algoNumeric
     */
    public function setAlgoNumeric(NumericAlgo $algoNumeric)
    {
        if ($algoNumeric != $this->algoNumeric) {
            $this->algoNumeric = $algoNumeric;
            $algoNumeric->addIndex($this);
        }
    }
}
