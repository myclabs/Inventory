<?php

namespace Orga\Domain;

use AF\Domain\AF;
use Core_Exception_UndefinedAttribute;
use Core_Model_Entity;
use Orga\Domain\Granularity;
use Orga\Domain\Cell;

/**
 * SubCellsGroup.
 *
 * @author valentin.claras
 */
class SubCellsGroup extends Core_Model_Entity
{
    // Constantes de tri et de filtre.
    const QUERY_GRANULARITY = 'inputGranularity';
    const QUERY_CELL = 'containerCell';

    /**
     * @var int
     */
    protected $id = null;

    /**
     * @var Cell
     */
    protected $containerCell = null;

    /**
     * @var Granularity
     */
    protected $inputGranularity = null;

    /**
     * @var AF
     */
    protected $aF = null;


    public function __construct(Cell $containerCell, Granularity $inputGranularity)
    {
        $this->containerCell = $containerCell;
        $this->containerCell->addSubCellsGroup($this);
        $this->inputGranularity = $inputGranularity;
    }

    /**
     * Fonction appelée avant un delete de l'objet (défini dans le mapper).
     */
    public function preDelete()
    {
        $this->getContainerCell()->removeSubCellsGroup($this);
    }

    /**
     * Renvoie l'id du CellGroup.
     *
     * @return string
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Renvoie le Cell container utilisé.
     *
     * @return Cell
     */
    public function getContainerCell()
    {
        return $this->containerCell;
    }

    /**
     * Renvoie la Granularity utilisé comme groupement sous la cellule container.
     *
     * @return Granularity
     */
    public function getInputGranularity()
    {
        return $this->inputGranularity;
    }

    /**
     * Spécifie l'AF utilisé par le groupement de cellule.
     *
     * @param AF $aF
     */
    public function setAF(AF $aF = null)
    {
        if ($this->aF !== $aF) {
            $this->aF = $aF;
            $containerCell = $this->getContainerCell();
            if ($containerCell->getGranularity() === $this->getInputGranularity()) {
                $containerCell->updateInputStatus();
            } else {
                foreach ($containerCell->getChildCellsForGranularity($this->getInputGranularity()) as $inputCell) {
                    $inputCell->updateInputStatus();
                }
            }
        }
    }

    /**
     * Renvoie l'AF utilisé par le groupement de cellule.
     *
     * @throws Core_Exception_UndefinedAttribute
     *
     * @return AF
     */
    public function getAF()
    {
        if ($this->aF === null) {
            throw new Core_Exception_UndefinedAttribute("L'AF du groupement du cellule n'a pas été défini");
        }
        return $this->aF;
    }
}
