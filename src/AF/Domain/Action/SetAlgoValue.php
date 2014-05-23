<?php

namespace AF\Domain\Action;

use AF\Domain\AFGenerationHelper;
use AF\Domain\Algorithm\Algo;
use AF\Application\Form\Action\SetValue as FormSetValue;

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
     * @return Algo|null
     */
    public function getAlgo()
    {
        return $this->algo;
    }

    /**
     * @param Algo|null $algo
     */
    public function setAlgo(Algo $algo = null)
    {
        $this->algo = $algo;
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
        // URL ajax à appeler pour récupérer la valeur (oulala c'est moche !)
//            $this->uiAction->request = 'af/ajax/getalgocalculation'
//                . '?refAlgo=' . $this->algo->getRef()
//                . '&idAction=' . $this->getId()
//                . '&idAF=' . $this->getTargetComponent()->getAf()->getId()
//                . '&idElement=' . $this->getTargetComponent()->getId();
        return $uiAction;
    }
}
