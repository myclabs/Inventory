<?php

namespace AF\Domain\Algorithm\Index;

use AF\Domain\Algorithm\InputSet;
use AF\Domain\Algorithm\Numeric\NumericAlgo;
use Classification\Domain\IndicatorAxis;
use Classification\Domain\AxisMember;
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
     * The classification axis
     * @var string|null
     */
    protected $refClassifAxis;

    /**
     * @var NumericAlgo|null
     */
    protected $algoNumeric;


    /**
     * @param \Classification\Domain\IndicatorAxis|null $classifAxis
     * @param NumericAlgo|null        $algoNumeric
     */
    public function __construct(IndicatorAxis $classifAxis = null, NumericAlgo $algoNumeric = null)
    {
        if ($classifAxis) {
            $this->refClassifAxis = $classifAxis->getRef();
        }
        $this->algoNumeric = $algoNumeric;
    }

    /**
     * Return the Classification member associated with the Result index
     * @param InputSet $inputSet
     * @return AxisMember|null
     */
    abstract public function getClassifMember(InputSet $inputSet = null);

    /**
     * @return \Classification\Domain\IndicatorAxis|null The classification axis associated to the value index
     */
    public function getClassifAxis()
    {
        if ($this->refClassifAxis === null) {
            return null;
        }
        try {
            return IndicatorAxis::loadByRef($this->refClassifAxis);
        } catch (Core_Exception_NotFound $e) {
            return null;
        }
    }

    /**
     * @param IndicatorAxis $classifAxis
     */
    public function setClassifAxis(IndicatorAxis $classifAxis)
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
