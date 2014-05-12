<?php
/**
 * Classe DW_Model_Indicator
 * @author valentin.claras
 * @author cyril.perraud
 * @package    DW
 * @subpackage Model
 */

use Core\Translation\TranslatedString;
use Mnapoli\Translated\TranslatedStringInterface;
use Unit\UnitAPI;

/**
 * Objet métier Indicateur.
 * @package    DW
 * @subpackage Model
 */
class DW_Model_Indicator extends Core_Model_Entity
{
    use Core_Strategy_Ordered;

    // Constantes de tris et de filtres.
    const QUERY_REF = 'ref';
    const QUERY_LABEL = 'label';
    const QUERY_POSITION = 'position';
    const QUERY_CUBE = 'cube';


    /**
     * Identifiant unique de l'Indicator.
     *
     * @var int
     */
    protected $id = null;

    /**
     * Référence unique (au sein d'un cube) de l'Indicator.
     *
     * @var string
     */
    protected  $ref = null;

    /**
     * Label de l'Indicator.
     *
     * @var TranslatedString
     */
    protected $label = null;

    /**
     * Cube contenant l'Indicator.
     *
     * @var DW_Model_Cube
     */
    protected $cube = null;

    /**
     * Unité dans laquelle est l'Indicator.
     *
     * @var UnitAPI
     */
    protected  $unit;

    /**
     * Unité utilisé pour les ratios.
     *
     * @var UnitAPI
     */
    protected $ratioUnit;


    /**
     * Constructeur de la classe Indicator.
     */
    public function __construct(DW_Model_Cube $cube)
    {
        $this->cube = $cube;
        $this->setPosition();
        $this->cube->addIndicator($this);
    }

    /**
     * Renvoi les valeurs du contexte pour l'objet.
     * .
     * @return array
     */
    protected function getContext()
    {
        return array('cube' => $this->cube);
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
     * Charge une Indicator en fonction de sa référence.
     *
     * @param string $ref
     * @param DW_Model_Cube $cube
     *
     * @return DW_Model_Indicator
     */
    public static function loadByRefAndCube($ref, DW_Model_Cube $cube)
    {
        return $cube->getIndicatorByRef($ref);
    }

    /**
     * Renvoie l'id de l'Indicator.
     *
     * @return string
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Modifie le ref de l'Indicator.
     *
     * @param string $ref
     */
    public function setRef($ref)
    {
        $this->ref = $ref;
    }

    /**
     * Retourne le ref de l'Indicator.
     *
     * @return string
     */
    public function getRef()
    {
        return $this->ref;
    }

    /**
     * Modifie le label de l'Indicator.
     *
     * @param TranslatedString $label
     */
    public function setLabel(TranslatedString $label)
    {
        $this->label = $label;
    }

    /**
     * Retourne le label de l'Indicator.
     *
     * @return TranslatedStringInterface
     */
    public function getLabel()
    {
        return $this->label;
    }

    /**
     * Renvoie le Cube de l'Indicator.
     *
     * @return DW_Model_Cube
     */
    public function getCube()
    {
        return $this->cube;
    }

    /**
     * Modifie l'unit de l'indicateur.
     *
     * @param UnitAPI $unit
     */
    public function setUnit($unit)
    {
        $this->unit = $unit;
    }

    /**
     * Retourne l'unit de l'indicateur.
     *
     * @return UnitAPI
     */
    public function getUnit()
    {
        return new UnitAPI($this->unit);
    }

    /**
     * Modifie l'unité de ratio de l'indicateur.
     *
     * @param UnitAPI $ratioUnit
     */
    public function setRatioUnit($ratioUnit)
    {
        $this->ratioUnit = $ratioUnit;
    }

    /**
     * Retourne l'unité de ratio de l'indicateur
     *
     * @return UnitAPI
     */
    public function getRatioUnit()
    {
        return new UnitAPI($this->ratioUnit);
    }

}
