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
    protected $refClassificationAxis;

    /**
     * @var NumericAlgo|null
     */
    protected $algoNumeric;


    /**
     * @param IndicatorAxis|null $classificationAxis
     * @param NumericAlgo|null   $algoNumeric
     */
    public function __construct(IndicatorAxis $classificationAxis = null, NumericAlgo $algoNumeric = null)
    {
        if ($classificationAxis) {
            $this->refClassificationAxis = $classificationAxis->getRef();
        }
        $this->algoNumeric = $algoNumeric;
    }

    /**
     * Return the Classification member associated with the Result index
     * @param InputSet $inputSet
     * @return AxisMember|null
     */
    abstract public function getClassificationMember(InputSet $inputSet = null);

    /**
     * @return IndicatorAxis|null The classification axis associated to the value index
     */
    public function getClassificationAxis()
    {
        if ($this->refClassificationAxis === null) {
            return null;
        }
        try {
            return IndicatorAxis::loadByRef($this->refClassificationAxis);
        } catch (Core_Exception_NotFound $e) {
            return null;
        }
    }

    /**
     * @param IndicatorAxis $axis
     */
    public function setClassificationAxis(IndicatorAxis $axis)
    {
        $this->refClassificationAxis = $axis->getRef();
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
