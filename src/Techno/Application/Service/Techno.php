<?php

namespace Techno\Application\Service;

use Calc_UnitValue;
use Core_Exception_InvalidArgument;
use Core_Exception_NotFound;
use Keyword\Application\Service\KeywordDTO;
use Techno\Domain\Element\CoeffElement;
use Techno\Domain\Element\ProcessElement;
use Techno\Domain\Family\Family;
use Techno\Domain\Meaning;

/**
 * Service haut niveau pour accéder en lecture aux données de techno
 * @author matthieu.napoli
 */
class Techno_Service_Techno
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
     * Retourne un meaning
     * @param string $keywordRef Identifiant du mot-clé du meaning
     * @return Meaning
     * @throws Core_Exception_NotFound
     */
    public function getMeaning($keywordRef)
    {
        return Meaning::loadByRef($keywordRef);
    }

    /**
     * Retourne la valeur dans une famille aux coordonnées spécifiées
     * @param Family $family
     * @param KeywordDTO[]        $keywords Mot-clés des membres indexés par le ref des dimensions
     * @throws \Core_Exception_InvalidArgument
     * @return null|Calc_UnitValue
     */
    public function getFamilyValueByCoordinates(Family $family, array $keywords)
    {
        if (count($keywords) != count($family->getDimensions())) {
            throw new Core_Exception_InvalidArgument("The family has " . count($family->getDimensions())
            . " dimensions, " . count($keywords) . "given");
        }
        $members = [];
        foreach ($keywords as $dimensionRef => $memberKeyword) {
            $meaning = Meaning::loadByRef($dimensionRef);
            $dimension = $family->getDimensionByMeaning($meaning);
            $members[] = $dimension->getMember($memberKeyword);
        }
        $cell = $family->getCell($members);
        $element = $cell->getChosenElement();
        if (!$element) {
            return null;
        }
        // TODO passer $value dans Techno\Domain\Element\Element pour éviter ça
        if ($element instanceof ProcessElement) {
            return new Calc_UnitValue(
                $element->getValueUnit(),
                $element->getValue()->getDigitalValue(),
                $element->getValue()->getRelativeUncertainty()
            );
        } elseif ($element instanceof CoeffElement) {
            return new Calc_UnitValue(
                $element->getValueUnit(),
                $element->getValue()->getDigitalValue(),
                $element->getValue()->getRelativeUncertainty()
            );
        }
        return null;
    }
}
