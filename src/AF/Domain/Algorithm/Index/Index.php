<?php

namespace AF\Domain\Algorithm\Index;

use AF\Domain\Algorithm\InputSet;
use AF\Domain\Algorithm\Numeric\NumericAlgo;
use Classification\Domain\Axis;
use Classification\Domain\Member;
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
     * @var Axis|null
     */
    protected $axis;

    /**
     * @var NumericAlgo|null
     */
    protected $algoNumeric;


    public function __construct(Axis $classificationAxis = null, NumericAlgo $algoNumeric = null)
    {
        $this->axis = $classificationAxis;
        $this->algoNumeric = $algoNumeric;
    }

    /**
     * Return the Classification member associated with the Result index
     * @param InputSet $inputSet
     * @return Member|null
     */
    abstract public function getClassificationMember(InputSet $inputSet = null);

    /**
     * @return Axis|null The classification axis associated to the value index
     */
    public function getClassificationAxis()
    {
        return $this->axis;
    }

    public function setClassificationAxis(Axis $axis)
    {
        $this->axis = $axis;
    }

    /**
     * @return NumericAlgo|null The algo numeric associated to the value index
     */
    public function getAlgoNumeric()
    {
        return $this->algoNumeric;
    }

    public function setAlgoNumeric(NumericAlgo $algoNumeric)
    {
        if ($algoNumeric != $this->algoNumeric) {
            $this->algoNumeric = $algoNumeric;
            $algoNumeric->addIndex($this);
        }
    }
}
