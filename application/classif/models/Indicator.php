<?php
/**
 * Classe Classif_Model_Indicator
 *
 * @author     valentin.claras
 * @author     cyril.perraud
 * @package    Classif
 * @subpackage Model
 */

/**
 * Permet de gérer un indicateur.
 *
 * @package    Classif
 * @subpackage Model
 */
class Classif_Model_Indicator extends Core_Model_Entity
{

    use Core_Strategy_Ordered;

    // Constantes de tris et de filtres.
    const QUERY_REF = 'ref';
    const QUERY_LABEL = 'label';
    const QUERY_UNIT = 'unit';
    const QUERY_POSITION = 'position';


    /**
     * Identifiant de l'indicateur.
     *
     * @var int
     */
    protected $id;

    /**
     * Référent textuel de l'indicateur.
     *
     * @var String
     */
    protected $ref;

    /**
     * Label de l'indicateur.
     *
     * @var String
     */
    protected $label;

    /**
     * Unité dans laquelle est l'indicateur.
     *
     * @var Unit_API
     */
    protected $unit;

    /**
     * Unité utilisé pour les ratios.
     *
     * @var Unit_API
     */
    protected $ratioUnit;


    /**
     * Fonction appelé avant un persist de l'objet (défini dans le mapper).
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
     * Fonction appelé avant un update de l'objet (défini dans le mapper).
     */
    public function preUpdate()
    {
        $this->checkHasPosition();
    }

    /**
     * Fonction appelé avant un delete de l'objet (défini dans le mapper).
     */
    public function preDelete()
    {
        $this->deletePosition();
    }

    /**
     * Fonction appelé après un load de l'objet (défini dans le mapper).
     */
    public function postLoad()
    {
        $this->updateCachePosition();
    }

    /**
     * Retourne un indicateur à partir de son ref
     * @param string $ref
     * @return Classif_Model_Indicator $indicator
     */
    public static function loadByRef($ref)
    {
        return self::getEntityRepository()->loadBy(array('ref' => $ref));
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
     * @param Unit_API $unit
     */
    public function setUnit($unit)
    {
        $this->unit = $unit;
    }

    /**
     * Retourne l'unit de l'indicateur.
     *
     * @return Unit_API
     */
    public function getUnit()
    {
        return new Unit_API($this->unit);
    }

    /**
     * Modifie l'unité de ratio de l'indicateur.
     *
     * @param Unit_API $ratioUnit
     */
    public function setRatioUnit($ratioUnit)
    {
        $this->ratioUnit = $ratioUnit;
    }

    /**
     * Retourne l'unité de ratio de l'indicateur
     *
     * @return Unit_API
     */
    public function getRatioUnit()
    {
        return new Unit_API($this->ratioUnit);
    }

    /**
     * Représentation de l'instance.
     *
     * @return string
     */
    public function __toString()
    {
        return $this->ref;
    }

}
