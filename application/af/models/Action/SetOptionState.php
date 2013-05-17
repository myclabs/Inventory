<?php
/**
 * @author  matthieu.napoli
 * @package AF
 */

/**
 * @package AF
 */
class AF_Model_Action_SetOptionState extends AF_Model_Action
{

    /**
     * @var int
     */
    protected $state;

    /**
     * @var AF_Model_Component_Select_Option
     */
    protected $option;


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
     * @return AF_Model_Component_Select_Option
     */
    public function getOption()
    {
        return $this->option;
    }

    /**
     * @param AF_Model_Component_Select_Option $option
     */
    public function setOption(AF_Model_Component_Select_Option $option)
    {
        $this->option = $option;
    }

}
