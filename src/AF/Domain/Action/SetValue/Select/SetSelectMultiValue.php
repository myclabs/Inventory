<?php

namespace AF\Domain\Action\SetValue\Select;

use AF\Domain\AFGenerationHelper;
use AF\Domain\Action\SetValue;
use AF\Domain\Component\Select\SelectOption;
use Doctrine\Common\Collections\Collection;
use Doctrine\Common\Collections\ArrayCollection;
use AF\Application\Form\Action\SetValue as FormSetValue;

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
        $uiAction = new FormSetValue($this->id);
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
     * @return SelectOption[]
     */
    public function getOptions()
    {
        return $this->options;
    }

    /**
     * @param SelectOption $option
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
     * @param SelectOption $option
     */
    public function removeOption(SelectOption $option)
    {
        if ($this->hasOption($option)) {
            $this->options->removeElement($option);
        }
    }
}
