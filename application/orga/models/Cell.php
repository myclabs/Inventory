<?php

use Doc\Domain\Bibliography;
use Doc\Domain\Library;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\Common\Collections\Criteria;
use Orga\Model\ACL\CellAuthorization;
use Orga\Model\ACL\Role\AbstractCellRole;
use Orga\Model\ACL\Role\CellAdminRole;
use Orga\Model\ACL\Role\CellContributorRole;
use Orga\Model\ACL\Role\CellManagerRole;
use Orga\Model\ACL\Role\CellObserverRole;
use User\Domain\ACL\Resource\Resource;
use User\Domain\ACL\Resource\ResourceTrait;

/**
 * Definit une cellule organisationnelle.
 *
 * @author valentin.claras
 * @author simon.rieu
 */
class Orga_Model_Cell extends Core_Model_Entity implements Resource
{
    use ResourceTrait;

    // Constantes de tris et de filtres.
    const QUERY_GRANULARITY = 'granularity';
    const QUERY_TAG = 'tag';
    const QUERY_MEMBERS_HASHKEY = 'membersHashKey';
    const QUERY_RELEVANT = 'relevant';
    const QUERY_ALLPARENTSRELEVANT = 'allParentsRelevant';
    const QUERY_INVENTORYSTATUS = 'inventoryStatus';
    const QUERY_AFINPUTSETPRIMARY = 'aFInputSetPrimary';

    // Séparateur des refs et labels des axes dans le label de la cellule.
    const  REF_SEPARATOR = '|';
    const  LABEL_SEPARATOR = ' | ';


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
     * Identifiant unique de la Cell.
     *
     * @var int
     */
    protected $id;

    /**
     * Granularity à laquelle appartient la Cell.
     *
     * @var Orga_Model_Granularity
     */
    protected $granularity;

    /**
     * Collection des Member indexant la Cell.
     *
     * @var Collection|Orga_Model_Member[]
     */
    protected $members = array();

    /**
     * Représentation simplifié en chaine de caractère des membres de la cellule.
     *
     * @var string
     */
    protected $membersHashKey = '';

    /**
     * Tag identifiant la cellule dans la hiérarchie de l'organization.
     *
     * @var string
     */
    protected $tag = '';

    /**
     * Définit si la cellule est pertinente.
     *
     * @var bool
     */
    protected $relevant = true;

    /**
     * Définit si toute les cellules parentes sont aussi pertinentes.
     *
     * @var bool
     */
    protected $allParentsRelevant = true;

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
     * Collection des CellsGroup utilisant cette Cell comme container.
     *
     * @var Collection|Orga_Model_CellsGroup[]
     */
    private $cellsGroups = null;

    /**
     * Tableau d'état des saisies de la cellule.
     *
     * @var AF_Model_InputSet_Primary
     */
    protected $aFInputSetPrimary = null;

    /**
     * Librairie utilisée pour stocker les documents des InputSets de la cellule et des cellules enfants.
     *
     * @var Library
     */
    protected $docLibraryForAFInputSetsPrimary = null;

    /**
     * Collection des SocialComment utilisés pour l'AFInputSetPrimary de la cellule.
     *
     * @var Collection|Social_Model_Comment[]
     */
    protected $socialCommentsForAFInputSetPrimary = null;

    /**
     * Bibliographie utiliséepar l'InputSets de la cellule.
     *
     * @var Bibliography
     */
    protected $docBibliographyForAFInputSetPrimary = null;

    /**
     * Organization de DW généré par et propre à la Cell.
     *
     * @var DW_model_cube
     */
    protected $dWCube = null;

    /**
     * Collection des résultats créés par le primarySet.
     *
     * @var Collection|DW_Model_Result[]
     */
    protected $dWResults = null;

    /**
     * Collection des GenericAction liées à la cellule.
     *
     * @var Collection|Social_Model_Comment
     */
    protected $socialGenericActions = null;

    /**
     * Collection des Docs des GenericAction liées à la cellule.
     *
     * @var Library
     */
    protected $docLibraryForSocialGenericActions = null;

    /**
     * Collection des ContextAction liées à la cellule.
     *
     * @var Collection|Social_Model_ContextAction
     */
    protected $socialContextActions = null;

    /**
     * Collection des Document liés aux ContextAction.
     *
     * @var Library
     */
    protected $docLibraryForSocialContextActions = null;

    /**
     * @var CellAuthorization[]|Collection
     */
    protected $acl;

    /**
     * Liste des roles administrateurs sur cette cellule.
     *
     * @var CellAdminRole[]|Collection
     */
    protected $adminRoles;

    /**
     * Liste des roles managers sur cette cellule.
     *
     * @var CellManagerRole[]|Collection
     */
    protected $managerRoles;

    /**
     * Liste des roles contributeurs sur cette cellule.
     *
     * @var CellContributorRole[]|Collection
     */
    protected $contributorRoles;

    /**
     * Liste des roles observateurs sur cette cellule.
     *
     * @var CellObserverRole[]|Collection
     */
    protected $observerRoles;


    /**
     * Constructeur de la classe Cell.
     * @param Orga_Model_Granularity $granularity
     * @param Orga_Model_Member[]    $members
     */
    public function __construct(Orga_Model_Granularity $granularity, array $members = [])
    {
        $this->members = new ArrayCollection();
        $this->cellsGroups = new ArrayCollection();
        $this->socialCommentsForAFInputSetPrimary = new ArrayCollection();
        $this->dWResults = new ArrayCollection();
        $this->socialGenericActions = new ArrayCollection();
        $this->socialContextActions = new ArrayCollection();
        $this->acl = new ArrayCollection();
        $this->adminRoles = new ArrayCollection();
        $this->managerRoles = new ArrayCollection();
        $this->contributorRoles = new ArrayCollection();
        $this->observerRoles = new ArrayCollection();

        $this->granularity = $granularity;
        foreach ($members as $member) {
            $this->members->add($member);
            $member->addCell($this);
        }
        $this->updateTags();

        // Création du cube de DW.
        $this->createDWCube();
        // Création du CellsGroup.
        foreach ($this->granularity->getInputGranularities() as $inputGranularity) {
            new Orga_Model_CellsGroup($this, $inputGranularity);
        }
        // Création de la Bibliography des Input.
        if ($this->granularity->getInputConfigGranularity() !== null) {
            $this->docBibliographyForAFInputSetPrimary = new Bibliography();
        }
        // Création de la Library des Input.
        if ($this->granularity->getCellsWithInputDocuments()) {
            $this->docLibraryForAFInputSetsPrimary = new Library();
        }
        // Création de la Library des GenericAction.
        if ($this->granularity->getCellsWithSocialGenericActions()) {
            $this->docLibraryForSocialGenericActions = new Library();
        }
        // Création de la Library des ContextAction.
        if ($this->granularity->getCellsWithInputDocuments()) {
            $this->docLibraryForSocialContextActions = new Library();
        }

        // Héritage des ACL (racines) des cellules parent
        foreach ($this->getParentCells() as $parentCell) {
            foreach ($parentCell->getRootACL() as $parentAuthorization) {
                // L'autorisation sera automatiquement ajoutée à $this->acl
                CellAuthorization::createChildAuthorization($parentAuthorization, $this);
            }
        }
    }

