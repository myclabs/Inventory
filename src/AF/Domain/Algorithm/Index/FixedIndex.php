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
    protected $refMember;

    /**
     * @param InputSet $inputSet
     * @return Member|null
     */
    public function getClassificationMember(InputSet $inputSet = null)
    {
        if ($this->refMember === null) {
            return null;
        }
        try {
            return $this->getClassificationAxis()->getMemberByRef($this->refMember);
        } catch (Core_Exception_NotFound $e) {
            return null;
        }
    }

    /**
     * @param Member $member
     */
    public function setClassificationMember(Member $member)
    {
        $this->refMember = $member->getRef();
    }

    /**
     * Vérifie si un membre est associé à l'index
     * @return bool
     */
    public function hasClassificationMember()
    {
        if ($this->refMember === null) {
            return false;
        }
        try {
            $this->getClassificationAxis()->getMemberByRef($this->refMember);
            return true;
        } catch (Core_Exception_NotFound $e) {
            return false;
        }
    }
}
