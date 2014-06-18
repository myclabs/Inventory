<?php

namespace AF\Domain\Action;

use AF\Domain\AFGenerationHelper;
use Core_Exception;
use Core_Exception_InvalidArgument;
use AF\Application\Form\Action\Disable;
use AF\Application\Form\Action\Enable;
use AF\Application\Form\Action\Hide;
use AF\Application\Form\Action\Show;

/**
 * @author matthieu.napoli
 * @author yoann.croizer
 */
class SetState extends Action
{
    /**
     * @var int
     */
    protected $state;

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
        if ($this->condition) {
            $uiAction->condition = $generationHelper->getUICondition($this->condition);
        }
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
     * @throws Core_Exception_InvalidArgument
     */
    public function setState($state)
    {
        switch ((int) $state) {
            case self::TYPE_DISABLE:
            case self::TYPE_ENABLE:
            case self::TYPE_HIDE:
            case self::TYPE_SHOW:
                $this->state = $state;
                break;
            default:
                throw new Core_Exception_InvalidArgument("Invalid state $state");
        }
    }
}
