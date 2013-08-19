<?php
/**
 * @author     matthieu.napoli
 * @author     yoann.croizer
 * @author     benjamin.bertin
 * @package    Algo
 * @subpackage Output
 */

/**
 * This class is used to index a value output from numeric algo
 *
 * @package    Algo
 * @subpackage Output
 */
class Algo_Model_Output
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
     * Indicator indexing the value
     * @var Classif_Model_ContextIndicator
     */
    protected $contextIndicator;

    /**
     * The algo label
     * @var string
     */
    protected $label;

    /**
     * Members indexing the value
     * @var Classif_Model_Member[]
     */
    protected $classifMembers = [];


    /**
     * Create a new Algo_Model_Output with an indicator
     * and (possibly) indexing members as an array
     * @param Calc_UnitValue                 $value          The value's value
     * @param Classif_Model_ContextIndicator $contextIndicator
     * @param Classif_Model_Member[]         $classifMembers
     * @param string                         $label          The algo's label
     */
    public function __construct(Calc_UnitValue $value, Classif_Model_ContextIndicator $contextIndicator,
                                array $classifMembers, $label
    ) {
        $this->sourceValue = clone $value;
        $this->contextIndicator = $contextIndicator;
        $this->label = $label;
        $unit = $contextIndicator->getIndicator()->getUnit();
        // Get the value using the conversionFactor
        $conversionFactor = $unit->getConversionFactor($this->sourceValue->getUnit()->getRef());
        $this->value = new Calc_Value(
            $value->getDigitalValue() * $conversionFactor,
            $value->getRelativeUncertainty()
        );
        foreach ($classifMembers as $member) {
            $this->classifMembers[] = $member;
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
     * Return the label
     * @return string
     */
    public function getLabel()
    {
        return $this->label;
    }

    /**
     * Set the label
     * @param string $label
     */
    public function setLabel($label)
    {
        $this->label = (string) $label;
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
     * Return the indicator indexing the value
     * @return Classif_Model_ContextIndicator
     */
    public function getContextIndicator()
    {
        return $this->contextIndicator;
    }

    /**
     * @param Classif_Model_ContextIndicator $contextIndicator
     */
    public function setContextIndicator(Classif_Model_ContextIndicator $contextIndicator)
    {
        $this->contextIndicator = $contextIndicator;
    }

    /**
     * Add a member to the value index
     * @param Classif_Model_Member $member
     */
    public function addMember(Classif_Model_Member $member)
    {
        $this->classifMembers[] = $member;
    }

    /**
     * Return the members indexing the value
     * @return Classif_Model_Member[]
     */
    public function getClassifMembers()
    {
        return $this->classifMembers;
    }

}
