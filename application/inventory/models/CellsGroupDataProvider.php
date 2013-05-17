<?php
/**
 * @package Inventory
 * @subpackage ModelProvider
 */
/**
 * Classe permettant de choisir les AF associés aux cellules d'un groupe de cellule.
 * @author valentin.claras
 * @package Inventory
 * @subpackage ModelProvider
 */
class Inventory_Model_CellsGroupDataProvider extends Core_Model_Entity
{
    // Constantes de tri et de filtre.
    const QUERY_GRANULARITYDATAPROVIDER = 'granularityDataProvider';
    const QUERY_CELLDATAPROVIDER = 'containerCellDataProvider';

    /**
     * Identifiant unique du Project.
     *
     * @var int
     */
    protected $id = null;

    /**
     * CellDataProvider ambiante qui déterminera les AF d'une granularité.
     *
     * @var Inventory_Model_CellDataProvider
     */
    protected $containerCellDataProvider = null;

    /**
     * AFGranularities (groupe de cellule) utilisant les même AF.
     * 	.
     * @var Inventory_Model_AFGranularities
     */
    protected $aFGranularities = null;

    /**
     * AF choisi pour le groupement de cellules parmi celles de la cellule ambiante.
     *
     * @var AF_Model_AF
     */
    protected $aF = null;


    /**
     * Spécifie la ContainerCell d'Inventory utilisée.
     *
     * @param Inventory_Model_CellDataProvider $containerCellDataProvider
     */
    public function setContainerCellDataProvider(Inventory_Model_CellDataProvider $containerCellDataProvider)
    {
        if ($this->containerCellDataProvider !== $containerCellDataProvider) {
            if ($this->containerCellDataProvider !== null) {
                throw new Core_Exception_Duplicate(
                    'Impossible de redéfinir la ContainerCell du groupement, elle a déjà été défini.'
                );
            }
            $this->containerCellDataProvider = $containerCellDataProvider;
        }
    }

    /**
     * Renvoie le CellDataProvider container utilisé.
     *
     * @return Inventory_Model_CellDataProvider
     */
    public function getContainerCellDataProvider()
    {
        if ($this->containerCellDataProvider === null) {
            throw new Core_Exception_UndefinedAttribute(
                'La ContainerCell du groupement n\'a pas été défini.'
            );
        }
        return $this->containerCellDataProvider;
    }

    /**
     * Spécifie l\'AFGranularities d'Inventory utilisé comme groupement sous la cellule container.
     *
     * @param Inventory_Model_AFGranularities $aFGranularities
     */
    public function setAFGranularities(Inventory_Model_AFGranularities $aFGranularities)
    {
        if ($this->aFGranularities !== $aFGranularities) {
            if ($this->aFGranularities !== null) {
                throw new Core_Exception_Duplicate(
                    'Impossible de redéfinir l\'AFGranularities d\'Inventory, elle a déjà été défini.'
                );
            }
            $this->aFGranularities = $aFGranularities;
            $aFGranularities->addCellsGroupDataProvider($this);
        }
    }

    /**
     * Renvoie l'instance d\'AFGranularities d'Inventory utilisé comme groupement sous la cellule container.
     *
     * @return Inventory_Model_AFGranularities
     */
    public function getAFGranularities()
    {
        if ($this->aFGranularities === null) {
            throw new Core_Exception_UndefinedAttribute(
                'La Granularity d\'Inventory n\'a pas été défini.'
            );
        }
        return $this->aFGranularities;
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
     * @return AF_Model_AF
     */
    public function getAF()
    {
        if ($this->aF === null) {
            throw new Core_Exception_UndefinedAttribute(
                'L\'AF du groupement du cellule n\'a pas été défini.'
            );
        }
        return $this->aF;
    }

}