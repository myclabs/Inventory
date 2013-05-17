<?php
/**
 * @author  matthieu.napoli
 * @author  cyril.perraud
 * @package Algo
 */

/**
 * @package Algo
 */
class Algo_Model_ParameterCoordinate_Algo extends Algo_Model_ParameterCoordinate
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
    public function getMemberKeyword(Algo_Model_InputSet $inputSet = null)
    {
        if (!$inputSet) {
            throw new Core_Exception_InvalidArgument("The InputSet can't be null");
        }
        $keyword = $this->algoKeyword->execute($inputSet);
        return Keyword_Model_Keyword::loadByRef($keyword);
    }

    /**
     * @return Algo_Model_Selection_TextKey
     */
    public function getAlgoKeyword()
    {
        return $this->algoKeyword;
    }

    /**
     * @param Algo_Model_Selection_TextKey $algoKeyword
     */
    public function setAlgoKeyword(Algo_Model_Selection_TextKey $algoKeyword)
    {
        $this->algoKeyword = $algoKeyword;
    }

}