    /**
     * Charge la Cell correspondant à un Primary Set AF.
     *
     * @param AF_Model_InputSet_Primary $aFInputSetPrimary
     *
     * @return Orga_Model_Cell
     */
    public static function loadByAFInputSetPrimary(AF_Model_InputSet_Primary $aFInputSetPrimary)
    {
        return self::getEntityRepository()->loadBy(array('aFInputSetPrimary' => $aFInputSetPrimary));
    }

    /**
     * Charge la Cell correspondant à un Organization de DW.
     *
     * @param DW_model_cube $dWCube
     *
     * @return Orga_Model_Cell
     */
    public static function loadByDWCube(DW_model_cube $dWCube)
    {
        return self::getEntityRepository()->loadBy(array('dWCube' => $dWCube));
    }

    /**
     * Charge la Cell correspondant à une Library de Doc utilisé pour les AFInputSetsPrimary.
     *
     * @param Library $docLibrary
     *
     * @return Orga_Model_Cell
     */
    public static function loadByDocLibraryForAFInputSetsPrimary(Library $docLibrary)
    {
        return self::getEntityRepository()->loadBy(array('docLibraryForAFInputSetsPrimary' => $docLibrary));
    }

    /**
     * Charge la Cell correspondant à une Library de Doc utilisé pour les SocialGenericAction.
     *
     * @param Library $docLibrary
     *
     * @return Orga_Model_Cell
     */
    public static function loadByDocLibraryForSocialGenericAction(Library $docLibrary)
    {
        return self::getEntityRepository()->loadBy(array('docLibraryForSocialGenericAction' => $docLibrary));
    }

    /**
     * Charge la Cell correspondant à une Library de Doc utilisé pour les SocialContextAction.
     *
     * @param Library $docLibrary
     *
     * @return Orga_Model_Cell
     */
    public static function loadByDocLibraryForSocialContextAction(Library $docLibrary)
    {
        return self::getEntityRepository()->loadBy(array('docLibraryForSocialContextAction' => $docLibrary));
    }

    /**
     * Renvoie l'id de la Cell.
     *
     * @return string
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Renvoie la Granularity de la Cell.
     *
     * @return Orga_Model_Granularity
     */
    public function getGranularity()
    {
        return $this->granularity;
    }

    /**
     * @return Orga_Model_Organization
     */
    public function getOrganization()
    {
        return $this->granularity->getOrganization();
    }

    /**
     * Vérifie si le Member donné indexe la Cell.
     *
     * @param Orga_Model_Member $member
     *
     * @return boolean
     */
    public function hasMember(Orga_Model_Member $member)
    {
        return $this->members->contains($member);
    }

    /**
     * Vérifie que la Cell possède au moir un Member.
     *
     * @return bool
     */
    public function hasMembers()
    {
        return !$this->members->isEmpty();
    }

    /**
     * Renvoi tous les Member indexant la Cell.
     *
     * @return Orga_Model_Member[]
     */
    public function getMembers()
    {
        return $this->members->toArray();
    }

    /**
     * Met à jour la HashKey et le ta de la cellule.
     */
    public function updateTags()
    {
        $this->updateMembersHashKey();
        $this->updateTag();
        $this->updateHierarchy();
    }

    /**
     * Met à jour la member hashKey de la cellule.
     */
    public function updateMembersHashKey()
    {
        $this->membersHashKey = self::buildMembersHashKey($this->getMembers());
    }

    /**
     * Retourne la clé de hashage des membres de la cellule.
     *
     * @return string
     */
    public function getMembersHashKey()
    {
        return $this->membersHashKey;
    }

    /**
     * Construit une chaine de caractère représentant les membres.
     *
     * Les membres sont ordonnés en fonction de la position globle de l'axe.
     *
     * @param Orga_Model_Member[] $listMembers
     *
     * @return string
     */
    public static function buildMembersHashKey($listMembers)
    {
        @usort($listMembers, ['Orga_Model_Member', 'orderMembers']);
        $membersRef = [];

        foreach ($listMembers as $member) {
            $membersRef[] = $member->getCompleteRef();
        }

        return sha1(implode(self::REF_SEPARATOR, $membersRef));
    }

    /**
     * Met à jour la member hashKey de la cellule.
     */
    public function updateTag()
    {
        if (!$this->hasMembers()) {
            $this->tag = Orga_Model_Organization::PATH_SEPARATOR;
        } else {
            $membersTagParts = array();
            $members = $this->getMembers();
            @usort($members, ['Orga_Model_Member', 'orderMembers']);
            foreach ($members as $member) {
                $membersTagParts[] = $member->getTag();
            }
            $this->tag =  implode(Orga_Model_Organization::PATH_JOIN, $membersTagParts);
        }
    }

    /**
     * Retourne la clé de hashage des membres de la cellule.
     *
     * @return string
     */
    public function getTag()
    {
        return $this->tag;
    }

