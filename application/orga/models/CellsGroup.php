<?php
/**
 * @package Orga
 * @subpackage Model
 */

/**
 * Classe permettant de choisir les AF associés aux cellules enfant d'une Cell et d'une Granularity données.
 * @author valentin.claras
 * @package Orga
 * @subpackage Model
 */
class Orga_Model_CellsGroup extends Core_Model_Entity
{
    // Constantes de tri et de filtre.
    const QUERY_GRANULARITY = 'inputGranularity';
    const QUERY_CELL = 'containerCell';

    /**
     * Identifiant unique du CellsGroup.
     *
     * @var int
     */
    protected $id = null;

    /**
     * Cell ambiante qui déterminera les AF d'une granularité.
     *
     * @var Orga_Model_Cell
     */
    protected $containerCell = null;

    /**
     * Granularity de saisie pour laquelle  les Cell enfants utiliseront l'AF.
     * 	.
     * @var Orga_Model_Granularity
     */
    protected $inputGranularity = null;

    /**
     * AF choisi pour les cellules enfants de la containerCell pour l'inputGranularity.
     *
     * @var AF_Model_AF
     */
    protected $aF = null;


    /**
     * Constructeur de la classe CellsGroup.
     *
     * @param Orga_Model_Cell $containerCell
     * @param Orga_Model_Granularity $inputGranularity
     */
    public function __construct(Orga_Model_Cell $containerCell, Orga_Model_Granularity $inputGranularity)
    {
        $this->containerCell = $containerCell;
        $containerCell->addCellsGroup($this);
        $this->inputGranularity = $inputGranularity;
    }

    /**
     * Fonction appelé avant un delete de l'objet (défini dans le mapper).
     */
    public function preDelete()
    {
        $this->getContainerCell()->removeCellsGroup($this);
    }

    /**
     * Renvoie le Cell container utilisé.
     *
     * @return Orga_Model_Cell
     */
    public function getContainerCell()
    {
        return $this->containerCell;
    }

    /**
     * Renvoie la Granularity utilisé comme groupement sous la cellule container.
     *
     * @return Orga_Model_Granularity
     */
    public function getInputGranularity()
    {
        return $this->inputGranularity;
    }

    /**
     * Spécifie l'AF utilisé par le groupement de cellule.
     *
     * @param AF_Model_AF $aF
     */
    public function setAF(AF_Model_AF $aF)
    {
        if ($this->aF !== $aF) {
            $this->aF = $aF;
        }
    }

    /**
     * Renvoie l'AF utilisé par le groupement de cellule.
     *
     * @throws Core_Exception_UndefinedAttribute
     *
     * @return AF_Model_AF
     */
    public function getAF()
    {
        if ($this->aF === null) {
            throw new Core_Exception_UndefinedAttribute("L'AF du groupement du cellule n'a pas été défini");
        }
        return $this->aF;
    }

}