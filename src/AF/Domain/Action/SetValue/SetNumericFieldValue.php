<?php

namespace AF\Domain\Action\SetValue;

use AF\Domain\Action\SetValue;
use Calc_Value;
use UI_Form_Action_SetValue;
use AF\Domain\AFGenerationHelper;

/**
 * @author matthieu.napoli
 * @author yoann.croizer
 */
class SetNumericFieldValue extends SetValue
{
    /**
     * @var Calc_Value
     */
    protected $value;

    public function __construct()
    {
        $this->value = new Calc_Value();
    }

    /**
     * {@inheritdoc}
     */
    public function getUIAction(AFGenerationHelper $generationHelper)
    {
        $uiAction = new UI_Form_Action_SetValue($this->id);
        if (!empty($this->condition)) {
            $uiAction->condition = $generationHelper->getUICondition($this->condition);
        }
        $uiAction->uncertainty = $this->getValue()->getRelativeUncertainty();
        $uiAction->value = $this->getValue()->getDigitalValue();
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