    /**
     * Définit si la Cell est pertinente ou non.
     *
     * @param bool $relevant
     */
    public function setRelevant($relevant)
    {
        if ($relevant != $this->relevant) {
            $this->relevant = $relevant;
            // Si les cellules supérieures ne sont pas pertinentes,
            //  alors modifier celle-ci n'impactera pas les cellules inférieures.
            if ($this->getAllParentsRelevant() === true) {
                // Nécessaire pour mettre à jour l'intégralité des cellules filles.
                foreach ($this->getChildCells() as $childCell) {
                    $childAccess = $relevant;
                    if ($childAccess) {
                        foreach ($childCell->getGranularity()->getBroaderGranularities() as $broaderGranularity) {
                            try {
                                $parentCell= $childCell->getParentCellForGranularity($broaderGranularity);
                            } catch (Core_Exception_NotFound $e) {
                                // Pas de cellule parente pour cette granularité.
                                $parentCell = null;
                            }
                            if (($parentCell === null) || (!$parentCell->getRelevant())) {
                                $childAccess = false;
                                break;
                            }
                        }
                    }
                    $childCell->setAllParentsRelevant($childAccess);
                }
            }
        }
    }

    /**
     * Renvoie la pertinence de la Cell.
     *
     * @return bool
     */
    public function getRelevant()
    {
        return $this->relevant;
    }

    /**
     * Définit si tous les parents de la Cell sont pertinents.
     *
     * @param bool $allParentsRelevant
     */
    protected function setAllParentsRelevant($allParentsRelevant)
    {
        $this->allParentsRelevant = $allParentsRelevant;
    }

    /**
     * Renvoie la pertinence des Cell parentes de la Cell courante.
     *
     * @return bool
     */
    public function getAllParentsRelevant()
    {
        return $this->allParentsRelevant;
    }

    /**
     * Vérifie si la Cell et ses Cell parentes sont pertinentes.
     *
     * @return bool
     */
    public function isRelevant()
    {
        return ($this->getRelevant() && $this->getAllParentsRelevant());
    }

    /**
     * Désactive la cellule (utilisé dans le cas d'une hiérarchie cassée).
     */
    public function disable()
    {
        $this->allParentsRelevant = false;
    }

    /**
     * Réactive la cellule et met à jour la pertinence de ses cellules parentes.
     */
    public function enable()
    {
        $access = true;
        foreach ($this->getGranularity()->getBroaderGranularities() as $broaderGranularity) {
            try {
                $parentCell= $this->getParentCellForGranularity($broaderGranularity);
            } catch (Core_Exception_NotFound $e) {
                // Pas de cellule parente pour cette granularité.
                $parentCell = null;
            } catch (Core_Exception_TooMany $e) {
                // Pas de cellule parente pour cette granularité.
                $parentCell = null;
            }
            if (($parentCell === null) || (!$parentCell->getRelevant())) {
                $access = false;
                break;
            }
        }
        $this->allParentsRelevant = $access;
    }

    /**
     * Met à jour les attributs dépendants de la hiérarchie.
     */
    public function updateHierarchy()
    {
        // Mise à jour de la pertinence.
        $this->enable();

        // Mise à jour du status de l'inventaire.
        try {
            $granularityForInventoryStatus = $this->granularity->getOrganization()->getGranularityForInventoryStatus();
        } catch (Core_Exception_UndefinedAttribute $e) {
            // La granularité des inventaires n'a pas encoré été choisie.
            $granularityForInventoryStatus = null;
        }
        // Définition du statut de l'inventaire.
        if (($granularityForInventoryStatus)
            && ($this->granularity !== $granularityForInventoryStatus)
            && ($this->granularity->isNarrowerThan($granularityForInventoryStatus))
        ) {
            // Cherche la cellule parent dans la granularité de définition des statut des inventaires
            try {
                $parentCellForInventoryStatus = $this->getParentCellForGranularity($granularityForInventoryStatus);
                $this->updateInventoryStatus($parentCellForInventoryStatus->getInventoryStatus());
            } catch (Core_Exception_NotFound $e) {
                // Il n'y a pas de cellules parentes.
                $this->updateInventoryStatus(self::STATUS_NOTLAUNCHED);
            }
        }

        // Vérification des bibliothèques.
        if ($this->getGranularity()->getInputConfigGranularity() !== null) {
            $parentDocLibraryForAFInputSetsPrimary = $this->getParentDocLibraryForAFInputSetsPrimary();
            $docBibliographyForAFInputSetPrimary = $this->getDocBibliographyForAFInputSetPrimary();
            foreach ($docBibliographyForAFInputSetPrimary->getReferencedDocuments() as $document) {
                if (!$parentDocLibraryForAFInputSetsPrimary->hasDocument($document)) {
                    $docBibliographyForAFInputSetPrimary->unreferenceDocument($document);
                }
            }
        }
        if ($this->getGranularity()->getCellsWithSocialGenericActions()) {
            $parentDocLibraryForSocialGenericAction = $this->getParentDocLibraryForSocialGenericAction();
            foreach ($this->getSocialGenericActions() as $socialGenericAction) {
                $docBibliographyForSocialGenericAction = $socialGenericAction->getDocumentBibliography();
                foreach ($docBibliographyForSocialGenericAction->getReferencedDocuments() as $document) {
                    if (!$parentDocLibraryForSocialGenericAction->hasDocument($document)) {
                        $docBibliographyForSocialGenericAction->unreferenceDocument($document);
                    }
                }
            }
        }
        if ($this->getGranularity()->getInputConfigGranularity()) {
            $parentDocLibraryForSocialContextAction = $this->getParentDocLibraryForSocialContextAction();
            foreach ($this->getSocialContextActions() as $sociaContextAction) {
                $docBibliographyForSocialContextAction = $sociaContextAction->getDocumentBibliography();
                foreach ($docBibliographyForSocialContextAction->getReferencedDocuments() as $document) {
                    if (!$parentDocLibraryForSocialContextAction->hasDocument($document)) {
                        $docBibliographyForSocialContextAction->unreferenceDocument($document);
                    }
                }
            }
        }
    }

    /**
     * Renvoie le label de la Cell. Basée sur les labels des Member.
     *
     * @return string
     */
    public function getLabel()
    {
        if ($this->members->isEmpty()) {
            return __('Orga', 'navigation', 'labelGlobalCell');
        }

        $labels = [];
        $members = $this->getMembers();
        @usort($members, ['Orga_Model_Member', 'orderMembers']);
        foreach ($members as $member) {
            $labels[] = $member->getLabel();
        }

        return implode(self::LABEL_SEPARATOR, $labels);
    }

