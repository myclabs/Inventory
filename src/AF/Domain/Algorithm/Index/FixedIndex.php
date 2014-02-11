<?php

namespace AF\Domain\Algorithm\Index;

use AF\Domain\Algorithm\InputSet;
use Classif\Domain\AxisMember;
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
     * @return AxisMember|null
     */
    public function getClassifMember(InputSet $inputSet = null)
    {
        if ($this->refClassifMember === null) {
            return null;
        }
        try {
            return AxisMember::loadByRefAndAxis($this->refClassifMember, $this->getClassifAxis());
        } catch (Core_Exception_NotFound $e) {
            return null;
        }
    }

    /**
     * @param AxisMember $member
     */
    public function setClassifMember(AxisMember $member)
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
            AxisMember::loadByRefAndAxis($this->refClassifMember, $this->getClassifAxis());
            return true;
        } catch (Core_Exception_NotFound $e) {
            return false;
        }
    }
}
