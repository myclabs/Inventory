<?php
/**
 * @author     matthieu.napoli
 * @package    Techno
 * @subpackage Service
 */
use Keyword\Domain\Keyword;

/**
 * @package    Techno
 * @subpackage Service
 */
class Techno_Service_Techno
{

    /**
     * Retourne une famille
     * @param string $ref Identifiant de la famille
     * @return Techno_Model_Family
     * @throws Core_Exception_NotFound Famille inconnue
     */
    public function getFamily($ref)
    {
        return Techno_Model_Family::loadByRef($ref);
    }

    /**
     * Retourne un meaning
     * @param string $keywordRef Identifiant du mot-clé du meaning
     * @return Techno_Model_Meaning
     * @throws Core_Exception_NotFound
     */
    public function getMeaning($keywordRef)
    {
        return Techno_Model_Meaning::loadByRef($keywordRef);
    }

    /**
     * Retourne la valeur dans une famille aux coordonnées spécifiées
     * @param Techno_Model_Family     $family
     * @param Keyword[] $keywords Mot-clés des membres indexés par le ref des dimensions
     * @return null|Calc_UnitValue
     */
    public function getFamilyValueByCoordinates(Techno_Model_Family $family, array $keywords)
    {
        if (count($keywords) != count($family->getDimensions())) {
            throw new Core_Exception_InvalidArgument("The family has " . count($family->getDimensions())
                                                         . " dimensions, " . count($keywords) . "given");
        }
        $members = [];
        foreach ($keywords as $dimensionRef => $memberKeyword) {
            $meaning = Techno_Model_Meaning::loadByRef($dimensionRef);
            $dimension = $family->getDimensionByMeaning($meaning);
            $members[] = $dimension->getMember($memberKeyword);
        }
        $cell = $family->getCell($members);
        $element = $cell->getChosenElement();
        if (!$element) {
            return null;
        }
        // TODO passer $value dans Techno_Model_Element pour éviter ça
        if ($element instanceof Techno_Model_Element_Process) {
            return new Calc_UnitValue(
                $element->getValueUnit(),
                $element->getValue()->getDigitalValue(),
                $element->getValue()->getRelativeUncertainty()
            );
        } elseif ($element instanceof Techno_Model_Element_Coeff) {
            return new Calc_UnitValue(
                $element->getValueUnit(),
                $element->getValue()->getDigitalValue(),
                $element->getValue()->getRelativeUncertainty()
            );
        }
        return null;
    }

}
