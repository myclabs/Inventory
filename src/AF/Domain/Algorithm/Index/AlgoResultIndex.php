<?php

namespace AF\Domain\Algorithm\Index;

use AF\Domain\Algorithm\InputSet;
use AF\Domain\Algorithm\Selection\TextKeySelectionAlgo;
use Classification\Domain\Member;

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
     * Return the Classification member associated with the index
     * @param InputSet $inputSet
     * @return Member
     */
    public function getClassificationMember(InputSet $inputSet = null)
    {
        $refClassificationMember = $this->getAlgo()->execute($inputSet);
        return $this->getClassificationAxis()->getMemberByRef($refClassificationMember);
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
