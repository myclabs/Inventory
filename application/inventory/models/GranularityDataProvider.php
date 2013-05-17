<?php
/**
 * @package Inventory
 * @subpackage ModelProvider
 */

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;

/**
 * Classe faisant le lien entre les granularités de la structure Orga, les AF et le DW.
 * @author valentin.claras
 * @package Inventory
 * @subpackage ModelProvider
 */
class Inventory_Model_GranularityDataProvider extends Core_Model_Entity implements Core_Event_ObserverInterface
{
    /**
     * Identifiant unique du GranularityDataProvider.
     * @var int
     */
    protected $id = null;

    /**
     * Granularité concernée.
     *
     * @var Orga_Model_Granularity
     */
    protected $orgaGranularity = null;

    /**
     * Défini si les cellules possèdent des droits d'accès.
     *
     * @var bool
     */
    protected $cellsWithACL = false;

    /**
     * Défini si les cellules génerent un Cube de DW.
     *
     * @var bool
     */
    protected $cellsGenerateDWCubes = false;

    /**
     * Cube de DW généré par et propre à la Cell.
     *
     * @var DW_Model_Cube
     */
    protected $dWCube = null;

    /**
     * Collection des GranularityReport utilisé par ce GranularityDataProvider.
     *
     * @var Collection
     */
    private $granularityReports = null;

    /**
     * Défini si les cellules de la granularité affichent l'onglet d'Orga.
     *
     * @var bool
     */
    protected $cellsWithOrgaTab = false;

    /**
     * Défini si les cellules de la granularité affichent l'onglet de configuration des AF.
     *
     * @var bool
     */
    protected $cellsWithAFConfigTab = false;

    /**
     * Défini si les cellules de la granularité comportent des GenericAction.
     *
     * @var bool
     */
    protected $cellsWithSocialGenericActions = false;

    /**
     * Défini si les cellules de la granularité comportent des ContextAction.
     *
     * @var bool
     */
    protected $cellsWithSocialContextActions = false;

    /**
     * Défini si les cellules de la granularité contiennent des documents.
     *
     * @var bool
     */
    protected $cellsWithInputDocs = false;


    /**
     * Constructeur de la classe GranularityDataProvider.
     */
    public function __construct()
    {
        $this->granularityReports = new ArrayCollection();
    }

    /**
     * Utilisé quand un événement est lancé.
     *
     * @param string            $event
     * @param Core_Model_Entity $subject
     * @param array             $arguments
     */
    public static function applyEvent($event, $subject, $arguments = [])
    {
        switch ($event) {
            case Orga_Model_Granularity::EVENT_SAVE:
                $granularityDataProvider = new Inventory_Model_GranularityDataProvider();
                $granularityDataProvider->setOrgaGranularity($subject);
                $granularityDataProvider->save();
                break;
            case Orga_Model_Granularity::EVENT_DELETE:
                $granularityDataProvider = Inventory_Model_GranularityDataProvider::loadByOrgaGranularity($subject);
                $project = Inventory_Model_Project::loadByOrgaCube($subject->getCube());
                try {
                    if ($project->getOrgaGranularityForInventoryStatus() === $subject) {
                        throw new Core_Exception_User('Inventory', 'granularity', 'deleteGranularityInventory');
                    }
                } catch (Core_Exception_UndefinedAttribute $e) {
                    // La granularité des inventaires n'a pas été définie.
                }
                foreach ($project->getAFGranularities() as $aFGranularities) {
                    if ($aFGranularities->getAFConfigOrgaGranularity() === $subject) {
                        throw new Core_Exception_User('Inventory', 'granularity', 'deleteGranularityConfigAF');
                    }
                    if ($aFGranularities->getAFInputOrgaGranularity() === $subject) {
                        throw new Core_Exception_User('Inventory', 'granularity', 'deleteGranularityInputAF');
                    }
                }
                $granularityDataProvider->delete();
                break;
        }
    }

