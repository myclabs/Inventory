<?php
/**
 * @package Inventory
 * @subpackage ModelProvider
 */

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;

/**
 * Classe faisant le lien entre les cellules de la structure Orga, les AF et le DW.
 * @author valentin.claras
 * @package Inventory
 * @subpackage ModelProvider
 */
class Inventory_Model_CellDataProvider extends Core_Model_Entity implements Core_Event_ObserverInterface
{
    // Constantes de tris et de filtres.
    const QUERY_INVENTORYSTATUS = 'inventoryStatus';
    const QUERY_AFINPUTSETPRIMARY = 'aFInputSetPrimary';

    /**
     * Etat non débuté de l'inventaire.
     */
    const STATUS_NOTLAUNCHED = 'notLaunched';

    /**
     * Etat actif de l'inventaire.
     */
    const STATUS_ACTIVE = 'active';

    /**
     * Etat terminé de l'inventaire.
     */
    const STATUS_CLOSED = 'closed';

    /**
     * Identifiant unique du CellDataProvider.
     * @var int
     */
    protected $id = null;

    /**
     * Cellule d'Orga concernée.
     *
     * @var Orga_Model_Cell
     */
    protected $orgaCell = null;

    /**
     * Status de l'inventaire.
     *
     * @var string
     * @see STATUS_NOTLAUNCHED;
     * @see STATUS_ACTIVE;
     * @see STATUS_CLOSED;
     */
    protected $inventoryStatus = self::STATUS_NOTLAUNCHED;

    /**
     * Librairie utilisée pour stocker les documents des InputSets de la cellule et des cellules enfants.
     *
     * @var Doc_Model_Library
     */
    protected $docLibraryForAFInputSetsPrimary = null;

    /**
     * Tableau d'état des saisies de la cellule.
     *
     * @var AF_Model_InputSet_Primary
     */
    protected $aFInputSetPrimary = null;

    /**
     * Collection des SocialComment utilisés pour l'AFInputSetPrimary de la cellule.
     *
     * @var Collection
     */
    protected $socialCommentsForAFInputSetPrimary = null;

    /**
     * Bibliographie utiliséepar l'InputSets de la cellule.
     *
     * @var Doc_Model_Bibliography
     */
    protected $docBibliographyForAFInputSetPrimary = null;

    /**
     * Cube de DW généré par et propre à la Cell.
     *
     * @var DW_Model_Cube
     */
    protected $dWCube = null;

    /**
     * Collection des résultats créés par le primarySet.
     *
     * @var Collection
     */
    protected $dWResults = null;

    /**
     * Collection des GenericAction liées à la cellule.
     *
     * @var Collection
     */
    protected $socialGenericActions = null;

    /**
     * Collection des Docs des GenericAction liées à la cellule.
     *
     * @var Doc_Model_Library
     */
    protected $docLibraryForSocialGenericActions = null;

    /**
     * Collection des ContextAction liées à la cellule.
     *
     * @var Collection
     */
    protected $socialContextActions = null;

    /**
     * Collection des Document liés aux ContextAction.
     *
     * @var Doc_Model_Library
     */
    protected $docLibraryForSocialContextActions = null;

    /**
     * Collection des CellsGroupDataProvider utilisant ce CellDataProvider.
     *
     * @var Collection
     */
    private $cellsGroupDataProviders = null;


    /**
     * Constructeur de la classe CellDataProvider
     */
    public function __construct()
    {
        $this->socialCommentsForAFInputSetPrimary = new ArrayCollection();
        $this->dWResults = new ArrayCollection();
        $this->socialGenericActions = new ArrayCollection();
        $this->socialContextActions = new ArrayCollection();
        $this->cellsGroupDataProviders = new ArrayCollection();
    }

    /**
     * Fonction appelé après un persist de l'objet (défini dans le mapper).
     */
    public function postSave()
    {
        Inventory_Service_ACLManager::getInstance()->createCellDataProviderResourceAndRoles($this);
        if ($this->dWCube !== null) {
            Inventory_Service_ACLManager::getInstance()->addGranularityReportViewAuthorizationToCellDataProvider($this);
        }
    }

    /**
     * Fonction appelé avant un remove de l'objet (défini dans le mapper).
     */
    public function preDelete()
    {
        Inventory_Service_ACLManager::getInstance()->deleteCellDataProviderResourceAndRoles($this);
    }

