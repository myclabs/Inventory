<?php

namespace AF\Domain\Algorithm\Index;

use AF\Domain\Algorithm\InputSet;
use AF\Domain\Algorithm\Selection\TextKeySelectionAlgo;
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
     * @var TextKeySelectionAlgo|null
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
     * @return TextKeySelectionAlgo|null
     */
    public function getAlgo()
    {
        return $this->algo;
    }

    /**
     * @param TextKeySelectionAlgo $algo
     */
    public function setAlgo(TextKeySelectionAlgo $algo)
    {
        $this->algo = $algo;
    }
}
