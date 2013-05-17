<?php
/**
 * @author     matthieu.napoli
 * @author     yoann.croizer
 * @package    AF
 * @subpackage Form
 */

/**
 * @package    AF
 * @subpackage Form
 */
class AF_Model_Action_SetValue_Numeric extends AF_Model_Action_SetValue
{

    /**
     * @var Calc_Value
     */
    protected $value;

    /**
     * Constructeur
     */
    public function __construct()
    {
        $this->value = new Calc_Value();
    }

    /**
     * {@inheritdoc}
     */
    public function getUIAction(AF_GenerationHelper $generationHelper)
    {
        $uiAction = new UI_Form_Action_SetValue($this->id);
        if (!empty($this->condition)) {
            $uiAction->condition = $generationHelper->getUICondition($this->condition);
        }
        $uiAction->uncertainty = $this->getValue()->relativeUncertainty;
        $uiAction->value = $this->getValue()->digitalValue;
        return $uiAction;
    }

    /**
     * @return Calc_Value
     */
    public function getValue()
    {
        return $this->value;
    }

    /**
     * @param Calc_Value $value
     */
    public function setValue(Calc_Value $value)
    {
        $this->value = $value;
    }

}