    /**
     * Utilisé quand un événement est lancé.
     *
     * @param string          $event
     * @param Orga_Model_Cell $cell
     * @param array           $arguments
     */
    public static function applyEvent($event, $cell, $arguments=array())
    {
        switch ($event) {
            case Orga_Model_Cell::EVENT_SAVE:
                $granularity = $cell->getGranularity();
                try {
                    $project = Inventory_Model_Project::loadByOrgaCube($granularity->getCube());
                    $granularityForInventoryStatus = $project->getOrgaGranularityForInventoryStatus();
                } catch (Core_Exception_NotFound $e) {
                    // Le projet n'est pas encore sauvegardé, on est donc en train de le créer
                    $granularityForInventoryStatus = null;
                } catch (Core_Exception_UndefinedAttribute $e) {
                    // La granularité des inventaires n'a pas encoré été créée
                    $granularityForInventoryStatus = null;
                }

                // Création de la cellule
                $cellDataProvider = new Inventory_Model_CellDataProvider();
                $cellDataProvider->setOrgaCell($cell);

                // Définition du statut de l'inventaire
                if ($granularityForInventoryStatus
                    && $granularity !== $granularityForInventoryStatus
                    && $granularity->isNarrowerThan($granularityForInventoryStatus)
                ) {
                    // Cherche la cellule parent dans la granularité de définition des statut des inventaires
                    $parentCell = $cell->getParentCellForGranularity($granularityForInventoryStatus);
                    // Si cette cellule n'est pas sauvegardée, alors on est au statut par défaut
                    if (isset($parentCell->getKey()['id'])) {
                        $parentCellDP = Inventory_Model_CellDataProvider::loadByOrgaCell($parentCell);
                        $cellDataProvider->inventoryStatus = $parentCellDP->getInventoryStatus();
                    }
                }

                $cellDataProvider->save();
                break;
            case Orga_Model_Cell::EVENT_DELETE:
                $cellDataProvider = Inventory_Model_CellDataProvider::loadByOrgaCell($cell);
                $cellDataProvider->delete();
                break;
        }
    }

    /**
     * Charge le CellDataProvider correspondant à une Cell d'Orga.
     *
     * @param Orga_Model_Cell $orgaCell
     *
     * @return Inventory_Model_CellDataProvider
     */
    public static function loadByOrgaCell($orgaCell)
    {
        return self::getEntityRepository()->loadBy(array('orgaCell' => $orgaCell));
    }

    /**
     * Charge le CellDataProvider correspondant à un Primary Set AF.
     *
     * @param AF_Model_InputSet_Primary $aFInputSetPrimary
     *
     * @return Inventory_Model_CellDataProvider
     */
    public static function loadByAFInputSetPrimary($aFInputSetPrimary)
    {
        return self::getEntityRepository()->loadBy(array('aFInputSetPrimary' => $aFInputSetPrimary));
    }

    /**
     * Charge le CellDataProvider correspondant à un Cube de DW.
     *
     * @param DW_Model_Cube $dWCube
     *
     * @return Inventory_Model_CellDataProvider
     */
    public static function loadByDWCube($dWCube)
    {
        return self::getEntityRepository()->loadBy(array('dWCube' => $dWCube));
    }

    /**
     * Charge le CellDataProvider correspondant à une Library de Doc utilisé pour les AFInputSetsPrimary.
     *
     * @param DW_Model_Library $docLibrary
     *
     * @return Inventory_Model_CellDataProvider
     */
    public static function loadByDocLibraryForAFInputSetsPrimary($docLibrary)
    {
        return self::getEntityRepository()->loadBy(array('docLibraryForAFInputSetsPrimary' => $docLibrary));
    }

    /**
     * Charge le CellDataProvider correspondant à une Library de Doc utilisé pour les SocialGenericAction.
     *
     * @param DW_Model_Library $docLibrary
     *
     * @return Inventory_Model_CellDataProvider
     */
    public static function loadByDocLibraryForSocialGenericAction($docLibrary)
    {
        return self::getEntityRepository()->loadBy(array('docLibraryForSocialGenericAction' => $docLibrary));
    }

