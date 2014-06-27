<?php
/**
 * @author valentin.claras
 */

namespace DW\Domain;

use Core\Translation\TranslatedString;
use Core_Model_Entity;
use Core_Strategy_Ordered;
use Unit\UnitAPI;

/**
 * @package    DW
 * @subpackage Domain
 */
class Indicator extends Core_Model_Entity
{
    use Core_Strategy_Ordered;

    // Constantes de tris et de filtres.
    const QUERY_REF = 'ref';
    const QUERY_LABEL = 'label';
    const QUERY_POSITION = 'position';
    const QUERY_CUBE = 'cube';


    /**
     * @var int
     */
    protected $id = null;

    /**
     * @var string
     */
    protected $ref = null;

    /**
     * @var TranslatedString
     */
    protected $label = null;

    /**
     * @var Cube
     */
    protected $cube = null;

    /**
     * @var UnitAPI
     */
    protected $unit;

    /**
     * @var UnitAPI
     */
    protected $ratioUnit;


    public function __construct(Cube $cube)
    {
        $this->cube = $cube;
        $this->setPosition();
        $this->cube->addIndicator($this);
    }

    /**
     * @return array
     */
    protected function getContext()
    {
        return ['cube' => $this->cube];
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
     * @return string
     */
    public function getId()
    {
        return $this->id;
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
     * @param TranslatedString $label
     */
    public function setLabel(TranslatedString $label)
    {
        $this->label = $label;
    }

    /**
     * @return TranslatedString
     */
    public function getLabel()
    {
        return $this->label;
    }

    /**
     * @return Cube
     */
    public function getCube()
    {
        return $this->cube;
    }

    /**
     * @param UnitAPI $unit
     */
    public function setUnit(UnitAPI $unit)
    {
        $this->unit = $unit->getRef();
    }

    /**
     * @return UnitAPI
     */
    public function getUnit()
    {
        return new UnitAPI($this->unit);
    }

    /**
     * @param UnitAPI $ratioUnit
     */
    public function setRatioUnit(UnitAPI $ratioUnit)
    {
        $this->ratioUnit = $ratioUnit->getRef();
    }

    /**
     * @return UnitAPI
     */
    public function getRatioUnit()
    {
        return new UnitAPI($this->ratioUnit);
    }

}
