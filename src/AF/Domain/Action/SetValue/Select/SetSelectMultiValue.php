<?php

namespace AF\Domain\Action\SetValue\Select;

use AF\Domain\AFGenerationHelper;
use AF\Domain\Action\SetValue;
use AF\Domain\Component\Select\SelectOption;
use Doctrine\Common\Collections\Collection;
use Doctrine\Common\Collections\ArrayCollection;
use UI_Form_Action_SetValue;

/**
 * @author matthieu.napoli
 * @author yoann.croizer
 */
class SetSelectMultiValue extends SetValue
{
    /**
     * Array of options
     * @var SelectOption[]|Collection
     */
    protected $options;

    public function __construct()
    {
        $this->options = new ArrayCollection();
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
        $optionsRef = $this->options->map(function (SelectOption $option) {
            return $option->getRef();
        });
        /** @var $optionsRef Collection */
        $uiAction->value = $optionsRef->toArray();
        return $uiAction;
    }

    /**
     * Get the options.
     * @return \AF\Domain\Component\Select\SelectOption[]
     */
    public function getOptions()
    {
        return $this->options;
    }

    /**
     * @param \AF\Domain\Component\Select\SelectOption $option
     * @return bool
     */
    public function hasOption(SelectOption $option)
    {
        return $this->options->contains($option);
    }

    /**
     * @param SelectOption $option
     */
    public function addOption(SelectOption $option)
    {
        if (!$this->hasOption($option)) {
            $this->options->add($option);
        }
    }

    /**
     * @param \AF\Domain\Component\Select\SelectOption $option
     */
    public function removeOption(SelectOption $option)
    {
        if ($this->hasOption($option)) {
            $this->options->removeElement($option);
        }
    }
}
