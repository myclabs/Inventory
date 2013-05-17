<?php
/**
 * @author  matthieu.napoli
 * @author  yoann.croizer
 * @package AF
 */

/**
 * @package    AF
 * @subpackage Condition
 */
class AF_Model_Condition_Elementary_Checkbox extends AF_Model_Condition_Elementary
{

    /**
     * Valeur boolean pour laquelle la condition est effective.
     * @var bool
     */
    protected $value = true;

    /**
     * {@inheritdoc}
     */
    public function getUICondition(AF_GenerationHelper $generationHelper)
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
