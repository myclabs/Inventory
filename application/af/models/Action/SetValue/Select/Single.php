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
class AF_Model_Action_SetValue_Select_Single extends AF_Model_Action_SetValue
{

    /**
     * @var AF_Model_Component_Select_Option|null
     */
    protected $option;

    /**
     * {@inheritdoc}
     */
    public function getUIAction(AF_GenerationHelper $generationHelper)
    {
        $uiAction = new UI_Form_Action_SetValue($this->id);
        if (!empty($this->condition)) {
            $uiAction->condition = $generationHelper->getUICondition($this->condition);
        }
        if ($this->option) {
            $uiAction->value = $this->option->getRef();
        }
        return $uiAction;
    }

    /**
     * @return AF_Model_Component_Select_Option|null
     */
    public function getOption()
    {
        return $this->option;
    }

    /**
     * @param AF_Model_Component_Select_Option|null $option
     */
    public function setOption(AF_Model_Component_Select_Option $option = null)
    {
        $this->option = $option;
    }

}
