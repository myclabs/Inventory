<?php

namespace AF\Domain\AF\Action\SetValue;

use AF\Domain\AF\Action\SetValue;
use UI_Form_Action_SetValue;
use AF\Domain\AF\GenerationHelper;

/**
 * @author matthieu.napoli
 * @author yoann.croizer
 */
class SetCheckboxValue extends SetValue
{
    /**
     * @var bool
     */
    protected $checked = false;

    /**
     * {@inheritdoc}
     */
    public function getUIAction(GenerationHelper $generationHelper)
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