    /**
     * Charge le GranularityDataProvider correspondant à l'id d'une Granularity d'Orga.
     * 
     * @param Orga_Model_Granularity $orgaGranularity
     *
     * @return Inventory_Model_GranularityDataProvider
     */
    public static function loadByOrgaGranularity($orgaGranularity)
    {
        return self::getEntityRepository()->loadBy(array('orgaGranularity' => $orgaGranularity));
    }

    /**
     * Charge le GranularityDataProvider correspondant à un Cube de DW.
     *
     * @param DW_Model_Cube $dWCube
     *
     * @return Inventory_Model_GranularityDataProvider
     */
    public static function loadByDWCube($dWCube)
    {
        return self::getEntityRepository()->loadBy(array('dWCube' => $dWCube));
    }

    /**
     * Spécifie la Granularity d'Orga.
     * 
     * @param Orga_Model_Granularity $orgaGranularity
     */
    public function setOrgaGranularity(Orga_Model_Granularity $orgaGranularity)
    {
        if ($this->orgaGranularity !== $orgaGranularity) {
            if ($this->orgaGranularity !== null) {
                throw new Core_Exception_Duplicate(
                    "Impossible de redéfinir la Granularity d'Orga, elle a déjà été définie"
                );
            }
            $this->orgaGranularity = $orgaGranularity;
        }
    }

    /**
     * Renvoie la Granularity d'Orga concernée.
     *
     * @return Orga_Model_Granularity
     */
    public function getOrgaGranularity()
    {
        if ($this->orgaGranularity === null) {
            throw new Core_Exception_UndefinedAttribute("La Granularity d'Orga n'a pas été définie");
        }
        return $this->orgaGranularity;
    }

    /**
     * Défini si les cellules de la granularité possèdent des droits d'accès.
     *
     * @param bool $bool
     */
    public function setCellsWithACL($bool)
    {
        $this->cellsWithACL = $bool;
    }

    /**
     * Indique si les cellules de la granularité possèdent des droits d'accès.
     *
     * @return bool
     */
    public function getCellsWithACL()
    {
        return $this->cellsWithACL;
    }

    /**
     * Défini si les cellules de la granularité génereront des cubes de DW.
     *
     * @param bool $bool
     */
    public function setCellsGenerateDWCubes($bool)
    {
        $this->cellsGenerateDWCubes = $bool;
        if ($this->cellsGenerateDWCubes === true) {
            $this->createDWCube();
        } else {
            $this->deleteDWCube();
        }
        foreach ($this->getOrgaGranularity()->getCells() as $orgaCell) {
            $cellDataProvider = Inventory_Model_CellDataProvider::loadByOrgaCell($orgaCell);
            if ($this->cellsGenerateDWCubes === true) {
                $cellDataProvider->createDWCube();
                $cellDataProvider->save();
            } else {
                $cellDataProvider->deleteDWCube();
            }
        }
        $this->save();
    }

    /**
     * Indique si les cellules de la granularité génerent des cubes de DW.
     *
     * @return bool
     */
    public function getCellsGenerateDWCubes()
    {
        return $this->cellsGenerateDWCubes;
    }

    /**
     * Créé le Cube pour la simulation.
     *
     * @return int Identifiant unique du Cube.
     */
    public function createDWCube()
    {
        if ($this->dWCube === null) {
            $this->dWCube = new DW_Model_Cube();
            $this->dWCube->setLabel($this->getOrgaGranularity()->getLabel());

            Inventory_Service_ETLStructure::getInstance()->populateGranularityDataProviderDWCube($this);
        }
    }

    /**
     * Créé le Cube pour la simulation.
     *
     * @return int Identifiant unique du Cube.
     */
    public function deleteDWCube()
    {
        if ($this->dWCube !== null) {
            $this->dWCube->delete();
            $this->dWCube = null;
        }
    }

