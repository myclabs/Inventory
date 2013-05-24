<?php
/**
 * @package Inventory
 * @subpackage ModelProvider
 */

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;

/**
 * Classe permettant de regrouper l'ensemble de la configuration et des données d'un projet.
 * @author valentin.claras
 * @package Inventory
 * @subpackage ModelProvider
 */
class Inventory_Model_Project extends Core_Model_Entity
{

    use Core_Model_Entity_Translatable;

    /**
     * Identifiant unique du Project.
     *
     * @var int
     */
    protected $id = null;

    /**
     * Cube organisationnel du projet.
     *
     * @var Orga_Model_Cube
     */
    protected $orgaCube = null;

    /**
     * Label du Project.
     *
     * @var string
     */
    protected $label = '';

    /**
     * Liste les Granularity d'Orga où l'on saisie et configure les AF du Project.
     *
     * @var Collection
     */
    protected $aFGranularitiess = null;

    /**
     * Granularity organisationnelle où est spécifiée le statut des inventaires.
     *
     * @var Orga_Model_Granularity
     */
    protected $orgaGranularityForInventoryStatus = null;


    /**
     * Constructeur de la classe Project.
     */
    public function __construct()
    {
        $this->aFGranularitiess = new ArrayCollection();

        $this->createOrgaCube();
    }

    /**
     * Fonction appelé après un persist de l'objet (défini dans le mapper).
     */
    public function postSave()
    {
        Inventory_Service_ACLManager::getInstance()->createProjectResourceAndRoles($this);
    }

    /**
     * Fonction appelé avant un delete de l'objet (défini dans le mapper).
     */
    public function preDelete()
    {
        Inventory_Service_ACLManager::getInstance()->deleteProjectResourceAndRoles($this);
    }

    /**
     * Charge le Project correspondant à un cube d'Orga.
     *
     * @param Orga_Model_Cube $orgaCube
     *
     * @return Inventory_Model_Project
     */
    public static function loadByOrgaCube($orgaCube)
    {
        return self::getEntityRepository()->loadBy(array('orgaCube' => $orgaCube));
    }

    /**
     * Créé le Cube pour le projet.
     */
    protected function createOrgaCube()
    {
        $this->orgaCube = new Orga_Model_Cube();
        $globalGranularity = new Orga_Model_Granularity();
        $globalGranularity->setCube($this->orgaCube);
        $globalGranularity->save();
    }

    /**
     * Renvoie l'instance du Cube d'Orga du Project.
     * 
     * @return Orga_Model_Cube
     */
    public function getOrgaCube()
    {
        if ($this->orgaCube === null) {
            throw new Core_Exception_UndefinedAttribute("La Cube d'Orga n'a pas été définie");
        }
        return $this->orgaCube;
    }

    /**
     * Spécifie le label du roject.
     *
     * @param string $label
     */
    public function setLabel($label)
    {
        if (!is_string($label)) {
            throw new Core_Exception_InvalidArgument("Le label d'un Project doit être une chaîne");
        }
        $this->label = $label;
    }

    /**
     * Renvoie le label textuel du projet.
     *
     * @return string
     */
    public function getLabel()
    {
        return $this->label;
    }

    /**
     * Vérifie si le Project possède la AFGranularities passée en paramètre.
     *
     * @param Inventory_Model_AFGranularities $aFGranularities
     *
     * @return bool
     */
    public function hasAFGranularities(Inventory_Model_AFGranularities $aFGranularities)
    {
        return $this->aFGranularitiess->contains($aFGranularities);
    }

    /**
     * Ajoute une AFGranularities au Project.
     *
     * @param Inventory_Model_AFGranularities $aFGranularities
     */
    public function addAFGranularities(Inventory_Model_AFGranularities $aFGranularities)
    {
        if (!($this->hasAFGranularities($aFGranularities))) {
            $this->aFGranularitiess->add($aFGranularities);
            $aFGranularities->setProject($this);
        }
    }

    /**
     * Retire une AFGranularities du Project.
     *
     * @param Inventory_Model_AFGranularities $aFGranularities
     */
    public function deleteAFGranularities(Inventory_Model_AFGranularities $aFGranularities)
    {
        if ($this->hasAFGranularities($aFGranularities)) {
            $this->aFGranularitiess->removeElement($aFGranularities);
            $aFGranularities->delete();
        }
    }

    /**
     * Vérifie si le Project possède au moins une AFGranularities.
     *
     * @return bool
     */
    public function hasAFGranularitiess()
    {
        return !$this->aFGranularitiess->isEmpty();
    }

    /**
     * Renvoi l'ensemble des AFGranularities du Project.
     *
     * @return Inventory_Model_AFGranularities[]
     */
    public function getAFGranularities()
    {
        return $this->aFGranularitiess->toArray();
    }

    /**
     * Spécifie la Granularity où est spécifié le statut des inventaires.
     *
     * @param Orga_Model_Granularity $orgaGranularity
     */
    public function setOrgaGranularityForInventoryStatus($orgaGranularity)
    {
        if ($this->orgaGranularityForInventoryStatus !== $orgaGranularity) {
            if ($this->orgaGranularityForInventoryStatus !== null) {
                foreach ($this->getAFGranularities() as $aFGranularities) {
                    if (!($aFGranularities->getAFInputOrgaGranularity()->isNarrowerThan($orgaGranularity))) {
                        throw new Core_Exception_InvalidArgument();
                    }
                }
                foreach ($this->orgaGranularityForInventoryStatus->getCells() as $orgaCell) {
                    $cellDataProvider = Inventory_Model_CellDataProvider::loadByOrgaCell($orgaCell);
                    $cellDataProvider->setInventoryStatus(Inventory_Model_CellDataProvider::STATUS_NOTLAUNCHED);
                }
            }
            $this->orgaGranularityForInventoryStatus = $orgaGranularity;
        }
    }

    /**
     * Renvoie l'instance de la Granularity où est spécifié le statut des inventaires.
     * @return Orga_Model_Granularity
     */
    public function getOrgaGranularityForInventoryStatus()
    {
        if ($this->orgaGranularityForInventoryStatus === null) {
            throw new Core_Exception_UndefinedAttribute(
                "La Granularity des inventaires n'a pas été défini"
            );
        }
        return $this->orgaGranularityForInventoryStatus;
    }

}