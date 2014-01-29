<?php
use AF\Domain\Algorithm\ParameterCoordinate;
use AF\Domain\Algorithm\InputSet;

/**
 * @author matthieu.napoli
 * @author cyril.perraud
 */
class Algo_Model_ParameterCoordinate_Algo extends ParameterCoordinate
{
    /**
     * Algo Keyword associé
     * @var Algo_Model_Selection_TextKey
     */
    protected $algoKeyword;

    /**
     * Renvoi le ref de keyword calculé par l'Algo_Model_ParameterCoordinate_Algo associé
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
     * @return Algo_Model_Selection_TextKey
     */
    public function getSelectionAlgo()
    {
        return $this->algoKeyword;
    }

    /**
     * @param Algo_Model_Selection_TextKey $selectionAlgo
     */
    public function setSelectionAlgo(Algo_Model_Selection_TextKey $selectionAlgo)
    {
        $this->algoKeyword = $selectionAlgo;
    }
}
