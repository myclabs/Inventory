<?php
/**
 * @author  valentin.claras
 * @author  hugo.charbonniere
 * @author  yoann.croizer
 * @package Unit
 */

namespace Unit\Domain\Unit;

use Unit\Domain\UnitExtension;

/**
 * Unité Étendue
 * Une unité étendue est formée d'une unité standard plus une extension.
 * @package    Unit
 * @subpackage Model
 */
class ExtendedUnit extends Unit
{
    // Constantes de tri et filtres.
    const QUERY_EXTENSION = 'extension';
    const QUERY_UNIT_STANDARD = 'standardUnit';


    /**
     * Coefficient mutliplicateur d'une unité Etendue.
     * Permet de connaitre le rapport entre deux unités étendues
     *  ayant la même unité de référence des grandeur physique de base.
     * @var float
     */
    protected $multiplier;

    /**
     * Extension associée à l'unité étendue.
     * @var UnitExtension
     */
    protected $extension;

    /**
     * Unité standard associée à l'unité étendue.
     * @var StandardUnit
     */
    protected $standardUnit;


    /**
     * Renvoie la référence de la pool active.
     *  Il s'agit de l'entityManager correspondant.
     *
     * @return string
     */
    public static function getActivePoolName()
    {
        return Unit::getActivePoolName();
    }

    /**
     * Retourne l'objet Unit à partir de son référent textuel.
     * @param string $ref
     * @return \Unit\Domain\Unit\ExtendedUnit
     */
    public static function loadByRef($ref)
    {
        return parent::loadByRef($ref);
    }

    /**
     * Défini le coefficient multiplicateur de l'unité étendue.
     * @param int $multiplier
     */
    public function setMultiplier($multiplier)
    {
        $this->multiplier = $multiplier;
    }

    /**
     * Renvoie le coefficient multiplicateur.
     * @throws \Core_Exception_UndefinedAttribute
     * @return int
     */
    public function getMultiplier()
    {
        if ($this->multiplier === null) {
            throw new \Core_Exception_UndefinedAttribute('Multiplier has not be defined');
        }
        return $this->multiplier;
    }

    /**
     * Défini l'extension rattaché à cette Grandeur Physique.
     * @param UnitExtension $unitExtension
     */
    public function setExtension(UnitExtension $unitExtension)
    {
        $this->extension = $unitExtension;
    }

    /**
     * Renvoi l'extension rattaché à cette Grandeur Physique.
     * @return UnitExtension
     */
    public function getExtension()
    {
        return $this->extension;
    }

    /**
     * Défini l'unité standard associée à une unité étendue.
     * @param StandardUnit $standardUnit
     */
    public function setStandardUnit(StandardUnit $standardUnit)
    {
        $this->standardUnit = $standardUnit;
    }

    /**
     * Défini l'unité standard associée à une unité étendue.
     * @return StandardUnit
     */
    public function getStandardUnit()
    {
        return $this->standardUnit;
    }

    /**
     * Renvoi le facteur de Conversion de l'unité
     * @param Unit $unit
     * @return int
     */
    public function getConversionFactor(Unit $unit)
    {
        return $this->multiplier;
    }

    /**
     * Récupère l'unité de référence d'une unité étendue.
     * Il s'agit de l'unité de référence de l'unité standard suivi du suffixe 'equCO2'
     * @return \Unit\Domain\Unit\ExtendedUnit
     */
    public function getReferenceUnit()
    {
        $standardUnit = $this->getStandardUnit()->getReferenceUnit();

        $extendedReferenceUnit = new ExtendedUnit();
        $extendedReferenceUnit->setRef($standardUnit->getRef() . '_co2e');
        $extendedReferenceUnit->setName('(' . $standardUnit->getName() . ' equivalent CO2)');
        $extendedReferenceUnit->setSymbol('(' . $standardUnit->getSymbol() . '.equCO2)');
        $extendedReferenceUnit->setStandardUnit($standardUnit);
        $extendedReferenceUnit->setExtension($this->getExtension());

        // L'unité étendue servant uniquement de proxy, elle est supprimée de l'entité manager.
        \Core\ContainerSingleton::getEntityManager()->detach($extendedReferenceUnit);

        return $extendedReferenceUnit;
    }

    /**
     * {@inheritdoc}
     */
    public function getCompatibleUnits()
    {
        return [];
    }

}
