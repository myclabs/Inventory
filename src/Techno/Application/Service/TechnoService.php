<?php

namespace Techno\Application\Service;

use Calc_UnitValue;
use Core_Exception_InvalidArgument;
use Core_Exception_NotFound;
use Techno\Domain\Family\Family;

/**
 * Service haut niveau pour accéder en lecture aux données de techno.
 *
 * @author matthieu.napoli
 */
class TechnoService
{
    /**
     * Retourne une famille
     * @param string $ref Identifiant de la famille
     * @return Family
     * @throws Core_Exception_NotFound Famille inconnue
     */
    public function getFamily($ref)
    {
        return Family::loadByRef($ref);
    }

    /**
     * Retourne la valeur dans une famille aux coordonnées spécifiées.
     *
     * @param Family   $family
     * @param string[] $membersRef Ref des membres indexés par le ref des dimensions
     *
     * @throws Core_Exception_NotFound No value defined for this coordinate.
     * @throws Core_Exception_InvalidArgument Not enough/too many members given.
     * @return null|Calc_UnitValue
     */
    public function getFamilyValueByCoordinates(Family $family, array $membersRef)
    {
        if (count($membersRef) != count($family->getDimensions())) {
            throw new Core_Exception_InvalidArgument(sprintf(
                'The family has %s dimensions, %s members given',
                count($family->getDimensions()),
                count($membersRef)
            ));
        }

        $members = [];
        foreach ($membersRef as $dimensionRef => $memberRef) {
            $dimension = $family->getDimension($dimensionRef);
            $members[] = $dimension->getMember($memberRef);
        }

        $cell = $family->getCell($members);

        $value = $cell->getValue();

        if ($value === null) {
            throw new Core_Exception_NotFound(sprintf(
                'No value is defined in family "%s" for the coordinates "%s"',
                $family->getRef(),
                implode(', ', $membersRef)
            ));
        }

        return new Calc_UnitValue(
            $family->getValueUnit(),
            $value->getDigitalValue(),
            $value->getRelativeUncertainty()
        );
    }
}
