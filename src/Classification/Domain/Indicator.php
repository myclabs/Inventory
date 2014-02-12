<?php

namespace Classification\Domain;

use Core_Exception_UndefinedAttribute;
use Core_Model_Entity;
use Core_Model_Entity_Translatable;
use Core_Strategy_Ordered;
use Unit\IncompatibleUnitsException;
use Unit\UnitAPI;

/**
 * Indicateur de classification.
 *
 * @author valentin.claras
 * @author cyril.perraud
 */
class Indicator extends Core_Model_Entity
{
    use Core_Strategy_Ordered;
    use Core_Model_Entity_Translatable;

    // Constantes de tris et de filtres.
    const QUERY_REF = 'ref';
    const QUERY_LABEL = 'label';
    const QUERY_UNIT = 'unit';
    const QUERY_POSITION = 'position';

    /**
     * @var int
     */
    protected $id;

    /**
     * @var IndicatorLibrary
     */
    protected $library;

    /**
     * Référent textuel de l'indicateur.
     *
     * @var string
     */
    protected $ref;

    /**
     * Label de l'indicateur.
     *
     * @var string
     */
    protected $label;

    /**
     * Unité de l'indicateur.
     *
     * @var UnitAPI
     */
    protected $unit;

    /**
     * Unité utilisé pour les ratios.
     *
     * @var UnitAPI
     */
    protected $ratioUnit;


    /**
     * Retourne un indicateur à partir de son ref
     * @param string $ref
     * @return Indicator $indicator
     */
    public static function loadByRef($ref)
    {
        return self::getEntityRepository()->loadBy(['ref' => $ref]);
    }

    /**
     * @param IndicatorLibrary $library
     * @param string           $ref       Identifiant textuel
     * @param string           $label     Libellé
     * @param UnitAPI          $unit      Unité de l'indicateur.
     * @param UnitAPI|null     $ratioUnit Unité utilisé pour les ratios. Si null, l'unité de l'indicateur est utilisée.
     *
     * @throws IncompatibleUnitsException Unit ant RatioUnit should be compatible
     */
    public function __construct(IndicatorLibrary $library, $ref, $label, UnitAPI $unit, UnitAPI $ratioUnit = null)
    {
        $this->library = $library;
        $this->ref = $ref;
        $this->label = $label;
        $this->setUnit($unit);
        $this->setRatioUnit($ratioUnit ?: $unit);
    }

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Modifie le ref de l'indicateur.
     *
     * @param string $ref
     */
    public function setRef($ref)
    {
        $this->ref = $ref;
    }

    /**
     * Retourne le ref de l'indicateur.
     *
     * @return string
     */
    public function getRef()
    {
        return $this->ref;
    }

    /**
     * Modifie le label de l'indicateur.
     *
     * @param string $label
     */
    public function setLabel($label)
    {
        $this->label = $label;
    }

    /**
     * Retourne le label de l'indicateur.
     *
     * @return string
     */
    public function getLabel()
    {
        return $this->label;
    }

    /**
     * Modifie l'unit de l'indicateur.
     *
     * @param UnitAPI $unit
     * @throws IncompatibleUnitsException
     */
    public function setUnit(UnitAPI $unit)
    {
        if ($this->ratioUnit && !$this->ratioUnit->isEquivalent($unit)) {
            throw new IncompatibleUnitsException('Unit ant RatioUnit should be compatible');
        }

        $this->unit = $unit;
    }

    /**
     * Retourne l'unit de l'indicateur.
     *
     * @return UnitAPI
     */
    public function getUnit()
    {
        return $this->unit;
    }

    /**
     * Modifie l'unité de ratio de l'indicateur.
     *
     * @param UnitAPI $ratioUnit
     * @throws IncompatibleUnitsException
     */
    public function setRatioUnit(UnitAPI $ratioUnit)
    {
        if ($this->unit && !$this->unit->isEquivalent($ratioUnit)) {
            throw new IncompatibleUnitsException('Unit ant RatioUnit should be compatible');
        }

        $this->ratioUnit = $ratioUnit;
    }

    /**
     * Retourne l'unité de ratio de l'indicateur
     *
     * @return UnitAPI
     */
    public function getRatioUnit()
    {
        return $this->ratioUnit;
    }

    /**
     * @return string
     */
    public function __toString()
    {
        return $this->ref;
    }

    /**
     * Fonction appelée avant un persist de l'objet (défini dans le mapper).
     */
    public function preSave()
    {
        try {
            $this->checkHasPosition();
        } catch (Core_Exception_UndefinedAttribute $e) {
            $this->setPosition();
        }
    }

    /**
     * Fonction appelée avant un update de l'objet (défini dans le mapper).
     */
    public function preUpdate()
    {
        $this->checkHasPosition();
    }

    /**
     * Fonction appelée avant un delete de l'objet (défini dans le mapper).
     */
    public function preDelete()
    {
        $this->deletePosition();
    }

    /**
     * Fonction appelée après un load de l'objet (défini dans le mapper).
     */
    public function postLoad()
    {
        $this->updateCachePosition();
    }
}
