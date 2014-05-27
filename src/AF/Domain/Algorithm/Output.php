<?php

namespace AF\Domain\Algorithm;

use AF\Domain\Algorithm\Numeric\NumericAlgo;
use Calc_UnitValue;
use Calc_Value;
use Classification\Domain\Member;

/**
 * This class is used to index a value output from numeric algo
 *
 * @author matthieu.napoli
 * @author yoann.croizer
 * @author benjamin.bertin
 */
class Output
{
    /**
     * Result's value normalized with the indicator unit
     * @var Calc_Value
     */
    protected $value;

    /**
     * Result's source value with it's unit (i.e.: before the normalization with the indicator's unit)
     * @var Calc_UnitValue
     */
    protected $sourceValue;

    /**
     * @var NumericAlgo
     */
    protected $algo;

    /**
     * Members indexing the value
     * @var Member[]
     */
    protected $classificationMembers = [];


    /**
     * Create a new output with an indicator and (possibly) indexing members as an array.
     *
     * @param Calc_UnitValue $value
     * @param NumericAlgo    $algo
     * @param Member[]       $classificationMembers
     */
    public function __construct(Calc_UnitValue $value, NumericAlgo $algo, array $classificationMembers)
    {
        $this->sourceValue = clone $value;
        $this->algo = $algo;
        $unit = $algo->getContextIndicator()->getIndicator()->getUnit();

        $convertedValue = $value->convertTo($unit);
        $this->value = new Calc_Value(
            $convertedValue->getDigitalValue(),
            $convertedValue->getRelativeUncertainty()
        );
        
        foreach ($classificationMembers as $member) {
            $this->classificationMembers[] = $member;
        }
    }

    /**
     * Return the value
     * @return Calc_Value
     */
    public function getValue()
    {
        return $this->value;
    }

    /**
     * Set the value
     * @param Calc_Value $value
     */
    public function setValue(Calc_Value $value)
    {
        $this->value = $value;
    }

    /**
     * Return the source value (i.e.: before the normalization with the indicator's unit)
     * @return Calc_UnitValue
     */
    public function getSourceValue()
    {
        return $this->sourceValue;
    }

    /**
     * Set the source value
     * @param Calc_UnitValue $value
     */
    public function setSourceValue(Calc_UnitValue $value)
    {
        $this->sourceValue = $value;
    }

    /**
     * Add a member to the value index
     * @param Member $member
     */
    public function addMember(Member $member)
    {
        $this->classificationMembers[] = $member;
    }

    /**
     * Return the members indexing the value
     * @return Member[]
     */
    public function getClassificationMembers()
    {
        return $this->classificationMembers;
    }

    /**
     * @return NumericAlgo
     */
    public function getAlgo()
    {
        return $this->algo;
    }
}