    /**
     * Renvoie le label étendue de la Cell. Basée sur les labels étendues des Member.
     *
     * @return string
     */
    public function getExtendedLabel()
    {
        if ($this->members->isEmpty()) {
            return __('Orga', 'navigation', 'labelGlobalCellExtended');
        }

        $labels = [];
        $members = $this->getMembers();
        @usort($members, ['Orga_Model_Member', 'orderMembers']);
        foreach ($members as $member) {
            $labels[] = $member->getExtendedLabel();
        }

        return implode(self::LABEL_SEPARATOR, $labels);
    }

    /**
     * Renvoie la liste des Member parents aux Member de la Cell courante pour une Granularity broader donnée.
     *
     * @param Orga_Model_Granularity $broaderGranularity
     *
     * @return Orga_Model_Member[]
     */
    protected function getParentMembersForGranularity(Orga_Model_Granularity $broaderGranularity)
    {
        $parentMembers = array();

        foreach ($this->getMembers() as $member) {
            foreach ($broaderGranularity->getAxes() as $broaderAxis) {
                if ($member->getAxis()->isNarrowerThan($broaderAxis)) {
                    $parentMembers[$broaderAxis->getRef()] = $member->getParentForAxis($broaderAxis);
                } else {
                    if ($member->getAxis() === $broaderAxis) {
                        $parentMembers[$broaderAxis->getRef()] = $member;
                    }
                }
            }
        }

        return $parentMembers;
    }

    /**
     * Renvoie la liste des Member enfants aux Member de la Cell courante pour une Granularity narrower donnée.
     *
     * @param Orga_Model_Granularity $narrowerGranularity
     *
     * @return Orga_Model_Member[]
     */
    public function getChildMembersForGranularity(Orga_Model_Granularity $narrowerGranularity)
    {
        $childMembers = array();

        foreach ($narrowerGranularity->getAxes() as $narrowerAxis) {
            $refNarrowerAxis = $narrowerAxis->getRef();
            foreach ($this->getMembers() as $member) {
                if ($member->getAxis()->isBroaderThan($narrowerAxis)) {
                    if (!isset($childMembers[$refNarrowerAxis])) {
                        $childMembers[$refNarrowerAxis] = $member->getChildrenForAxis($narrowerAxis);
                    } else {
                        $childMembers[$refNarrowerAxis] = array_intersect(
                            $childMembers[$refNarrowerAxis],
                            $member->getChildrenForAxis($narrowerAxis)
                        );
                    }
                } else if ($member->getAxis() === $narrowerAxis) {
                    $childMembers[$refNarrowerAxis] = [$member];
                }
            }
            if (!isset($childMembers[$refNarrowerAxis])) {
                $childMembers[$refNarrowerAxis] = $narrowerAxis->getMembers()->toArray();
            }
        }

        return $childMembers;
    }

    /**
     * Renvoie la Cell parente pour une Granularity broader donnée.
     *
     * @param Orga_Model_Granularity $broaderGranularity
     *
     * @throws Core_Exception_InvalidArgument The given granularity is not broader than the current
     * @return Orga_Model_Cell
     */
    public function getParentCellForGranularity(Orga_Model_Granularity $broaderGranularity)
    {
        if (!$this->getGranularity()->isNarrowerThan($broaderGranularity)) {
            throw new Core_Exception_InvalidArgument('The given granularity is not broader than the current.');
        }

        return $broaderGranularity->getCellByMembers($this->getParentMembersForGranularity($broaderGranularity));
    }

    /**
     * Renvoie les Cell parentes pour toutes les Granularity broader.
     *
     * @return Orga_Model_Cell[]
     */
    public function getParentCells()
    {
        $parentCells = array();

        foreach ($this->getGranularity()->getBroaderGranularities() as $broaderGranularity) {
            try {
                $parentCells[] = $this->getParentCellForGranularity($broaderGranularity);
            } catch (Core_Exception_NotFound $e) {
                // Pas de cellule parente pour cette granularité.
            }
        }

        return $parentCells;
    }

    /**
     * Renvoie les Cell enfantes pour une Granularity donnée.
     *
     * @param Orga_Model_Granularity $narrowerGranularity
     *
     * @throws Core_Exception_InvalidArgument The given granularity is not narrower than the current
     * @return Collection|Orga_Model_Cell[]
     */
    public function getChildCellsForGranularity(Orga_Model_Granularity $narrowerGranularity)
    {
        if (!$narrowerGranularity->isNarrowerThan($this->getGranularity())) {
            throw new Core_Exception_InvalidArgument('The given granularity is not narrower than the current.');
        }

        $criteria = Doctrine\Common\Collections\Criteria::create();
        foreach (explode(Orga_Model_Organization::PATH_JOIN, $this->getTag()) as $pathTag) {
            $criteria->andWhere($criteria->expr()->contains('tag', $pathTag));
        }
        //@todo Ordre des Cellules suivant les tag (?Position- & Ref) !== ordre réel (Position || Label).
        $criteria->orderBy(['tag' => 'ASC']);
        return $narrowerGranularity->getCells()->matching($criteria);
    }

    /**
     * Renvoie les Cell enfantes pour une Granularity donnée.
     *
     * @throws Core_Exception_InvalidArgument The given granularity is not narrower than the current
     * @return Orga_Model_Cell[]
     */
    public function getChildCells()
    {
        $childCells = [];

        foreach ($this->getGranularity()->getNarrowerGranularities() as $narrowerGranularity) {
            $childCells = array_merge($childCells, $this->getChildCellsForGranularity($narrowerGranularity)->toArray());
        }

        return $childCells;
    }

