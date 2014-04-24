<?php

namespace AccountingForm\Domain\Processing\ParameterImporter;

use AccountingForm\Domain\ValueSet;
use AF\Domain\Algorithm\AlgoConfigurationError;
use Core_Exception_NotFound;
use Core_Exception_UndefinedAttribute;

/**
 * @author matthieu.napoli
 * @author cyril.perraud
 */
class FixedParameterCoordinate extends ParameterCoordinate
{
    /**
     * @var string|null
     */
    protected $memberRef;

    /**
     * @param string $dimensionRef
     * @param string $memberRef
     */
    public function __construct($dimensionRef, $memberRef)
    {
        parent::__construct($dimensionRef);

        $this->memberRef = $memberRef;
    }

    /**
     * {@inheritdoc}
     */
    public function getMemberRef(ValueSet $values)
    {
        if (! $this->memberRef) {
            throw new Core_Exception_UndefinedAttribute("The member of the parameter coordinate is not defined");
        }

        return $this->memberRef;
    }

    /**
     * @param string $memberId
     */
    public function setMember($memberId)
    {
        $this->memberRef = $memberId;
    }

    public function validate()
    {
        if (! $this->memberRef) {
            return [
                new AlgoConfigurationError(__('Algo', 'configControl', 'noMember', [
                    'REF_DIMENSION' => $this->dimensionRef,
                    'REF_ALGO'      => $this->getAlgoParameter()->getRef(),
                ]), true)
            ];
        }

        try {
            $this->getDimension()->getMember($this->memberRef);
        } catch (Core_Exception_NotFound $e) {
            return [
                new AlgoConfigurationError(__('Algo', 'configControl', 'invalidMember', [
                    'REF_DIMENSION' => $this->dimensionRef,
                    'REF_ALGO'      => $this->getAlgoParameter()->getRef(),
                    'REF_MEMBER'    => $this->memberRef
                ]), true)
            ];
        }

        return [];
    }
}
