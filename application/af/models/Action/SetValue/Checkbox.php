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
class AF_Model_Action_SetValue_Checkbox extends AF_Model_Action_SetValue
{

    /**
     * @var bool
     */
    protected $checked = false;

    /**
     * {@inheritdoc}
     */
    public function getUIAction(AF_GenerationHelper $generationHelper)
    {
        $uiAction = new UI_Form_Action_SetValue($this->id);
        if (!empty($this->condition)) {
            $uiAction->condition = $generationHelper->getUICondition($this->condition);
        }
        $uiAction->value = $this->checked;
        return $uiAction;
    }

    /**
     * @return bool
     */
    public function getChecked()
    {
        return $this->checked;
    }

    /**
     * @param bool $checked
     */
    public function setChecked($checked)
    {
        $this->checked = (bool) $checked;
    }

}