    /**
     * Renvoi le Cube de DW spécifique à la Cell.
     *
     * @return DW_Model_Cube
     */
    public function getDWCube()
    {
        if ($this->dWCube === null) {
            throw new Core_Exception_UndefinedAttribute('La Granularity de la Cell ne génère pas de DWCube');
        }
        return $this->dWCube;
    }

    /**
     * Ajoute un GranularityReport GranularityDataProvider.
     *
     * @param Inventory_Model_GranularityReport $granularityReport
     */
    public function addGranularityReport(Inventory_Model_GranularityReport $granularityReport)
    {
        if (!($this->hasGranularityReport($granularityReport))) {
            $this->granularityReports->add($granularityReport);
            $granularityReport->setGranularityDataProvider($this);
        }
    }

    /**
     * Vérifie si le GranularityDataprovider possède le GranularityReport donné.
     *
     * @param Inventory_Model_GranularityReport $granularityReport
     *
     * @return boolean
     */
    public function hasGranularityReport(Inventory_Model_GranularityReport $granularityReport)
    {
        return $this->granularityReports->contains($granularityReport);
    }

    /**
     * Retire le GranularityReport donné des GranularityReport du GranularityDataProvider.
     *
     * @param Inventory_Model_GranularityReport $granularityReport
     */
    public function removeGranularityReport($granularityReport)
    {
        if ($this->hasGranularityReport($granularityReport)) {
            $this->granularityReports->removeElement($granularityReport);
        }
    }

    /**
     * Vérifie que le GranularityDataProvider possède au moins un GranularityReport.
     *
     * @return bool
     */
    public function hasGranularityReports()
    {
        return !$this->granularityReports->isEmpty();
    }

    /**
     * Renvoie un tableau des GranularityReport du GranularityDataProvider.
     *
     * @return Inventory_Model_GranularityReport[]
     */
    public function getGranularityReports()
    {
        return $this->granularityReports->toArray();
    }

    /**
     * Défini si les cellules de la granularité afficheront le tab d'Orga.
     *
     * @param bool $bool
     */
    public function setCellsWithOrgaTab($bool)
    {
        $this->cellsWithOrgaTab = $bool;
    }

    /**
     * Indique si les cellules de la granularité affichent le tab d'Orga.
     *
     * @return bool
     */
    public function getCellsWithOrgaTab()
    {
        return $this->cellsWithOrgaTab;
    }

    /**
     * Défini si les cellules de la granularité afficheront le tab de configuration d'AF.
     *
     * @param bool $bool
     */
    public function setCellsWithAFConfigTab($bool)
    {
        $this->cellsWithAFConfigTab = $bool;
    }

    /**
     * Indique si les cellules de la granularité affichent le tab de configuration d'AF.
     *
     * @return bool
     */
    public function getCellsWithAFConfigTab()
    {
        return $this->cellsWithAFConfigTab;
    }

    /**
     * Défini si les cellules de la granularité posséderont des GenericAction de Social.
     *
     * @param bool $bool
     */
    public function setCellsWithSocialGenericActions($bool)
    {
        if ($this->cellsWithSocialGenericActions !== $bool) {
            if ($bool === false) {
                /** @var Inventory_Model_CellDataProvider[] $cellDataProviders */
                $cellDataProviders = [];
                foreach ($this->getOrgaGranularity()->getCells() as $orgaCell) {
                    $cellDataProvider = Inventory_Model_CellDataProvider::loadByOrgaCell($orgaCell);
                    if ($cellDataProvider->getDocLibraryForSocialGenericAction()->hasDocuments()) {
                        throw new Core_Exception_User('Inventory', 'exception', 'changeCellsWithSocialGenericActions');
                    }
                    $cellDataProviders[] = $cellDataProvider;
                }
                foreach ($cellDataProviders as $cellDataProvider) {
                    $cellDataProvider->getDocLibraryForSocialGenericAction()->delete();
                    $cellDataProvider->setDocLibraryForSocialGenericAction(null);
                }
            } else  {
                foreach ($this->getOrgaGranularity()->getCells() as $orgaCell) {
                    $cellDataProvider = Inventory_Model_CellDataProvider::loadByOrgaCell($orgaCell);
                    $cellDataProvider->setDocLibraryForSocialGenericAction(new Doc_Model_Library());
                }
            }
            $this->cellsWithSocialGenericActions = $bool;
        }
    }

