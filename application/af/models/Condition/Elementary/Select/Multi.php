<?php
/**
 * @author  matthieu.napoli
 * @author  yoann.croizer
 * @package AF
 */

/**
 * @package    AF
 * @subpackage Condition
 */
class AF_Model_Condition_Elementary_Select_Multi extends AF_Model_Condition_Elementary
{

    /**
     * {@inheritdoc}
     */
    protected $relation = self::RELATION_CONTAINS;

    /**
     * Option sur laquelle agit la condition.
     * @var AF_Model_Component_Select_Option|null
     */
    protected $option;


    /**
     * {@inheritdoc}
     */
    public function getUICondition(AF_GenerationHelper $generationHelper)
    {
        $uiCondition = new UI_Form_Condition_Elementary($this->ref);
        $uiCondition->element = $generationHelper->getUIElement($this->field);
        switch ($this->relation) {
            case self::RELATION_CONTAINS:
                $uiCondition->relation = UI_Form_Condition_Elementary::EQUAL;
                break;
            case self::RELATION_NCONTAINS:
                $uiCondition->relation = UI_Form_Condition_Elementary::NEQUAL;
                break;
            default :
                throw new Core_Exception("The relation '$this->relation'' is invalid or undefined");
        }
        $uiCondition->value = $generationHelper->getUIOption($this->option)->ref;
        return $uiCondition;
    }

    /**
     * @param int $relation
     * @throws Core_Exception_InvalidArgument
     */
    public function setRelation($relation)
    {
        if ($relation != self::RELATION_CONTAINS && $relation != self::RELATION_NCONTAINS) {
            throw new Core_Exception_InvalidArgument("Invalid relation $relation");
        }
        $this->relation = $relation;
    }

    /**
     * @return AF_Model_Component_Select_Option
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
