<?php

namespace AF\Domain\Algorithm\ParameterCoordinate;

use AF\Domain\Algorithm\InputSet;
use AF\Domain\Algorithm\Selection\TextKeySelectionAlgo;
use Core_Exception_InvalidArgument;

/**
 * @author matthieu.napoli
 * @author cyril.perraud
 */
class AlgoParameterCoordinate extends ParameterCoordinate
{
    /**
     * Algo Keyword associé
     * @var \AF\Domain\Algorithm\Selection\TextKeySelectionAlgo
     */
    protected $algoKeyword;

    /**
     * Renvoi le ref de keyword calculé par l'AlgoParameterCoordinate associé
     * {@inheritdoc}
     */
    public function getMember(InputSet $inputSet = null)
    {
        if (!$inputSet) {
            throw new Core_Exception_InvalidArgument("The InputSet can't be null");
        }
        return $this->algoKeyword->execute($inputSet);
    }

    /**
     * @return \AF\Domain\Algorithm\Selection\TextKeySelectionAlgo
     */
    public function getSelectionAlgo()
    {
        return $this->algoKeyword;
    }

    /**
     * @param \AF\Domain\Algorithm\Selection\TextKeySelectionAlgo $selectionAlgo
     */
    public function setSelectionAlgo(TextKeySelectionAlgo $selectionAlgo)
    {
        $this->algoKeyword = $selectionAlgo;
    }
}
