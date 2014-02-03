<?php

namespace AF\Domain\Algorithm\Index;

use AF\Domain\Algorithm\InputSet;
use Classif_Model_Member;
use Core_Exception_NotFound;

/**
 * @author matthieu.napoli
 * @author cyril.perraud
 * @author yoann.croizer
 */
class FixedIndex extends Index
{
    /**
     * The classif member associated to the index
     * @var string|null
     */
    protected $refClassifMember;

    /**
     * @param InputSet $inputSet
     * @return Classif_Model_Member|null
     */
    public function getClassifMember(InputSet $inputSet = null)
    {
        if ($this->refClassifMember === null) {
            return null;
        }
        try {
            return Classif_Model_Member::loadByRefAndAxis($this->refClassifMember, $this->getClassifAxis());
        } catch (Core_Exception_NotFound $e) {
            return null;
        }
    }

    /**
     * @param Classif_Model_Member $member
     */
    public function setClassifMember(Classif_Model_Member $member)
    {
        $this->refClassifMember = $member->getRef();
    }

    /**
     * Vérifie si un membre est associé à l'index
     * @return bool
     */
    public function hasClassifMember()
    {
        if ($this->refClassifMember === null) {
            return false;
        }
        try {
            Classif_Model_Member::loadByRefAndAxis($this->refClassifMember, $this->getClassifAxis());
            return true;
        } catch (Core_Exception_NotFound $e) {
            return false;
        }
    }
}
