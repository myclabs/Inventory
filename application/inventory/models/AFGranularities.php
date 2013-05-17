<?php
/**
 * @package Inventory
 * @subpackage ModelProvider
 */
/**
 * Classe indiquant une paire de granularité : configuration et utilisation.
 * @author valentin.claras
 * @package Inventory
 * @subpackage ModelProvider
 */
class Inventory_Model_AFGranularities extends Core_Model_Entity
{
    // Constantes de tri et de filtre.
    const QUERY_AFCONFIGGRANULARITY = 'aFConfigOrgaGranularity';

    /**
     * Identifiant unique du Project.
     *
     * @var int
     */
    protected $id = null;

    /**
     * Project utilisant cette configuration.
     *
     * @var Inventory_Model_Project
     */
    protected $project = null;

    /**
     * Granularity de configuration des AF.
     *
     * @var Orga_Model_Granularity
     */
    protected $aFConfigOrgaGranularity = null;

    /**
     * Granularity de saisie des AF.
     *
     * @var Orga_Model_Granularity
     */
    protected $aFInputOrgaGranularity = null;

    /**
     * Collection des CellsGroupDataProvider utilisant ce GranularityDataProvider.
     *
     * @var Doctrine\Common\Collections\ArrayCollection
     */
    protected $cellsGroupDataProviders = null;


    /**
     * Constructeur de la classe AFGranularities.
     */
    public function __construct()
    {
        $this->cellsGroupDataProviders = new Doctrine\Common\Collections\ArrayCollection();
    }
    
    /**
     * Charge l'AFGranularities correspondant à une granularité de saisie.
     *
     * @param Orga_Model_Granularity $aFInputOrgaGranularity
     *
     * @return Inventory_Model_AFGranularities
     */
    public static function loadByAFInputOrgaGranularity($aFInputOrgaGranularity)
    {
        return self::getEntityRepository()->loadBy(array('aFInputOrgaGranularity' => $aFInputOrgaGranularity));
    }

    /**
     * Spécifie le Project utilisant pour configurer les AF.
     *
     * @param Inventory_Model_Project $project
     */
    public function setProject(Inventory_Model_Project $project)
    {
        if ($this->project !== $project) {
            if ($this->project !== null) {
                throw new Core_Exception_Duplicate(
                    'Impossible de redéfinir la Granularity de configuration des AF, elle a déjà été défini.'
                );
            }
            $this->project = $project;
            $project->addAFGranularities($this);
        }
    }

    /**
     * Renvoie le Project utilisant cette configuration des AF.
     *
     * @return Inventory_Model_Project
     */
    public function getProject()
    {
        if ($this->project === null) {
            throw new Core_Exception_UndefinedAttribute(
                'Le Project utilisant cette AFGranularities n\'a pas été défini.'
            );
        }
        return $this->project;
    }

    /**
     * Spécifie la Granularity d'Orga utilisée pour configurer les AF.
     *
     * @param Orga_Model_Granularity $orgaGranularity
     */
    public function setAFConfigOrgaGranularity(Orga_Model_Granularity $orgaGranularity)
    {
        if ($this->aFConfigOrgaGranularity !== $orgaGranularity) {
            if ($this->aFConfigOrgaGranularity !== null) {
                throw new Core_Exception_Duplicate(
                    'Impossible de redéfinir la Granularity de configuration des AF, elle a déjà été défini.'
                );
            }
            if (($this->aFInputOrgaGranularity !== null)
                && (!($orgaGranularity->isBroaderThan($this->aFInputOrgaGranularity)))) {
                throw new Core_Exception_InvalidArgument(
                    'La Granularity de configuration des AF doit être broader que la granularité de saisie.'
                );
            }
            $this->aFConfigOrgaGranularity = $orgaGranularity;
        }
    }

    /**
     * Renvoie la Granularity d'Orga utilisée pour la configuration des AF.
     *
     * @return Orga_Model_Granularity
     */
    public function getAFConfigOrgaGranularity()
    {
        if ($this->aFConfigOrgaGranularity === null) {
            throw new Core_Exception_UndefinedAttribute(
                'La Granularity de configuration des AF n\'a pas été défini.'
            );
        }
        return $this->aFConfigOrgaGranularity;
    }

