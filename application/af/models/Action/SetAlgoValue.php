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
class AF_Model_Action_SetAlgoValue extends AF_Model_Action
{

    /**
     * @var Algo_Model_Algo|null
     */
    protected $algo;

    /**
     * @return Algo_Model_Algo|null
     */
    public function getAlgo()
    {
        return $this->algo;
    }

    /**
     * @param Algo_Model_Algo|null $algo
     */
    public function setAlgo(Algo_Model_Algo $algo = null)
    {
        $this->algo = $algo;
    }

    /**
     * {@inheritdoc}
     */
    public function getUIAction(AF_GenerationHelper $generationHelper)
    {
        $uiAction = new UI_Form_Action_SetValue($this->id);
        if (!empty($this->condition)) {
            $uiAction->condition = $generationHelper->getUICondition($this->condition);
        }
        // URL ajax à appeler pour récupérer la valeur (oulala c'est moche !)
//            $this->uiAction->request = 'af/ajax/getalgocalculation'
//                . '?refAlgo=' . $this->algo->getRef()
//                . '&idAction=' . $this->getId()
//                . '&idAF=' . $this->getTargetComponent()->getAf()->getId()
//                . '&idElement=' . $this->getTargetComponent()->getId();
        return $uiAction;
    }

}