    /**
     * Charge le CellDataProvider correspondant à une Library de Doc utilisé pour les SocialContextAction.
     *
     * @param DW_Model_Library $docLibrary
     *
     * @return Inventory_Model_CellDataProvider
     */
    public static function loadByDocLibraryForSocialContextAction($docLibrary)
    {
        return self::getEntityRepository()->loadBy(array('docLibraryForSocialContextAction' => $docLibrary));
    }

    /**
     * Spécifie la Cell d'Orga.
     * @param Orga_Model_Cell $orgaCell
     */
    public function setOrgaCell(Orga_Model_Cell $orgaCell)
    {
        if ($this->orgaCell !== $orgaCell) {
            if ($this->orgaCell !== null) {
                throw new Core_Exception_Duplicate("Impossible de redéfinir la Cell d'Orga, elle a déjà été définie");
            }
            $this->orgaCell = $orgaCell;
            $this->createDWCube();
            if ($this->getOrgaCell()->getGranularity()->getKey() !== array()) {
                if ($this->getGranularityDataProvider()->getCellsWithInputDocs()) {
                    $this->setDocLibraryForAFInputSetsPrimary(new Doc_Model_Library());
                }
                if ($this->getGranularityDataProvider()->getCellsWithSocialGenericActions()) {
                    $this->setDocLibraryForSocialGenericAction(new Doc_Model_Library());
                }
                if ($this->getGranularityDataProvider()->getCellsWithSocialContextActions()) {
                    $this->setDocLibraryForSocialContextAction(new Doc_Model_Library());
                }
                try {
                    // TODO : ?? variable inutilisée + types incompatibles (argument de la méthode)
                    $afGranularities = Inventory_Model_AFGranularities::loadByAFInputOrgaGranularity($this->getOrgaCell());
                    $this->setDocBibliographyForAFInputSetPrimary(new Doc_Model_Bibliography());
                } catch (Core_Exception_NotFound $e) {
                    // Pas d'InputSetPrimary.
                }
            }
        }
    }

    /**
     * Renvoie l'instance de la Cell d'Orga concernée.
     *
     * @return Orga_Model_Cell
     */
    public function getOrgaCell()
    {
        if ($this->orgaCell === null) {
            throw new Core_Exception_UndefinedAttribute("La Cell d'Orga n'a pas été définie");
        }
        return $this->orgaCell;
    }

    /**
     * Renvoie la GranularityDataProvider lié au CellDataProvider.
     *
     * @return Inventory_Model_GranularityDataProvider
     */
    public function getGranularityDataProvider()
    {
        return Inventory_Model_GranularityDataProvider::loadByOrgaGranularity(
            $this->getOrgaCell()->getGranularity()
        );
    }

    /**
     * Renvoie le projet lié au CellDataProvider.
     *
     * @return Inventory_Model_Project
     */
    public function getProject()
    {
        return Inventory_Model_Project::loadByOrgaCube(
            $this->getOrgaCell()->getGranularity()->getCube()
        );
    }

