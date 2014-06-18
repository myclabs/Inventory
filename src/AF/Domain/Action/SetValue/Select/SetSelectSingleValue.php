<?php

namespace AF\Domain\Action\SetValue\Select;

use AF\Domain\Action\SetValue;
use AF\Application\Form\Action\SetValue as FormSetValue;
use AF\Domain\Component\Select\SelectOption;
use AF\Domain\AFGenerationHelper;

/**
 * @author matthieu.napoli
 * @author yoann.croizer
 */
class SetSelectSingleValue extends SetValue
{
    /**
     * @var SelectOption|null
     */
    protected $option;

    /**
     * {@inheritdoc}
     */
    public function getUIAction(AFGenerationHelper $generationHelper)
    {
        $uiAction = new FormSetValue($this->id);
        if (!empty($this->condition)) {
            $uiAction->condition = $generationHelper->getUICondition($this->condition);
        }
        if ($this->option) {
            $uiAction->value = $this->option->getRef();
        }
        return $uiAction;
    }

    /**
     * @return SelectOption|null
     */
    public function getOption()
    {
        return $this->option;
    }

    /**
     * @param SelectOption|null $option
     */
    public function setOption(SelectOption $option = null)
    {
        $this->option = $option;
    }
}