    /**
     * Spécifie la Granularity d'Orga utilisée pour saisir les AF.
     *
     * @param Orga_Model_Granularity $orgaGranularity
     */
    public function setAFInputOrgaGranularity(Orga_Model_Granularity $orgaGranularity)
    {
        if ($this->aFInputOrgaGranularity !== $orgaGranularity) {
            if ($this->aFInputOrgaGranularity !== null) {
                throw new Core_Exception_Duplicate(
                    'Impossible de redéfinir la Granularity de saisie des AF, elle a déjà été défini.'
                );
            }
            if (($this->aFConfigOrgaGranularity !== null)
                && (!($orgaGranularity->isNarrowerThan($this->aFConfigOrgaGranularity)))) {
                throw new Core_Exception_InvalidArgument(
                    'La Granularity de saisie des AF doit être narrower que la granularité de configuration.'
                );
            }
            if (!($this->getProject()->getOrgaGranularityForInventoryStatus()->isBroaderThan($orgaGranularity))) {
                throw new Core_Exception_InvalidArgument(
                    'La Granularity de saisie des AF doit être narrower que la granularité des inventaires du project.'
                );
            }
            $this->aFInputOrgaGranularity = $orgaGranularity;
        }
    }

    /**
     * Renvoie la Granularity d'Orga utilisée pour la saisie des AF.
     *
     * @return Orga_Model_Granularity
     */
    public function getAFInputOrgaGranularity()
    {
        if ($this->aFInputOrgaGranularity === null) {
            throw new Core_Exception_UndefinedAttribute(
                'La Granularity de saisie des AF n\'a pas été défini.'
            );
        }
        return $this->aFInputOrgaGranularity;
    }

    /**
     * Vérifie si l'AFGranularities possède le CellsGroupDataProvider passée en paramètre.
     *
     * @param Inventory_Model_CellsGroupDataProvider $cellsGroupDataProvider
     *
     * @return bool
     */
    public function hasCellsGroupDataProvider(Inventory_Model_CellsGroupDataProvider $cellsGroupDataProvider)
    {
        return $this->cellsGroupDataProviders->contains($cellsGroupDataProvider);
    }

    /**
     * Ajoute une CellsGroupDataProvider à l'AFGranularities.
     *
     * @param Inventory_Model_CellsGroupDataProvider $cellsGroupDataProvider
     */
    public function addCellsGroupDataProvider(Inventory_Model_CellsGroupDataProvider $cellsGroupDataProvider)
    {
        if (!($this->hasCellsGroupDataProvider($cellsGroupDataProvider))) {
            $this->cellsGroupDataProviders->add($cellsGroupDataProvider);
            $cellsGroupDataProvider->setAFGranularities($this);
        }
    }

    /**
     * Retire une CellsGroupDataProvider de l'AFGranularities.
     *
     * @param Inventory_Model_CellsGroupDataProvider $cellsGroupDataProvider
     */
    public function deleteCellsGroupDataProvider(Inventory_Model_CellsGroupDataProvider $cellsGroupDataProvider)
    {
        if ($this->hasCellsGroupDataProvider($cellsGroupDataProvider)) {
            $this->cellsGroupDataProviders->removeElement($cellsGroupDataProvider);
            $cellsGroupDataProvider->delete();
        }
    }

    /**
     * Vérifie si l'AFGranularities possède au moins une CellsGroupDataProvider.
     *
     * @return bool
     */
    public function hasCellsGroupDataProviders()
    {
        return !$this->cellsGroupDataProviders->isEmpty();
    }

    /**
     * Renvoi l'ensemble des CellsGroupDataProvider de l'AFGranularities.
     *
     * @return Inventory_Model_CellsGroupDataProvider[]
     */
    public function getCellsGroupDataProvider()
    {
        return $this->cellsGroupDataProviders->toArray();
    }

    /**
     * Renvoi l'ensemble des CellsGroupDataProvider de l'AFGranularities.
     *
     * @param Inventory_Model_CellDataProvider $cellDataProvider
     *
     * @return Inventory_Model_CellsGroupDataProvider
     */
    public function getCellsGroupDataProviderForContainerCellDataProvider($cellDataProvider)
    {
        $criteria = Doctrine\Common\Collections\Criteria::create()->where(
            Doctrine\Common\Collections\Criteria::expr()->eq('containerCellDataProvider', $cellDataProvider)
        );
        $cellsGroupDataProvider = $this->cellsGroupDataProviders->matching($criteria)->toArray();

        if (empty($cellsGroupDataProvider)) {
            throw new Core_Exception_NotFound('Aucun CellsGroupDataProvider correspondant au CellDataProvider');
        }

        return $cellsGroupDataProvider[0];
    }

}