    /**
     * Créé le Cube pour la simulation.
     *
     * @return int Identifiant unique du Cube.
     */
    public function createDWCube()
    {
        if ($this->dWCube === null) {
            if ($this->getOrgaCell()->getGranularity()->getKey() !== array()) {
                if ($this->getGranularityDataProvider()->getCellsGenerateDWCubes()) {
                    $this->dWCube = new DW_Model_Cube();
                    $this->dWCube->setLabel($this->getOrgaCell()->getLabel());

                    Inventory_Service_ETLStructure::getInstance()->populateCellDataProviderDWCube($this);
                    Inventory_Service_ETLStructure::getInstance()->addGranularityReportsToCellDataProviderDWCube($this);
                }
            }
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
     * Spécifie la statut de l'inventaire de la cellule.
     *
     * @param string $inventoryStatus
     * @see self::STATUS_ACTIVE
     * @see self::STATUS_CLOSED
     * @see self::STATUS_NOTLAUNCHED
     */
    public function setInventoryStatus($inventoryStatus)
    {
        if ($this->inventoryStatus !== $inventoryStatus) {
            $acceptedStatus = [self::STATUS_ACTIVE, self::STATUS_CLOSED, self::STATUS_NOTLAUNCHED];
            if (! in_array($inventoryStatus, $acceptedStatus)) {
                throw new Core_Exception_InvalidArgument(
                    "Le statut de l'inventaire doit être une constante de la classe STATUS_[..]"
                );
            }

            $this->inventoryStatus = $inventoryStatus;

            foreach ($this->getOrgaCell()->getChildCells() as $childOrgaCell) {
                $childCellDataProvider = Inventory_Model_CellDataProvider::loadByOrgaCell($childOrgaCell);
                $childCellDataProvider->setInventoryStatus($this->inventoryStatus);
            }
        }
    }

    /**
     * Renvoi la statut de l'inventaire de la cellule.
     * @return string
     */
    public function getInventoryStatus()
    {
        if ($this->inventoryStatus === null) {
            throw new Core_Exception_UndefinedAttribute("Le statut de l'inventaire n'a pas été défini");
        }
        return $this->inventoryStatus;
    }

    /**
     * Spécifie la DocLibrary pour les AFInputSetPrimary de la cellule.
     *
     * @param Doc_Model_Library $docLibrary
     */
    public function setDocLibraryForAFInputSetsPrimary(Doc_Model_Library $docLibrary)
    {
        if ($this->docLibraryForAFInputSetsPrimary !== $docLibrary) {
            if (($this->docLibraryForAFInputSetsPrimary !== null) && ($docLibrary !== null)) {
                throw new Core_Exception_Duplicate(
                    'Impossible de redéfinir la Doc Library pour les AF InputSet Primary, elle a déjà été définie'
                );
            }
            $this->docLibraryForAFInputSetsPrimary = $docLibrary;
        }
    }

    /**
     * Renvoi la DocLibrary pour les AFInputSetPrimary de la cellule.
     *
     * @return Doc_Model_Library
     */
    public function getDocLibraryForAFInputSetsPrimary()
    {
        if ($this->docLibraryForAFInputSetsPrimary === null) {
            throw new Core_Exception_UndefinedAttribute(
                "La Doc Library pour les AF InputSet Primary n'a pas été définie"
            );
        }
        return $this->docLibraryForAFInputSetsPrimary;
    }

    /**
     * Spécifie l'InputSetPrimary de la cellule.
     *
     * @param AF_Model_InputSet_Primary $aFInputSetPrimary
     */
    public function setAFInputSetPrimary($aFInputSetPrimary)
    {
        if ($this->aFInputSetPrimary !== $aFInputSetPrimary) {
            if (($this->aFInputSetPrimary !== null) && ($aFInputSetPrimary !== null)) {
                throw new Core_Exception_Duplicate(
                    "Impossible de redéfinir l'InputSetPrimary, il a déjà été défini"
                );
            }
            $this->aFInputSetPrimary = $aFInputSetPrimary;
        }
    }

    /**
     * Renvoie l'InputSetPrimary associé à la cellule.
     *
     * @return AF_Model_InputSet_Primary
     */
    public function getAFInputSetPrimary()
    {
        if ($this->aFInputSetPrimary === null) {
            throw new Core_Exception_UndefinedAttribute(
                "L'InputSetPrimary n'a pas été défini"
            );
        }
        return $this->aFInputSetPrimary;
    }

    /**
     * Spécifie la DocBibliography pour l'AFInputSetPrimary de la cellule.
     *
     * @param Doc_Model_Bibliography $docBibliography
     */
    public function setDocBibliographyForAFInputSetPrimary($docBibliography)
    {
        if ($this->docBibliographyForAFInputSetPrimary !== $docBibliography) {
            if (($this->docBibliographyForAFInputSetPrimary !== null) && ($docBibliography !== null)) {
                throw new Core_Exception_Duplicate(
                    "Impossible de redéfinir la Doc Bibliography pour l'AF InputSet Primary, elle a déjà été défini"
                );
            }
            $this->docBibliographyForAFInputSetPrimary = $docBibliography;
        }
    }

    /**
     * Renvoi la DocBibliography pour l'AFInputSetPrimary de la cellule.
     *
     * @return Doc_Model_Bibliography
     */
    public function getDocBibliographyForAFInputSetPrimary()
    {
        if ($this->docBibliographyForAFInputSetPrimary === null) {
            throw new Core_Exception_UndefinedAttribute(
                "La Doc Bibliography pour l'AF InputSet Primary n'a pas été défini"
            );
        }
        return $this->docBibliographyForAFInputSetPrimary;
    }

    /**
     * Vérifie si un SocialComment est utilisée par la cellule.
     *
     * @param Social_Model_Comment $socialComment
     *
     * @return bool
     */
    public function hasSocialCommentForInputSetPrimary(Social_Model_Comment $socialComment)
    {
        return $this->socialCommentsForAFInputSetPrimary->contains($socialComment);
    }

    /**
     * Ajoute un SocialComment à la Cellule.
     *
     * @param Social_Model_Comment $socialComment
     */
    public function addSocialCommentForInputSetPrimary(Social_Model_Comment $socialComment)
    {
        if (!($this->hasSocialCommentForInputSetPrimary($socialComment))) {
            $this->socialCommentsForAFInputSetPrimary->add($socialComment);
        }
    }

    /**
     * Retire un SocialComment de la cellule.
     *
     * @param Social_Model_Comment $socialComment
     */
    public function removeSocialCommentForInputSetPrimary(Social_Model_Comment $socialComment)
    {
        if ($this->hasSocialCommentForInputSetPrimary($socialComment)) {
            $this->socialCommentsForAFInputSetPrimary->removeElement($socialComment);
        }
    }

    /**
     * Vérifie si au moins un SocialComment est utilisée par la cellule pour l'InputSetPrimary.
     *
     * @return bool
     */
    public function hasSocialCommentsForInputSetPrimary()
    {
        return !$this->socialCommentsForAFInputSetPrimary->isEmpty();
    }

    /**
     * Renvoi l'ensemble des GeneriAction de la cellule.
     *
     * @return Social_Model_Comment[]
     */
    public function getSocialCommentsForInputSetPrimary()
    {
        return $this->socialCommentsForAFInputSetPrimary->toArray();
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
     * Récupère l'ensemble des cubes de DW peuplés par le CellDataProvider.
     *
     * @return DW_Model_Cube[]
     */
    public function getPopulatedDWCubes()
    {
        $populatedDWCubes = array();

        if ($this->getGranularityDataProvider()->getCellsGenerateDWCubes()) {
            if (Inventory_Service_ETLStructure::getInstance()->isCellDataProviderDWCubeUpToDate($this)) {
                $populatedDWCubes[] = $this->getDWCube();
            }
        }

        foreach ($this->getOrgaCell()->getParentCells() as $parentOrgaCell) {
            $parentGranularityDataProvider = Inventory_Model_GranularityDataProvider::loadByOrgaGranularity(
                $parentOrgaCell->getGranularity()
            );

            if ($parentGranularityDataProvider->getCellsGenerateDWCubes()) {
                $parentCellDataProvider = Inventory_Model_CellDataProvider::loadByOrgaCell($parentOrgaCell);

                $populatedDWCubes[] = $parentCellDataProvider->getDWCube();
            }
        }

        return $populatedDWCubes;
    }

    /**
     * Récupère l'ensemble des CellDataProvider peuplant le cube de DW de la cellule.
     *
     * @return Inventory_Model_CellDataProvider[]
     */
    public function getPopulatingCellDataProviders()
    {
        // Renvoie une exception si la cellule ne possède pas de cube.
        $this->getDWCube();

        $populatingCellDataProviders = [];

        foreach ($this->getProject()->getAFGranularities() as $aFGranularities) {
            $inputOrgaGranularity = $aFGranularities->getAFInputOrgaGranularity();
            if ($inputOrgaGranularity->isNarrowerThan($this->getOrgaCell()->getGranularity())) {
                foreach ($this->orgaCell->getChildCellsForGranularity($inputOrgaGranularity) as $inputChildOrgaCell) {
                    $populatingCellDataProviders[] = Inventory_Model_CellDataProvider::loadByOrgaCell(
                        $inputChildOrgaCell
                    );
                }
            }
        }

        return $populatingCellDataProviders;
    }

    /**
     * Créer les Result de DW issues de l'AF et les ajoute aux cubes peuplés par la cellule.
     */
    public function createDWResults()
    {
        foreach ($this->getPopulatedDWCubes() as $dWCube) {
            $this->createDWResultsForCube($dWCube);
        }
    }

    /**
     * Créer l'ensemble des résultats pour un cube de DW donné.
     *
     * @param DW_Model_Cube $dWCube
     */
    public function createDWResultsForCube(DW_Model_Cube $dWCube)
    {
        if (($this->aFInputSetPrimary === null) || ($this->aFInputSetPrimary->getOutputSet() === null)) {
            return;
        }

        foreach ($this->getAFInputSetPrimary()->getOutputSet()->getElements() as $outputElement) {
            $refClassifIndicator = $outputElement->getContextIndicator()->getIndicator()->getRef();
            try {
                $dWIndicator = DW_Model_Indicator::loadByRefAndCube('classif_'.$refClassifIndicator, $dWCube);
            } catch (Core_Exception_NotFound $e) {
                // Indexation selon l'indicateur de classif non trouvée. Impossible de créer le résultat.
                continue;
            }

            $dWResult = new DW_Model_Result();
            $dWResult->setCube($dWCube);
            $dWResult->setIndicator($dWIndicator);
            $dWResult->setValue($outputElement->getValue());

            foreach ($outputElement->getIndexes() as $outputIndex) {
                try {
                    $dWAxis = DW_Model_Axis::loadByRefAndCube('classif_'.$outputIndex->getRefAxis(), $dWCube);
                    $dWMember = DW_Model_Member::loadByRefAndAxis('classif_'.$outputIndex->getRefMember(), $dWAxis);
                    $dWResult->addMember($dWMember);
                } catch (Core_Exception_NotFound $e) {
                    // Indexation selon classif non trouvée.
                }

                foreach ($outputIndex->getMember()->getAllParents() as $classifParentMember) {
                    try {
                        $dWBroaderAxis = DW_Model_Axis::loadByRefAndCube('classif_'.$classifParentMember->getAxis()->getRef(), $dWCube);
                        $dWParentMember = DW_Model_Member::loadByRefAndAxis('classif_'.$classifParentMember->getRef(), $dWBroaderAxis);
                        $dWResult->addMember($dWParentMember);
                    } catch (Core_Exception_NotFound $e) {
                        // Indexation selon classif non trouvée.
                    }
                }
            }

            $indexingOrgaMembers = array();
            foreach ($this->getOrgaCell()->getMembers() as $orgaMember) {
                array_push($indexingOrgaMembers, $orgaMember);
                $indexingOrgaMembers = array_merge($indexingOrgaMembers, $orgaMember->getAllParents());
            }
            foreach ($indexingOrgaMembers as $indexingOrgaMember) {
                try {
                    $dWAxis = DW_Model_Axis::loadByRefAndCube('orga_'.$indexingOrgaMember->getAxis()->getRef(), $dWCube);
                    $dWMember = DW_Model_Member::loadByRefAndAxis('orga_'.$indexingOrgaMember->getRef(), $dWAxis);
                    $dWResult->addMember($dWMember);
                } catch (Core_Exception_NotFound $e) {
                    // Indexation non trouvée.
                }
            }

            $this->dWResults->add($dWResult);
        }
    }

    /**
     * Supprime l'ensemble des résultats de l'InputSet de la cellule dans les cube de DW peuplés par la cellule.
     */
    public function deleteDWResults()
    {
        $dWResults = $this->dWResults->toArray();
        foreach ($dWResults as $dWResult) {
            $dWResult->delete();
            $this->dWResults->removeElement($dWResult);
        }
    }

    /**
     * Supprime l'ensemble des résultats de l'InputSet de la cellule dans le cube de DW donné.
     *
     * @param DW_Model_Cube $dWCube
     */
    public function deleteDWResultsForCube(DW_Model_Cube $dWCube)
    {
        // Pas de criteria sur les manyToMany pour le moment.
//        $criteria = Doctrine\Common\Collections\Criteria::create()->where(
//            Doctrine\Common\Collections\Criteria::expr()->eq('dWCube', $dWCube)
//        );
//        foreach ($this->dWResults->matching($criteria)->toArray() as $dWResult) {
        foreach ($this->dWResults->toArray() as $dWResult) {
            if ($dWResult->getCube() === $dWCube) {
                $this->dWResults->removeElement($dWResult);
                $dWResult->delete();
            }
        }
    }

    /**
     * Retourne l'ensemble des Reports du Cube de DW issues du Cube de DW de la Granularity.
     *
     * @return DW_Model_Report[]
     */
    public function getGranularityReports()
    {
        $granularityReports = [];

        $dWCube = $this->getDWCube();
        foreach ($this->getGranularityDataProvider()->getGranularityReports() as $granularityReport) {
            foreach ($granularityReport->getCellDataProviderDWReports() as $dWReport) {
                if ($dWReport->getCube() === $dWCube) {
                    $granularityReports[] = $dWReport;
                }
            }
        }

        return $granularityReports;
    }

    /**
     * Vérifie si une GenericAction est utilisée par la cellule.
     *
     * @param Social_Model_GenericAction $socialGenericAction
     *
     * @return bool
     */
    public function hasSocialGenericAction(Social_Model_GenericAction $socialGenericAction)
    {
        return $this->socialGenericActions->contains($socialGenericAction);
    }

    /**
     * Ajoute une GeneriAction à la Cellule.
     *
     * @param Social_Model_GenericAction $socialGenericAction
     */
    public function addSocialGenericAction(Social_Model_GenericAction $socialGenericAction)
    {
        if (!($this->hasSocialGenericAction($socialGenericAction))) {
            $this->socialGenericActions->add($socialGenericAction);
        }
    }

    /**
     * Retire une GeneriAction de la cellule.
     *
     * @param Social_Model_GenericAction $socialGenericAction
     */
    public function removeSocialGenericAction(Social_Model_GenericAction $socialGenericAction)
    {
        if ($this->hasSocialGenericAction($socialGenericAction)) {
            $this->socialGenericActions->removeElement($socialGenericAction);
        }
    }

    /**
     * Vérifie si au moins une GeneriAction est utilisée par la cellule pour l'InputSetPrimary.
     *
     * @return bool
     */
    public function hasSocialGenericActions()
    {
        return !$this->socialGenericActions->isEmpty();
    }

    /**
     * Renvoi l'ensemble des GeneriAction de la cellule.
     *
     * @return Social_Model_GenericAction[]
     */
    public function getSocialGenericActions()
    {
        return $this->socialGenericActions->toArray();
    }

    /**
     * Spécifie la DocLibrary pour les SocialGenericAction de la cellule.
     *
     * @param Doc_Model_Library $docLibrary
     */
    public function setDocLibraryForSocialGenericAction(Doc_Model_Library $docLibrary)
    {
        if ($this->docLibraryForSocialGenericActions !== $docLibrary) {
            if (($this->docLibraryForSocialGenericActions !== null) && ($docLibrary !== null)) {
                throw new Core_Exception_Duplicate(
                    'Impossible de redéfinir la Doc Library pour les  Social Generic Action, elle a déjà été définie'
                );
            }
            $this->docLibraryForSocialGenericActions = $docLibrary;
        }
    }

    /**
     * Renvoi la DocLibrary pour les SocialGenericAction de la cellule.
     *
     * @return Doc_Model_Library
     */
    public function getDocLibraryForSocialGenericAction()
    {
        if ($this->docLibraryForSocialGenericActions === null) {
            throw new Core_Exception_UndefinedAttribute(
                "La Doc Library pour les Social Generic Actions n'a pas été définie"
            );
        }
        return $this->docLibraryForSocialGenericActions;
    }

    /**
     * Vérifie si un Doc est utilisé par la cellule pour les ContextAction.
     *
     * @param Social_Model_ContextAction $socialContextActon
     *
     * @return bool
     */
    public function hasSocialContextAction(Social_Model_ContextAction $socialContextActon)
    {
        return $this->socialContextActions->contains($socialContextActon);
    }

    /**
     * Ajoute une ContextAction à la Cellule.
     *
     * @param Social_Model_ContextAction $socialContextAction
     */
    public function addSocialContextAction(Social_Model_ContextAction $socialContextAction)
    {
        if (!($this->hasSocialContextAction($socialContextAction))) {
            $this->socialContextActions->add($socialContextAction);
        }
    }

    /**
     * Retire un ContextAction de la cellule.
     *
     * @param Social_Model_ContextAction $socialContextActon
     */
    public function removeSocialContextAction(Social_Model_ContextAction $socialContextActon)
    {
        if ($this->hasSocialContextActions($socialContextActon)) {
            $this->socialContextActions->removeElement($socialContextActon);
        }
    }

    /**
     * Vérifie si au moins une ContextAction est utilisé par la cellule.
     *
     * @return bool
     */
    public function hasSocialContextActions()
    {
        return !$this->socialContextActions->isEmpty();
    }

    /**
     * Renvoi l'ensemble des ContextAction de la cellule.
     *
     * @return Social_Model_ContextAction[]
     */
    public function getSocialContextActions()
    {
        return $this->socialContextActions->toArray();
    }

    /**
     * Spécifie la DocLibrary pour les SocialContextAction de la cellule.
     *
     * @param Doc_Model_Library $docLibrary
     */
    public function setDocLibraryForSocialContextAction(Doc_Model_Library $docLibrary)
    {
        if ($this->docLibraryForSocialContextActions !== $docLibrary) {
            if (($this->docLibraryForSocialContextActions !== null) && ($docLibrary !== null)) {
                throw new Core_Exception_Duplicate(
                    'Impossible de redéfinir la Doc Library pour les Social Context Action, elle a déjà été définie'
                );
            }
            $this->docLibraryForSocialContextActions = $docLibrary;
        }
    }

    /**
     * Renvoi la DocLibrary pour les SocialContextAction de la cellule.
     *
     * @return Doc_Model_Library
     */
    public function getDocLibraryForSocialContextAction()
    {
        if ($this->docLibraryForSocialContextActions === null) {
            throw new Core_Exception_UndefinedAttribute(
                "La Doc Library pour les Social Context Actions n'a pas été définie"
            );
        }
        return $this->docLibraryForSocialContextActions;
    }

    /**
     * Renvoie les Cell enfantes pour une Granularity donnée.
     *  Redéfinition de la fonction pour permettre les filtres et tri sur les CellDataProvider.
     *
     * @param Orga_Model_Granularity $narrowerGranularity
     * @param Core_Model_Query $queryParameters
     *
     * @return Inventory_Model_CellDataProvider[]
     */
    public function getChildCellsForGranularity($narrowerGranularity, Core_Model_Query $queryParameters=null)
    {
        if (!($this->getOrgaCell()->getGranularity()->isBroaderThan($narrowerGranularity))) {
            throw new Core_Exception_InvalidArgument('The given granularity is not narrower than the current');
        }
        if ($queryParameters === null) {
            $queryParameters = new Core_Model_Query();
            $queryParameters->order->addOrder(
                Orga_Model_Cell::QUERY_MEMBERS_HASHKEY,
                Core_Model_Order::ORDER_ASC,
                Orga_Model_Cell::getAlias()
            );
        }

        $childMembersForGranularity = $this->getOrgaCell()->getChildMembersForGranularity($narrowerGranularity);

        // Si l'un des axes de la granularité ne possède pas d'enfants, alors il n'y a pas de cellules enfantes.
        foreach ($childMembersForGranularity as $childAxisMembersForGranularity) {
            if (empty($childAxisMembersForGranularity)) {
                return [];
            }
        }

        $childMembers = array(
            array(
                'granularity' => $narrowerGranularity,
                'members'     => $childMembersForGranularity
            )
        );
        return self::getEntityRepository()->loadByMembers($childMembers, $queryParameters);
    }

    /**
     * Compte le total des Cell enfantes pour une Granularity donnée.
     *
     * @param Orga_Model_Granularity $narrowerGranularity
     * @param Core_Model_Query $queryParameters
     *
     * @return int
     */
    public function countTotalChildCellsForGranularity($narrowerGranularity, Core_Model_Query $queryParameters = null)
    {
        if (!($this->getOrgaCell()->getGranularity()->isBroaderThan($narrowerGranularity))) {
            throw new Core_Exception_InvalidArgument('The given granularity is not narrower than the current');
        }
        if ($queryParameters === null) {
            $queryParameters = new Core_Model_Query();
            $queryParameters->order->addOrder(
                Orga_Model_Cell::QUERY_MEMBERS_HASHKEY,
                Core_Model_Order::ORDER_ASC,
                Orga_Model_Cell::getAlias()
            );
        }

        $childMembersForGranularity = $this->getOrgaCell()->getChildMembersForGranularity($narrowerGranularity);

        // Si l'un des axes de la granularité ne possède pas d'enfants, alors il n'y a pas de cellules enfantes.
        foreach ($childMembersForGranularity as $childAxisMembersForGranularity) {
            if (empty($childAxisMembersForGranularity)) {
                return [];
            }
        }

        $childMembers = array(
            array(
                'granularity' => $narrowerGranularity,
                'members'     => $childMembersForGranularity
            )
        );
        return self::getEntityRepository()->countTotalByMembers($childMembers, $queryParameters);
    }

}