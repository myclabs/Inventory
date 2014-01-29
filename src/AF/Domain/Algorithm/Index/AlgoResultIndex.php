<?php

namespace AF\Domain\Algorithm\Index;

use AF\Domain\Algorithm\InputSet;
use Algo_Model_Selection_TextKey;
use Classif_Model_Member;

/**
 * Indexation avec le résultat d'un algorithme.
 *
 * @author matthieu.napoli
 * @author cyril.perraud
 */
class AlgoResultIndex extends Index
{
    /**
     * @var Algo_Model_Selection_TextKey|null
     */
    protected $algo;

    /**
     * Return the Classif member associated with the index
     * @param InputSet $inputSet
     * @return Classif_Model_Member
     */
    public function getClassifMember(InputSet $inputSet = null)
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
