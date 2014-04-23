<?php

namespace Classification\Domain;

use Core_Exception_UndefinedAttribute;
use Core_Model_Entity;
use Core_Model_Entity_Translatable;
use Core_Strategy_Ordered;
use MyCLabs\UnitAPI\Exception\IncompatibleUnitsException;
use Unit\UnitAPI;

/**
 * Indicator de Classification.
 *
 * @author valentin.claras
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
     * @var ClassificationLibrary
     */
    protected $library;

    /**
     * @var string
     */
    protected $ref;

    /**
     * @var string
     */
    protected $label;

    /**
     * @var UnitAPI
     */
    protected $unit;

    /**
     * @var UnitAPI
     */
    protected $ratioUnit;


    /**
     * @param ClassificationLibrary $library
     * @param string           $ref       Identifiant textuel
     * @param string           $label     Libellé
     * @param UnitAPI          $unit      Unité de l'indicateur.
     * @param UnitAPI|null     $ratioUnit Unité utilisé pour les ratios. Si null, l'unité de l'indicateur est utilisée.
     * @throws IncompatibleUnitsException Unit ant RatioUnit should be compatible
     */
    public function __construct(ClassificationLibrary $library, $ref, $label, UnitAPI $unit, UnitAPI $ratioUnit = null)
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
     * @return ClassificationLibrary
     */
    public function getLibrary()
    {
        return $this->library;
    }

    /**
     * @param string $ref
     */
    public function setRef($ref)
    {
        $this->ref = $ref;
    }

    /**
     * @return string
     */
    public function getRef()
    {
        return $this->ref;
    }

    /**
     * @param string $label
     */
    public function setLabel($label)
    {
        $this->label = $label;
    }

    /**
     * @return string
     */
    public function getLabel()
    {
        return $this->label;
    }

    /**
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
     * @return UnitAPI
     */
    public function getUnit()
    {
        return $this->unit;
    }

    /**
     * @param UnitAPI $ratioUnit
     * @throws IncompatibleUnitsException
     */
    public function setRatioUnit(UnitAPI $ratioUnit)
    {
        if ($this->unit != null) {
            if (!$this->getUnit()->isEquivalent($ratioUnit)) {
                throw new IncompatibleUnitsException(sprintf(
                    'Unit (%s) ant RatioUnit (%s) should be compatible',
                    $this->unit,
                    $ratioUnit->getRef()
                ));
            }
        }

        $this->ratioUnit = $ratioUnit;
    }

    /**
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

    /**
     * @return array
     */
    protected function getContext()
    {
        return ['library' => $this->library];
    }
}
