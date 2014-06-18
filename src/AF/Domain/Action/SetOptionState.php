<?php

namespace AF\Domain\Action;

use AF\Domain\AFGenerationHelper;
use AF\Domain\Component\Select\SelectOption;
use Core_Exception;
use AF\Application\Form\Action\Disable;
use AF\Application\Form\Action\Enable;
use AF\Application\Form\Action\Hide;
use AF\Application\Form\Action\Show;

/**
 * @author matthieu.napoli
 */
class SetOptionState extends Action
{
    /**
     * @var int
     */
    protected $state;

    /**
     * @var SelectOption
     */
    protected $option;

    /**
     * {@inheritdoc}
     */
    public function getUIAction(AFGenerationHelper $generationHelper)
    {
        switch ($this->state) {
            case self::TYPE_DISABLE:
                $uiAction = new Disable($this->id);
                break;
            case self::TYPE_ENABLE:
                $uiAction = new Enable($this->id);
                break;
            case self::TYPE_HIDE:
                $uiAction = new Hide($this->id);
                break;
            case self::TYPE_SHOW:
                $uiAction = new Show($this->id);
                break;
            default:
                throw new Core_Exception("Unknown type $this->state");
        }
        if (!empty($this->condition)) {
            $uiAction->condition = $generationHelper->getUICondition($this->condition);
        }
        $uiAction->setOption($generationHelper->getUIOption($this->option));
        return $uiAction;
    }

    /**
     * Get the state attribute.
     * @return int
     */
    public function getState()
    {
        return $this->state;
    }

    /**
     * Set the state attribute.
     * @param int $state
     */
    public function setState($state)
    {
        $this->state = $state;
    }

    /**
     * @return \AF\Domain\Component\Select\SelectOption
     */
    public function getOption()
    {
        return $this->option;
    }

    /**
     * @param \AF\Domain\Component\Select\SelectOption $option
     */
    public function setOption(SelectOption $option)
    {
        $this->option = $option;
    }
}