    /**
     * Renvoie les Cell enfantes pour une Granularity donnée.
     *
     * @param Orga_Model_Granularity $narrowerGranularity
     * @param Core_Model_Query|null  $queryParameters
     *
     * @throws Core_Exception_InvalidArgument The given granularity is not narrower than the current
     * @return Orga_Model_Cell[]
     */
    public function loadChildCellsForGranularity($narrowerGranularity, Core_Model_Query $queryParameters = null)
    {
        if (!($this->getGranularity()->isBroaderThan($narrowerGranularity))) {
            throw new Core_Exception_InvalidArgument('The given granularity is not narrower than the current');
        }
        if ($queryParameters === null) {
            $queryParameters = new Core_Model_Query();
            $queryParameters->order->addOrder('tag');
        }

        $childMembersForGranularity = $this->getChildMembersForGranularity($narrowerGranularity);

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
     * @param Core_Model_Query       $queryParameters
     *
     * @throws Core_Exception_InvalidArgument The given granularity is not narrower than the current
     * @return int
     */
    public function countTotalChildCellsForGranularity($narrowerGranularity, Core_Model_Query $queryParameters = null)
    {
        if (!($this->getGranularity()->isBroaderThan($narrowerGranularity))) {
            throw new Core_Exception_InvalidArgument('The given granularity is not narrower than the current.');
        }
        if ($queryParameters === null) {
            $queryParameters = new Core_Model_Query();
            $queryParameters->order->addOrder(self::QUERY_MEMBERS_HASHKEY);
        }

        $childMembersForGranularity = $this->getChildMembersForGranularity($narrowerGranularity);

        // Si l'un des axes de la granularité ne possède pas d'enfants, alors il n'y a pas de cellules enfantes.
        foreach ($childMembersForGranularity as $childAxisMembersForGranularity) {
            if (empty($childAxisMembersForGranularity)) {
                return array();
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

    /**
     * Compte le total des Cell enfantes.
     *
     * @return int
     */
    public function countTotalChildCells()
    {
        $totalChildCells = 0;
        foreach ($this->getGranularity()->getNarrowerGranularities() as $narrowerGranularity) {
            $totalChildCells += $this->countTotalChildCellsForGranularity($narrowerGranularity);
        }
        return $totalChildCells;
    }

    /**
     * Spécifie la statut de l'inventaire de la cellule.
     *
     * @param string $inventoryStatus
     *
     * @throws Core_Exception
     * @throws Core_Exception_InvalidArgument
     *
     * @see self::STATUS_ACTIVE
     * @see self::STATUS_CLOSED
     * @see self::STATUS_NOTLAUNCHED
     */
    public function setInventoryStatus($inventoryStatus)
    {
        if ($this->getGranularity() !== $this->getGranularity()->getOrganization()->getGranularityForInventoryStatus()) {
            throw new Core_Exception('Inventory status can only be defined in the inventory granularity.');
        }

        if ($this->inventoryStatus !== $inventoryStatus) {
            $acceptedStatus = [self::STATUS_ACTIVE, self::STATUS_CLOSED, self::STATUS_NOTLAUNCHED];
            if (! in_array($inventoryStatus, $acceptedStatus)) {
                throw new Core_Exception_InvalidArgument('Inventory status must be a class constant (STATUS_[..]).');
            }

            $this->inventoryStatus = $inventoryStatus;

            foreach ($this->getChildCells() as $childCell) {
                $childCell->updateInventoryStatus($this->inventoryStatus);
            }
        }
    }

    /**
     * Mets à jour [interne] la statut de l'inventaire de la cellule.
     *
     * @param string $inventoryStatus
     */
    private function updateInventoryStatus($inventoryStatus)
    {
        $this->inventoryStatus = $inventoryStatus;
    }

    /**
     * Renvoi la statut de l'inventaire de la cellule.
     *
     * @return string
     *
     * @see self::STATUS_ACTIVE
     * @see self::STATUS_CLOSED
     * @see self::STATUS_NOTLAUNCHED
     */
    public function getInventoryStatus()
    {
        return $this->inventoryStatus;
    }

    /**
     * @param Orga_Model_CellsGroup $cellsGroup
     * @throws Core_Exception_InvalidArgument
     */
    public function addCellsGroup(Orga_Model_CellsGroup $cellsGroup)
    {
        if ($cellsGroup->getContainerCell() !== $this) {
            throw new Core_Exception_InvalidArgument();
        }

        if (!$this->hasCellsGroup($cellsGroup)) {
            $this->cellsGroups->add($cellsGroup);
        }
    }

    /**
     * Vérifie si le CellsGroup passé fait partie de la Cell.
     *
     * @param Orga_Model_CellsGroup $cellsGroup
     *
     * @return boolean
     */
    public function hasCellsGroup(Orga_Model_CellsGroup $cellsGroup)
    {
        return $this->cellsGroups->contains($cellsGroup);
    }

    /**
     * Renvoie le CellsGroup corespondant à une Granularité de saisie.
     *
     * @param Orga_Model_Granularity $inputGranularity
     *
     * @throws Core_Exception_NotFound
     * @throws Core_Exception_TooMany
     *
     * @return Orga_Model_CellsGroup
     */
    public function getCellsGroupForInputGranularity(Orga_Model_Granularity $inputGranularity)
    {
        $criteria = \Doctrine\Common\Collections\Criteria::create();
        $criteria->where($criteria->expr()->eq('inputGranularity', $inputGranularity));
        $cellsGroup = $this->cellsGroups->matching($criteria)->toArray();

        if (empty($cellsGroup)) {
            throw new Core_Exception_NotFound('No CellsGroup for input Granularity "'.$inputGranularity.'".');
        } elseif (count($cellsGroup) > 1) {
            throw new Core_Exception_TooMany('Too many CellsGroup for input Granularity "'.$inputGranularity.'".');
        }

        return array_pop($cellsGroup);
    }

    /**
     * Supprime le CellGroup donné de la Cell.
     *
     * @param Orga_Model_CellsGroup $cellsGroup
     */
    public function removeCellsGroup(Orga_Model_CellsGroup $cellsGroup)
    {
        if ($this->hasCellsGroup($cellsGroup)) {
            $this->cellsGroups->removeElement($cellsGroup);
            $cellsGroup->delete();
        }
    }

    /**
     * Vérifie si la Cell possède des CellsGroup.
     *
     * @return bool
     */
    public function hasCellsGroups()
    {
        return !$this->cellsGroups->isEmpty();
    }

    /**
     * Retourne un tableau contenant les CellsGroup de la Cell.
     *
     * @return Orga_Model_CellsGroup[]
     */
    public function getCellsGroups()
    {
        return $this->cellsGroups->toArray();
    }

    /**
     * Spécifie l'InputSetPrimary de la cellule.
     *
     * @param AF_Model_InputSet_Primary $aFInputSetPrimary
     *
     * @throws Core_Exception_Duplicate
     */
    public function setAFInputSetPrimary(AF_Model_InputSet_Primary $aFInputSetPrimary=null)
    {
        if ($this->aFInputSetPrimary !== $aFInputSetPrimary) {
            if (($this->aFInputSetPrimary !== null) && ($aFInputSetPrimary !== null)) {
                throw new Core_Exception_Duplicate('InputSetPrimary as already be defined.');
            }
            if ($this->aFInputSetPrimary !== null) {
                $this->aFInputSetPrimary->delete();
            }
            $this->aFInputSetPrimary = $aFInputSetPrimary;
        }
    }

    /**
     * Renvoie l'InputSetPrimary associé à la cellule.
     *
     * @throws Core_Exception_UndefinedAttribute
     *
     * @return AF_Model_InputSet_Primary
     */
    public function getAFInputSetPrimary()
    {
        if ($this->aFInputSetPrimary === null) {
            throw new Core_Exception_UndefinedAttribute('InputSetPrimary has not be defined.');
        }
        return $this->aFInputSetPrimary;
    }

    /**
     * Spécifie la DocLibrary pour les AFInputSetPrimary de la cellule.
     *
     * @param Library $docLibrary
     *
     * @throws Core_Exception_Duplicate
     */
    public function setDocLibraryForAFInputSetsPrimary(Library $docLibrary=null)
    {
        if ($this->docLibraryForAFInputSetsPrimary !== $docLibrary) {
            if ($this->docLibraryForAFInputSetsPrimary !== null) {
                $this->docLibraryForAFInputSetsPrimary->delete();
            }
            $this->docLibraryForAFInputSetsPrimary = $docLibrary;
        }
    }

    /**
     * Renvoi la DocLibrary pour les AFInputSetPrimary de la cellule.
     *
     * @throws Core_Exception_UndefinedAttribute
     *
     * @return Library
     */
    public function getDocLibraryForAFInputSetsPrimary()
    {
        if ($this->docLibraryForAFInputSetsPrimary === null) {
            throw new Core_Exception_UndefinedAttribute('Doc Library for InputSetPrimary has not be defined.');
        }
        return $this->docLibraryForAFInputSetsPrimary;
    }

    /**
     * Renvoi la première Library parente trouvé pour la Bibliography de l'AFInputSetsPrimary.
     *
     * @throws Core_Exception_NotFound
     *
     * @return Library
     */
    public function getParentDocLibraryForAFInputSetsPrimary()
    {
        if ($this->getGranularity()->getCellsWithInputDocuments()) {
            return $this->getDocLibraryForAFInputSetsPrimary();
        } else {
            foreach ($this->getGranularity()->getBroaderGranularities() as $broaderGranularity) {
                if ($broaderGranularity->getCellsWithInputDocuments()) {
                    return $this->getParentCellForGranularity($broaderGranularity)->getDocLibraryForAFInputSetsPrimary();
                }
            }
        }
        throw new Core_Exception_NotFound('No broader Granularity provides a Library for the AFInputSetsPrimary.');
    }

    /**
     * Spécifie la DocBibliography pour l'AFInputSetPrimary de la cellule.
     *
     * @param Bibliography $docBibliography
     *
     * @throws Core_Exception_Duplicate
     */
    public function setDocBibliographyForAFInputSetPrimary(Bibliography $docBibliography=null)
    {
        if ($this->docBibliographyForAFInputSetPrimary !== $docBibliography) {
            if ($this->docBibliographyForAFInputSetPrimary !== null) {
                $this->docBibliographyForAFInputSetPrimary->delete();
            }
            $this->docBibliographyForAFInputSetPrimary = $docBibliography;
        }
    }

    /**
     * Renvoi la DocBibliography pour l'AFInputSetPrimary de la cellule.
     *
     * @throws Core_Exception_UndefinedAttribute
     *
     * @return Bibliography
     */
    public function getDocBibliographyForAFInputSetPrimary()
    {
        if ($this->docBibliographyForAFInputSetPrimary === null) {
            throw new Core_Exception_UndefinedAttribute('Doc Bibliography for InputSetPrimary has not be defined.');
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
     * Renvoi les derniers commentaires pour cette cellule et ses sous-cellules.
     *
     * @param int $count
     * @return Orga_Model_InputComment[]
     */
    public function getInputSetLatestComments($count)
    {
        // Ce code est un peu lourdingue, mais bon.
        // Transforme les Social_Model_Comment en Orga_Model_InputComment
        $comments = $this->socialCommentsForAFInputSetPrimary->map(
            function (Social_Model_Comment $comment) {
                return new Orga_Model_InputComment($this, $comment);
            }
        );
        /** @var \Doctrine\Common\Collections\Selectable|Collection $comments */
        // Ajoute à la collection les commentaires des sous-cellules
        foreach ($this->getChildCells() as $subCell) {
            foreach ($subCell->getSocialCommentsForInputSetPrimary() as $comment) {
                $comments->add(new Orga_Model_InputComment($subCell, $comment));
            }
        }

        // Trie par date et garde les X derniers seulement
        $criteria = Criteria::create();
        $criteria->orderBy(['creationDate' => Criteria::DESC]);
        $criteria->setMaxResults($count);
        return $comments->matching($criteria);
    }

    /**
     * Créé le Organization pour la simulation.
     *
     * @return int Identifiant unique du Organization.
     */
    public function createDWCube()
    {
        if (($this->dWCube === null) && ($this->getGranularity()->getCellsGenerateDWCubes())) {
            /** @var \DI\Container $container */
            $container = Zend_Registry::get('container');
            /** @var Orga_Service_ETLStructure $etlStructureService */
            $etlStructureService = $container->get('Orga_Service_ETLStructure');
            /** @var Orga_Service_ETLData $etlDataService */
            $etlDataService = $container->get('Orga_Service_ETLData');

            $this->dWCube = new DW_model_cube();
            $this->dWCube->setLabel($this->getLabel());

            $etlStructureService->populateCellDWCube($this);
            $etlStructureService->addGranularityDWReportsToCellDWCube($this);
            $etlDataService->populateDWResultsForCell($this);
        }
    }

    /**
     * Créé le Organization pour la simulation.
     *
     * @return int Identifiant unique du Organization.
     */
    public function deleteDWCube()
    {
        if ($this->dWCube !== null) {
            /** @var \DI\Container $container */
            $container = Zend_Registry::get('container');
            /** @var Orga_Service_ETLData $etlDataService */
            $etlDataService = $container->get('Orga_Service_ETLData');

            $etlDataService->clearDWResultsForCell($this);
            $this->dWCube->delete();
            $this->dWCube = null;
        }
    }

    /**
     * Renvoi le Organization de DW spécifique à la Cell.
     *
     * @throws Core_Exception_UndefinedAttribute
     *
     * @return DW_model_cube
     */
    public function getDWCube()
    {
        if ($this->dWCube === null) {
            throw new Core_Exception_UndefinedAttribute('DW Cube has not be defined.');
        }
        return $this->dWCube;
    }

    /**
     * Récupère l'ensemble des organizations de DW peuplés par la Cell.
     *
     * @return DW_model_cube[]
     */
    public function getPopulatedDWCubes()
    {
        $populatedDWCubes = array();

        if ($this->getGranularity()->getCellsGenerateDWCubes()) {
            $populatedDWCubes[] = $this->getDWCube();
        }

        foreach ($this->getParentCells() as $parentCell) {
            if ($parentCell->getGranularity()->getCellsGenerateDWCubes()) {
                $populatedDWCubes[] = $parentCell->getDWCube();
            }
        }

        return $populatedDWCubes;
    }

    /**
     * Récupère l'ensemble des Cell peuplant le cube de DW de la cellule.
     *
     * @return Orga_Model_Cell[]
     */
    public function getPopulatingCells()
    {
        // Renvoie une exception si la cellule ne possède pas de cube de DW.
        $this->getDWCube();

        $populatingCells = [];

        foreach ($this->getGranularity()->getOrganization()->getInputGranularities() as $inputGranularity) {
            if ($inputGranularity === $this->getGranularity()) {
                $populatingCells[] = $this;
            } else if ($inputGranularity->isNarrowerThan($this->getGranularity())) {
                foreach ($this->getChildCellsForGranularity($inputGranularity) as $inputChildCell) {
                    $populatingCells[] = $inputChildCell;
                }
            }
        }

        return $populatingCells;
    }

    /**
     * Créer les Result de DW issues de l'AF et les ajoute aux organizations peuplés par la cellule.
     */
    public function createDWResults()
    {
        foreach ($this->getPopulatedDWCubes() as $dWCube) {
            $this->createDWResultsForDWCube($dWCube);
        }
    }

    /**
     * Créer l'ensemble des résultats pour un organization de DW donné.
     *
     * @param DW_model_cube $dWCube
     */
    public function createDWResultsForDWCube(DW_model_cube $dWCube)
    {
        if (($this->aFInputSetPrimary === null) || ($this->aFInputSetPrimary->getOutputSet() === null)) {
            return;
        }

        foreach ($this->getAFInputSetPrimary()->getOutputSet()->getElements() as $outputElement) {
            $refClassifIndicator = $outputElement->getContextIndicator()->getIndicator()->getRef();
            try {
                $dWIndicator = DW_Model_Indicator::loadByRefAndCube($refClassifIndicator, $dWCube);
            } catch (Core_Exception_NotFound $e) {
                // Indexation selon l'indicateur de classif non trouvée. Impossible de créer le résultat.
                continue;
            }

            $dWResult = new DW_Model_Result($dWIndicator);
            $dWResult->setValue($outputElement->getValue());

            foreach ($outputElement->getIndexes() as $outputIndex) {
                try {
                    $dWAxis = DW_Model_Axis::loadByRefAndCube('c_'.$outputIndex->getRefAxis(), $dWCube);
                    $dWMember = DW_Model_Member::loadByRefAndAxis($outputIndex->getRefMember(), $dWAxis);
                    $dWResult->addMember($dWMember);
                } catch (Core_Exception_NotFound $e) {
                    // Indexation selon classif non trouvée.
                }

                foreach ($outputIndex->getMember()->getAllParents() as $classifParentMember) {
                    try {
                        $dWBroaderAxis = DW_Model_Axis::loadByRefAndCube('c_'.$classifParentMember->getAxis()->getRef(), $dWCube);
                        $dWParentMember = DW_Model_Member::loadByRefAndAxis($classifParentMember->getRef(), $dWBroaderAxis);
                        $dWResult->addMember($dWParentMember);
                    } catch (Core_Exception_NotFound $e) {
                        // Indexation selon classif non trouvée.
                    }
                }
            }

            /** @var Orga_Model_Member[] $indexingMembers */
            $indexingMembers = array();
            foreach ($this->getMembers() as $member) {
                array_push($indexingMembers, $member);
                $indexingMembers = array_merge($indexingMembers, $member->getAllParents());
            }
            foreach ($indexingMembers as $indexingMember) {
                try {
                    $dWAxis = DW_Model_Axis::loadByRefAndCube('o_'.$indexingMember->getAxis()->getRef(), $dWCube);
                    $dWMember = DW_Model_Member::loadByRefAndAxis($indexingMember->getRef(), $dWAxis);
                    $dWResult->addMember($dWMember);
                } catch (Core_Exception_NotFound $e) {
                    // Indexation selon orga non trouvée.
                }
            }

            $inputStatusDWAxis = $dWCube->getAxisByRef('inputStatus');
            if ($this->getAFInputSetPrimary()->isFinished()) {
                $dWResult->addMember($inputStatusDWAxis->getMemberByRef('finished'));
            } else {
                $dWResult->addMember($inputStatusDWAxis->getMemberByRef('completed'));
            }

            $this->dWResults->add($dWResult);
        }
    }

    /**
     * Supprime l'ensemble des résultats de l'InputSet de la cellule dans les organization de DW peuplés par la cellule.
     */
    public function deleteDWResults()
    {
        foreach ($this->dWResults->toArray() as $dWResult) {
            $this->dWResults->removeElement($dWResult);
            $dWResult->delete();
        }
    }

    /**
     * Supprime l'ensemble des résultats de l'InputSet de la cellule dans le organization de DW donné.
     *
     * @param DW_model_cube $dWCube
     */
    public function deleteDWResultsForDWCube(DW_model_cube $dWCube)
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
     * @param Library $docLibrary
     */
    public function setDocLibraryForSocialGenericAction(Library $docLibrary=null)
    {
        if ($this->docLibraryForSocialGenericActions !== $docLibrary) {
            if ($this->docLibraryForSocialGenericActions !== null) {
                $this->docLibraryForSocialGenericActions->delete();
                $this->docLibraryForSocialGenericActions = null;
            }
            $this->docLibraryForSocialGenericActions = $docLibrary;
        }
    }

    /**
     * Renvoi la DocLibrary pour les SocialGenericAction de la cellule.
     *
     * @throws Core_Exception_UndefinedAttribute
     *
     * @return Library
     */
    public function getDocLibraryForSocialGenericAction()
    {
        if ($this->docLibraryForSocialGenericActions === null) {
            throw new Core_Exception_UndefinedAttribute('Doc Library for Social GenericActions has not be defined.');
        }
        return $this->docLibraryForSocialGenericActions;
    }

    /**
     * Renvoi la première Library parente trouvé pour la Bibliography de les SocialGenericAction.
     *
     * @throws Core_Exception_NotFound
     *
     * @return Library
     */
    public function getParentDocLibraryForSocialGenericAction()
    {
        if ($this->getGranularity()->getCellsWithSocialGenericActions()) {
            return $this->getDocLibraryForSocialGenericAction();
        } else {
            foreach ($this->getGranularity()->getBroaderGranularities() as $broaderGranularity) {
                if ($broaderGranularity->getCellsWithSocialGenericActions()) {
                    return $this->getParentCellForGranularity($broaderGranularity)->getDocLibraryForSocialGenericAction();
                }
            }
        }
        throw new Core_Exception_NotFound('No broader Granularity provides a Library for the SocialGenericAction.');
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
     * @param Library $docLibrary
     */
    public function setDocLibraryForSocialContextAction(Library $docLibrary=null)
    {
        if ($this->docLibraryForSocialContextActions !== $docLibrary) {
            if ($this->docLibraryForSocialContextActions !== null) {
                $this->docLibraryForSocialContextActions->delete();
            }
            $this->docLibraryForSocialContextActions = $docLibrary;
        }
    }

    /**
     * Renvoi la DocLibrary pour les SocialContextAction de la cellule.
     *
     * @throws Core_Exception_UndefinedAttribute
     *
     * @return Library
     */
    public function getDocLibraryForSocialContextAction()
    {
        if ($this->docLibraryForSocialContextActions === null) {
            throw new Core_Exception_UndefinedAttribute('Doc Library for Social ContextActions has not be defined.');
        }
        return $this->docLibraryForSocialContextActions;
    }

    /**
     * Renvoi la première Library parente trouvé pour la Bibliography de l'SocialContextAction.
     *
     * @throws Core_Exception_NotFound
     *
     * @return Library
     */
    public function getParentDocLibraryForSocialContextAction()
    {
        if ($this->getGranularity()->getCellsWithSocialContextActions()) {
            return $this->getDocLibraryForSocialContextAction();
        } else {
            foreach ($this->getGranularity()->getBroaderGranularities() as $broaderGranularity) {
                if ($broaderGranularity->getCellsWithSocialContextActions()) {
                    return $this->getParentCellForGranularity($broaderGranularity)->getDocLibraryForSocialContextAction();
                }
            }
        }
        throw new Core_Exception_NotFound('No broader Granularity provides a Library for the SocialContextAction.');
    }

    /**
     * @return AbstractCellRole[]
     */
    public function getAllRoles()
    {
        return array_merge(
            $this->adminRoles->toArray(),
            $this->managerRoles->toArray(),
            $this->contributorRoles->toArray(),
            $this->observerRoles->toArray()
        );
    }

    /**
     * @return CellAdminRole[]
     */
    public function getAdminRoles()
    {
        return $this->adminRoles;
    }

    /**
     * API utilisée uniquement par CellAdminRole
     *
     * @param CellAdminRole $adminRole
     */
    public function addAdminRole(CellAdminRole $adminRole)
    {
        $this->adminRoles->add($adminRole);
    }

    /**
     * API utilisée uniquement par CellAdminRole
     *
     * @param CellAdminRole $adminRole
     */
    public function removeAdminRole(CellAdminRole $adminRole)
    {
        $this->adminRoles->removeElement($adminRole);
    }

    /**
     * @return CellManagerRole[]
     */
    public function getManagerRoles()
    {
        return $this->managerRoles;
    }

    /**
     * API utilisée uniquement par CellManagerRole
     *
     * @param CellManagerRole $managerRole
     */
    public function addManagerRole(CellManagerRole $managerRole)
    {
        $this->managerRoles->add($managerRole);
    }

    /**
     * API utilisée uniquement par CellManagerRole
     *
     * @param CellManagerRole $managerRole
     */
    public function removeManagerRole(CellManagerRole $managerRole)
    {
        $this->managerRoles->removeElement($managerRole);
    }

    /**
     * @return CellContributorRole[]
     */
    public function getContributorRoles()
    {
        return $this->contributorRoles;
    }

    /**
     * API utilisée uniquement par CellContributorRole
     *
     * @param CellContributorRole $contributorRole
     */
    public function addContributorRole(CellContributorRole $contributorRole)
    {
        $this->contributorRoles->add($contributorRole);
    }

    /**
     * API utilisée uniquement par CellContributorRole
     *
     * @param CellContributorRole $contributorRole
     */
    public function removeContributorRole(CellContributorRole $contributorRole)
    {
        $this->contributorRoles->removeElement($contributorRole);
    }

    /**
     * @return CellObserverRole[]
     */
    public function getObserverRoles()
    {
        return $this->observerRoles;
    }

    /**
     * API utilisée uniquement par CellObserverRole
     *
     * @param CellObserverRole $observerRole
     */
    public function addObserverRole(CellObserverRole $observerRole)
    {
        $this->observerRoles->add($observerRole);
    }

    /**
     * API utilisée uniquement par CellObserverRole
     *
     * @param CellObserverRole $observerRole
     */
    public function removeObserverRole(CellObserverRole $observerRole)
    {
        $this->observerRoles->removeElement($observerRole);
    }

    /**
     * @return string Représentation textuelle de l'unité
     */
    public function __toString()
    {
        return $this->getMembersHashKey();
    }
}
