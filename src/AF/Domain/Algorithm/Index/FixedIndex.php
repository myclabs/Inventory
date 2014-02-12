<?php

namespace AF\Domain\Algorithm\Index;

use AF\Domain\Algorithm\InputSet;
use Classification\Domain\AxisMember;
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
     * @return AxisMember|null
     */
    public function getClassificationMember(InputSet $inputSet = null)
    {
        if ($this->refClassificationMember === null) {
            return null;
        }
        try {
            return AxisMember::loadByRefAndAxis($this->refClassificationMember, $this->getClassificationAxis());
        } catch (Core_Exception_NotFound $e) {
            return null;
        }
    }

    /**
     * @param AxisMember $member
     */
    public function setClassificationMember(AxisMember $member)
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
            AxisMember::loadByRefAndAxis($this->refClassificationMember, $this->getClassificationAxis());
            return true;
        } catch (Core_Exception_NotFound $e) {
            return false;
        }
    }
}
