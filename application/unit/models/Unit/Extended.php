<?php
/**
 * Classe Unit_Model_Unit_Extended
 * @author valentin.claras
 * @author hugo.charbonniere
 * @author yoann.croizer
 * @package Unit
 */

/**
 * Unité Étendue
 * Une unité étendue est formée d'une unité standard plus une extension.
 * @package Unit
 * @subpackage Model
 */
class Unit_Model_Unit_Extended extends Unit_Model_Unit
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
     * @var Unit_Model_Unit_Extension
     */
    protected $extension;

    /**
     * Unité standard associée à l'unité étendue.
     * @var Unit_Model_Unit_Standard
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
        return Unit_Model_Unit::getActivePoolName();
    }

    /**
     * Retourne l'objet Unit à partir de son référent textuel.
     * @param string $ref
     * @return Unit_Model_Unit_Extended
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
     * @throws Core_Exception_UndefinedAttribute
     * @return int
     */
    public function getMultiplier()
    {
        if ($this->multiplier === null) {
            throw new Core_Exception_UndefinedAttribute('Multiplier has not be defined');
        }
        return $this->multiplier;
    }

    /**
     * Défini l'extension rattaché à cette Grandeur Physique.
     * @param Unit_Model_Unit_Extension $unitExtension
     */
    public function setExtension(Unit_Model_Unit_Extension $unitExtension)
    {
        $this->extension = $unitExtension;
    }

    /**
     * Renvoi l'extension rattaché à cette Grandeur Physique.
     * @return Unit_Model_Unit_Extension
     */
    public function getExtension()
    {
        return $this->extension;
    }

    /**
     * Défini l'unité standard associée à une unité étendue.
     * @param Unit_Model_Unit_Standard $standardUnit
     */
    public function setStandardUnit(Unit_Model_Unit_Standard $standardUnit)
    {
        $this->standardUnit = $standardUnit;
    }

    /**
     * Défini l'unité standard associée à une unité étendue.
     * @return Unit_Model_Unit_Standard
     */
    public function getStandardUnit()
    {
        return $this->standardUnit;
    }

    /**
     * Renvoi le facteur de Conversion de l'unité
     * @param Unit_Model_Unit $unit
     * @return int
     */
    public function getConversionFactor(Unit_Model_Unit $unit)
    {
        return $this->multiplier;
    }

     /**
      * Récupère l'unité de référence d'une unité étendue.
      * Il s'agit de l'unité de référence de l'unité standard suivi du suffixe 'equCO2'
      * @return Unit_Model_Unit_Extended
      */
     public function getReferenceUnit()
     {
        $standardUnit = $this->getStandardUnit()->getReferenceUnit();

        $extendedReferenceUnit = new Unit_Model_Unit_Extended();
        $extendedReferenceUnit->setRef($standardUnit->getRef().'_co2e');
        $extendedReferenceUnit->setName('('.$standardUnit->getName().' equivalent CO2)');
        $extendedReferenceUnit->setSymbol('('.$standardUnit->getSymbol().'.equCO2)');
        $extendedReferenceUnit->setStandardUnit($standardUnit);
        $extendedReferenceUnit->setExtension($this->getExtension());

        // L'unité étendue servant uniquement de proxy, elle est supprimée de l'entité manager.
        $entityManagers = Zend_Registry::get('EntityManagers');
        $entityManagers['unit']->detach($extendedReferenceUnit);

        return $extendedReferenceUnit;
     }

}