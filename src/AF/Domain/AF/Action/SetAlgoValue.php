<?php

namespace AF\Domain\AF\Action;

use AF\Domain\AF\GenerationHelper;
use AF\Domain\Algorithm\Algo;
use UI_Form_Action_SetValue;

/**
 * @author matthieu.napoli
 * @author yoann.croizer
 */
class SetAlgoValue extends Action
{
    /**
     * @var Algo|null
     */
    protected $algo;

    /**
     * @return \AF\Domain\Algo\Algo|null
     */
    public function getAlgo()
    {
        return $this->algo;
    }

    /**
     * @param \AF\Domain\Algo\\AF\Domain\Algorithm\Algo|null $algo
     */
    public function setAlgo(Algo $algo = null)
    {
        $this->algo = $algo;
    }

    /**
     * {@inheritdoc}
     */
    public function getUIAction(GenerationHelper $generationHelper)
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
