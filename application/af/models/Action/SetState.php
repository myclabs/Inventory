<?php
/**
 * @author  matthieu.napoli
 * @author  yoann.croizer
 * @package AF
 */

/**
 * @package AF
 */
class AF_Model_Action_SetState extends AF_Model_Action
{

    /**
     * @var int
     */
    protected $state;


    /**
     * {@inheritdoc}
     */
    public function getUIAction(AF_GenerationHelper $generationHelper)
    {
        switch ($this->state) {
            case self::TYPE_DISABLE:
                $uiAction = new UI_Form_Action_Disable($this->id);
                break;
            case self::TYPE_ENABLE:
                $uiAction = new UI_Form_Action_Enable($this->id);
                break;
            case self::TYPE_HIDE:
                $uiAction = new UI_Form_Action_Hide($this->id);
                break;
            case self::TYPE_SHOW:
                $uiAction = new UI_Form_Action_Show($this->id);
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
