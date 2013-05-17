<?php
/**
 * @author  matthieu.napoli
 * @author  cyril.perraud
 * @package Algo
 */

/**
 * @package Algo
 */
class Algo_Model_Index_Algo extends Algo_Model_Index
{

    /**
     * @var Algo_Model_Selection_TextKey|null
     */
    protected $algo;

    /**
     * Return the Classif member associated with the index
     * @param Algo_Model_InputSet $inputSet
     * @return Classif_Model_Member
     */
    public function getClassifMember(Algo_Model_InputSet $inputSet = null)
    {
        $refClassifMember = $this->getAlgo()->execute($inputSet);
        return Classif_Model_Member::loadByRefAndAxis($refClassifMember, $this->getClassifAxis());
    }

    /**
     * @return Algo_Model_Selection_TextKey|null
     */
    public function getAlgo()
    {
        return $this->algo;
    }

    /**
     * @param Algo_Model_Selection_TextKey $algo
     */
    public function setAlgo(Algo_Model_Selection_TextKey $algo)
    {
        $this->algo = $algo;
    }

}
