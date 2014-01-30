<?php

namespace AF\Domain\Condition;

use UI_Form_Condition_Elementary;
use AF\Domain\AFGenerationHelper;

/**
 * @author matthieu.napoli
 * @author yoann.croizer
 */
class CheckboxCondition extends ElementaryCondition
{
    /**
     * Valeur boolean pour laquelle la condition est effective.
     * @var bool
     */
    protected $value = true;

    /**
     * {@inheritdoc}
     */
    public function getUICondition(AFGenerationHelper $generationHelper)
    {
        $uiCondition = new UI_Form_Condition_Elementary($this->ref);
        $uiCondition->element = $generationHelper->getUIElement($this->field);
        $uiCondition->relation = UI_Form_Condition_Elementary::EQUAL;
        $uiCondition->value = $this->value;
        return $uiCondition;
    }

    /**
     * Valeur boolean pour laquelle la condition est effective.
     * @return bool
     */
    public function getValue()
    {
        return $this->value;
    }

    /**
     * Valeur boolean pour laquelle la condition est effective.
     * @param bool $value
     */
    public function setValue($value)
    {
        $this->value = (bool) $value;
    }
}