    /**
     * Indique si les cellules de la granularité possédent des GenericAction de Social.
     *
     * @return bool
     */
    public function getCellsWithSocialGenericActions()
    {
        return $this->cellsWithSocialGenericActions;
    }

    /**
     * Défini si les cellules de la granularité posséderont des GenericAction de Social.
     *
     * @param bool $bool
     */
    public function setCellsWithSocialContextActions($bool)
    {
        if ($this->cellsWithSocialContextActions !== $bool) {
            if ($bool === false) {
                /** @var Inventory_Model_CellDataProvider[] $cellDataProviders */
                $cellDataProviders = [];
                foreach ($this->getOrgaGranularity()->getCells() as $orgaCell) {
                    $cellDataProvider = Inventory_Model_CellDataProvider::loadByOrgaCell($orgaCell);
                    if ($cellDataProvider->getDocLibraryForSocialContextAction()->hasDocuments()) {
                        throw new Core_Exception_User('Inventory', 'exception', 'changeCellsWithSocialContextActions');
                    }
                    $cellDataProviders[] = $cellDataProvider;
                }
                foreach ($cellDataProviders as $cellDataProvider) {
                    $cellDataProvider->getDocLibraryForSocialContextAction()->delete();
                    $cellDataProvider->setDocLibraryForSocialContextAction(null);
                }
            } else  {
                foreach ($this->getOrgaGranularity()->getCells() as $orgaCell) {
                    $cellDataProvider = Inventory_Model_CellDataProvider::loadByOrgaCell($orgaCell);
                    $cellDataProvider->setDocLibraryForSocialContextAction(new Doc_Model_Library());
                }
            }
            $this->cellsWithSocialContextActions = $bool;
        }
    }

    /**
     * Indique si les cellules de la granularité possédent des ContextAction de Social.
     *
     * @return bool
     */
    public function getCellsWithSocialContextActions()
    {
        return $this->cellsWithSocialContextActions;
    }

    /**
     * Défini si les cellules de la granularité possèderont des Doc pour l'InputSetPrimary.
     *
     * @param bool $bool
     */
    public function setCellsWithInputDocs($bool)
    {
        if ($this->cellsWithInputDocs !== $bool) {
            if ($bool === false) {
                /** @var Inventory_Model_CellDataProvider[] $cellDataProviders */
                $cellDataProviders = [];
                foreach ($this->getOrgaGranularity()->getCells() as $orgaCell) {
                    $cellDataProvider = Inventory_Model_CellDataProvider::loadByOrgaCell($orgaCell);
                    if ($cellDataProvider->getDocLibraryForAFInputSetsPrimary()->hasDocuments()) {
                        throw new Core_Exception_User('Inventory', 'exception', 'changeCellsWithInputDocs');
                    }
                    $cellDataProviders[] = $cellDataProvider;
                }
                foreach ($cellDataProviders as $cellDataProvider) {
                    $cellDataProvider->getDocLibraryForAFInputSetsPrimary()->delete();
                    $cellDataProvider->setDocLibraryForAFInputSetsPrimary(null);
                }
            } else  {
                foreach ($this->getOrgaGranularity()->getCells() as $orgaCell) {
                    $cellDataProvider = Inventory_Model_CellDataProvider::loadByOrgaCell($orgaCell);
                    $cellDataProvider->setDocLibraryForAFInputSetsPrimary(new Doc_Model_Library());
                }
            }
            $this->cellsWithInputDocs = $bool;
        }
    }

    /**
     * Indique si les cellules de la granularité possèdent des Doc pour l'InputSetPrimary.
     *
     * @return bool
     */
    public function getCellsWithInputDocs()
    {
        return $this->cellsWithInputDocs;
    }
}