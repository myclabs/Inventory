<?php

namespace AF\Domain\Action;

use Core_Exception_InvalidArgument;

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
