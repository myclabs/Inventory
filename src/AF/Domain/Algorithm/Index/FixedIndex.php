<?php

namespace AF\Domain\Algorithm\Index;

use AF\Domain\Algorithm\InputSet;
use Classification\Domain\Member;
use Core_Exception_NotFound;

/**
 * @author matthieu.napoli
 * @author cyril.perraud
 * @author yoann.croizer
 */
class FixedIndex extends Index
{
    /**
     * The classification member associated to the index
     * @var string|null
     */
    protected $refClassificationMember;

    /**
     * @param InputSet $inputSet
     * @return Member|null
     */
    public function getClassificationMember(InputSet $inputSet = null)
    {
        if ($this->refClassificationMember === null) {
            return null;
        }
        try {
            return $this->getClassificationAxis()->getMemberByRef($this->refClassificationMember);
        } catch (Core_Exception_NotFound $e) {
            return null;
        }
    }

    /**
     * @param Member $member
     */
    public function setClassificationMember(Member $member)
    {
        $this->refClassificationMember = $member->getRef();
    }

    /**
     * Vérifie si un membre est associé à l'index
     * @return bool
     */
    public function hasClassificationMember()
    {
        if ($this->refClassificationMember === null) {
            return false;
        }
        try {
            $this->getClassificationAxis()->getMemberByRef($this->refClassificationMember);
            return true;
        } catch (Core_Exception_NotFound $e) {
            return false;
        }
    }
}
