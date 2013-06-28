<?php
/**
 * @author     matthieu.napoli
 * @author     yoann.croizer
 * @package    AF
 * @subpackage Input
 */

/**
 * Input Element for numerics fields
 * @package    AF
 * @subpackage Input
 */
class AF_Model_Input_Numeric extends AF_Model_Input implements Algo_Model_Input_Numeric
{

    /**
     * @var Calc_UnitValue
     */
    protected $value;


    /**
     * @param AF_Model_InputSet  $inputSet
     * @param AF_Model_Component $component
     */
    public function __construct(AF_Model_InputSet $inputSet, AF_Model_Component $component)
    {
        parent::__construct($inputSet, $component);
        $this->value = new Calc_UnitValue();
    }

    /**
     * @return int Nombre de champs remplis dans le composant
     */
    public function getNbRequiredFieldsCompleted()
    {
        if (!$this->isHidden()) {
            /** @var $component AF_Model_Component_Numeric */
            $component = $this->getComponent();
            if ($component->getRequired() && $this->value->value->digitalValue != null) {
                return 1;
            }
        }
        return 0;
    }

    /**
     * @return Calc_UnitValue
     */
    public function getValue()
    {
        return $this->value;
    }

    /**
     * @param Calc_UnitValue $value
     */
    public function setValue(Calc_UnitValue $value)
    {
        if (!$value instanceof Calc_UnitValue) {
            throw new Core_Exception_InvalidArgument('The value must be a Calc_UnitValue');
        }
        $this->value = $value;
    }

    /**
     * {@inheritdoc}
     */
    public function equals(AF_Model_Input $input)
    {
        $equals = parent::equals($input);
        if (! $equals) {
            return false;
        }

        if ($input instanceof AF_Model_Input_Numeric) {
            return $this->getValue()->toCompare($input->getValue(), Calc_UnitValue::RELATION_EQUAL);
        }

        return false;
    }

}
