<?php
/**
 * @package Social
 */
use Unit\UnitAPI;

/**
 * @author  joseph.rouffet
 * @author  matthieu.napoli
 * @package Social
 */
class Social_Model_ActionKeyFigure extends Core_Model_Entity
{

    use Core_Strategy_Ordered;

    /**
     * @var int
     */
    protected $id;

    /**
     * @var string
     */
    protected $label;

    /**
     * Unité associé
     * @var string (ref)
     */
    protected $unitRef;


    /**
     * @param UnitAPI $unit
     * @param string $label
     */
    public function __construct(UnitAPI $unit, $label)
    {
        $this->setUnit($unit);
        $this->setLabel($label);
    }

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @return string
     */
    public function getLabel()
    {
        return $this->label;
    }

    /**
     * @param string $label
     */
    public function setLabel($label)
    {
        $this->label = (string) $label;
    }

    /**
     * @return UnitAPI
     */
    public function getUnit()
    {
        return new UnitAPI($this->unitRef);
    }

    /**
     * @param UnitAPI $unit
     */
    public function setUnit(UnitAPI $unit)
    {
        $this->unitRef = $unit->getRef();
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
