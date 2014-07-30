<?php

namespace Orga\Domain;

use AF\Domain\AF;
use AF\Domain\InputSet\PrimaryInputSet;
use Core\Translation\TranslatedString;
use Core_Exception;
use Core_Exception_InvalidArgument;
use Core_Exception_NotFound;
use Core_Exception_TooMany;
use Core_Exception_UndefinedAttribute;
use Core_Model_Entity;
use Core_Model_Query;
use Doc\Domain\Library;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\Common\Collections\Criteria;
use Doctrine\Common\Collections\Selectable;
use DW\Domain\Cube;
use DW\Domain\Result;
use Orga\Domain\Cell\CellInputComment;
use MyCLabs\ACL\Model\EntityResource;
use Orga\Domain\Service\OrgaDomainHelper;
use Orga\Domain\ACL\AbstractCellRole;
use Orga\Domain\ACL\CellAdminRole;
use Orga\Domain\ACL\CellContributorRole;
use Orga\Domain\ACL\CellManagerRole;
use Orga\Domain\ACL\CellObserverRole;

/**
 * Cell
 *
 * @author valentin.claras
 */
class Cell extends Core_Model_Entity implements EntityResource
{
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

    // Constantes des statuts de saisie.
    const INPUT_STATUS_AF_NOT_CONFIGURED = 'statusAFNotConfigured';
    const INPUT_STATUS_NOT_STARTED = 'statusNotStarted';
    const INPUT_STATUS_INPUT_INCOMPLETE = "statusInputIncomplete";
    const INPUT_STATUS_CALCULATION_INCOMPLETE = "statusCalculationIncomplete";
    const INPUT_STATUS_COMPLETE = "statusComplete";
    const INPUT_STATUS_FINISHED = "statusFinished";

    /**
     * Etat non débuté de l'inventaire.
     */
    const INVENTORY_STATUS_NOTLAUNCHED = 'notLaunched';
    /**
     * Etat actif de l'inventaire.
     */
    const INVENTORY_STATUS_ACTIVE = 'active';
    /**
     * Etat terminé de l'inventaire.
     */
    const INVENTORY_STATUS_CLOSED = 'closed';


    /**
     * @var int
     */
    protected $id;

    /**
     * @var Granularity
     */
    protected $granularity;

    /**
     * @var Collection|Member[]
     */
    protected $members;

    /**
     * @var string
     */
    protected $membersHashKey = '';

    /**
     * @var string
     */
    protected $tag = '';

    /**
     * @var bool
     */
    protected $relevant = true;

    /**
     * @var bool
     */
    protected $allParentsRelevant = true;

    /**
     * @var string
     * @see STATUS_NOTLAUNCHED;
     * @see STATUS_ACTIVE;
     * @see STATUS_CLOSED;
     */
    protected $inventoryStatus = self::INVENTORY_STATUS_NOTLAUNCHED;

    /**
     * @var Collection|SubCellsGroup[]
     */
    protected $subCellsGroups = null;

    /**
     * @var string
     */
    protected $inputStatus = self::INPUT_STATUS_AF_NOT_CONFIGURED;

    /**
     * @var PrimaryInputSet|null
     */
    protected $aFInputSetPrimary = null;

    /**
     * @var int
     */
    protected $numberOfInconsistenciesInInputSet = 0;

    /**
     * @var Library
     */
    protected $docLibraryForAFInputSetPrimary = null;

    /**
     * @var Collection|CellInputComment[]
     */
    protected $commentsForAFInputSetPrimary = null;

    /**
     * @var Cube
     */
    protected $dWCube = null;

    /**
     * @var Collection|Result[]
     */
    protected $dWResults = null;

    /**
     * @var \Orga\Domain\ACL\AbstractCellRole[]|Collection
     */
    protected $roles;


    /**
     * @param Granularity $granularity
     * @param Member[] $members
     */
    public function __construct(Granularity $granularity, array $members = [])
    {
        $this->members = new ArrayCollection();
        $this->subCellsGroups = new ArrayCollection();
        $this->commentsForAFInputSetPrimary = new ArrayCollection();
        $this->dWResults = new ArrayCollection();
        $this->roles = new ArrayCollection();

        $this->granularity = $granularity;
        foreach ($members as $member) {
            $this->members->add($member);
            $member->addCell($this);
        }
        $this->updateMembersHashKey();
        $this->updateTag();

        // Création du cube de DW.
        $this->createDWCube();
        // Création du SubCellsGroup.
        foreach ($this->granularity->getInputGranularities() as $inputGranularity) {
            new SubCellsGroup($this, $inputGranularity);
        }
        // Création de la Library des Input.
        $this->enableDocLibraryForAFInputSetPrimary();

        $this->updateHierarchy();
    }

    /**
     * @param PrimaryInputSet $aFInputSetPrimary
     * @throws Core_Exception_NotFound
     * @return Cell
     */
    public static function loadByAFInputSetPrimary(PrimaryInputSet $aFInputSetPrimary)
    {
        return self::getEntityRepository()->loadBy(array('aFInputSetPrimary' => $aFInputSetPrimary));
    }

    /**
     * @param Cube $dWCube
     * @return Cell
     */
    public static function loadByDWCube(Cube $dWCube)
    {
        return self::getEntityRepository()->loadBy(array('dWCube' => $dWCube));
    }

    /**
     * @param Library $docLibrary
     * @return Cell
     */
    public static function loadByDocLibraryForAFInputSetsPrimary(Library $docLibrary)
    {
        return self::getEntityRepository()->loadBy(['docLibraryForAFInputSetPrimary' => $docLibrary]);
    }

    /**
     * @throws \Core_Exception_InvalidArgument
     */
    public function removeFromMember()
    {
        if ($this->getGranularity()->getCells()->contains($this)) {
            throw new Core_Exception_InvalidArgument('RemoveFromMember can only be called by the Granularity.');
        }

        $this->granularity = null;
        foreach ($this->getMembers() as $member) {
            $member->removeCell($this);
        }
    }

    /**
     * @return string
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @return Granularity
     */
    public function getGranularity()
    {
        return $this->granularity;
    }

    /**
     * @return Workspace
     */
    public function getWorkspace()
    {
        return $this->getGranularity()->getWorkspace();
    }

    /**
     * @param Member $member
     * @return boolean
     */
    public function hasMember(Member $member)
    {
        return $this->members->contains($member);
    }

    /**
     * @return bool
     */
    public function hasMembers()
    {
        return !$this->members->isEmpty();
    }

    /**
     * @return Member[]
     */
    public function getMembers()
    {
        return $this->members->toArray();
    }

    /**
     * @param Axis $axis
     * @throws Core_Exception_InvalidArgument
     * @return Member
     */
    public function getMemberForAxis(Axis $axis)
    {
        if (!$this->getGranularity()->hasAxis($axis)) {
            throw new Core_Exception_InvalidArgument(
                'Given axis needs to be part of the cell\'s granularity.'
            );
        }

        $criteriaAxis = new Criteria();
        $criteriaAxis->where($criteriaAxis->expr()->eq('axis', $axis));
        $member = $this->members->matching($criteriaAxis)->toArray();
        return array_pop($member);
    }

    public function updateMembersHashKey()
    {
        $this->membersHashKey = self::buildMembersHashKey($this->getMembers());
    }

    /**
     * @return string
     */
    public function getMembersHashKey()
    {
        return $this->membersHashKey;
    }

    /**
     * @param Member[] $listMembers
     * @return string
     */
    public static function buildMembersHashKey(array $listMembers)
    {
        @usort($listMembers, [Member::class, 'orderMembers']);
        $membersRef = [];

        foreach ($listMembers as $member) {
            $membersRef[] = $member->getCompleteRef();
        }

        return sha1(implode(self::REF_SEPARATOR, $membersRef));
    }

    public function updateTag()
    {
        if (!$this->hasMembers()) {
            $this->tag = Workspace::PATH_SEPARATOR;
        } else {
            $membersTagParts = array();
            $members = $this->getMembers();
            @usort($members, [Member::class, 'orderMembers']);
            foreach ($members as $member) {
                $membersTagParts[] = $member->getTag();
            }
            $this->tag = implode(Workspace::PATH_JOIN, $membersTagParts);
        }
    }

    /**
     * @return string
     */
    public function getTag()
    {
        return $this->tag;
    }

    /**
     * @param bool $relevant
     * @throws Core_Exception
     */
    public function setRelevant($relevant)
    {
        if (!$this->getGranularity()->getCellsControlRelevance()) {
            throw new Core_Exception('Relevance can only be defined if the granularity permits it.');
        }

        if ($relevant != $this->relevant) {
            $this->relevant = $relevant;
            // Si les cellules parentes ne sont pas pertinentes,
            //  alors modifier celle-ci n'impactera pas les cellules enfantes.
            if ($this->getAllParentsRelevant() === true) {
                if ($this->relevant) {
                    // Vérification de l'état des cellules filles.
                    foreach ($this->getChildCells() as $childCell) {
                        $childCell->enable();
                    }
                } else {
                    // Désactivation de totes les cellules filles.
                    foreach ($this->getChildCells() as $childCell) {
                        $childCell->disable();
                    }
                }
            }
        }
    }

    public function disable()
    {
        $this->allParentsRelevant = false;
    }

    /**
     * Tente de réactiver la cellule et met à jour la pertinence de ses cellules parentes.
     */
    public function enable()
    {
        $this->allParentsRelevant = false;

        // Vérification que chaque membre possède tous ses membres parents.
        foreach ($this->getMembers() as $member) {
            if (count($member->getAxis()->getDirectBroaders()) !== count($member->getDirectParents())) {
                return;
            }
        }

        // Sinon vérification que les cellules parentes existent et sont pertinentes.
        foreach ($this->getGranularity()->getBroaderGranularities() as $broaderGranularity) {
            try {
                $parentCell = $this->getParentCellForGranularity($broaderGranularity);
                if (!$parentCell->getRelevant()) {
                    return;
                }
            } catch (Core_Exception_NotFound $e) {
                // Pas de cellule parente pour cette granularité.
                return;
            }
        }

        $this->allParentsRelevant = true;
    }

    /**
     * @return bool
     */
    public function getRelevant()
    {
        return $this->relevant;
    }

    /**
     * @return bool
     */
    public function getAllParentsRelevant()
    {
        return $this->allParentsRelevant;
    }

    /**
     * @return bool
     */
    public function isRelevant()
    {
        return ($this->getRelevant() && $this->getAllParentsRelevant());
    }

    /**
     * Met à jour les attributs dépendants de la hiérarchie.
     */
    public function updateHierarchy()
    {
        // Mise à jour de la pertinence.
        $this->enable();

        // Mise à jour du status de l'inventaire.
        $granularityForInventoryStatus = $this->getWorkspace()->getGranularityForInventoryStatus();
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
                $this->updateInventoryStatus(self::INVENTORY_STATUS_NOTLAUNCHED);
            }
        }

        // Mise à jour du nombre d'incohérence issues de la saisie précédente.
        OrgaDomainHelper::getCellInputUpdater()->updateInconsistencyForCell($this);

        //@todo Mettre à jour la cascade des autorisations.
    }

    /**
     * @return TranslatedString
     */
    public function getLabel()
    {
        if ($this->members->isEmpty()) {
            return TranslatedString::untranslated(__('Orga', 'navigation', 'labelGlobalCell'));
        }

        $labels = [];
        $members = $this->getMembers();
        @usort($members, [Member::class, 'orderMembers']);
        foreach ($members as $member) {
            $labels[] = $member->getLabel();
        }

        return TranslatedString::implode(self::LABEL_SEPARATOR, $labels);
    }

    /**
     * @return TranslatedString
     */
    public function getExtendedLabel()
    {
        if ($this->members->isEmpty()) {
            return TranslatedString::untranslated(__('Orga', 'navigation', 'labelGlobalCellExtended'));
        }

        $labels = [];
        $members = $this->getMembers();
        @usort($members, [Member::class, 'orderMembers']);
        foreach ($members as $member) {
            $labels[] = $member->getExtendedLabel();
        }

        return TranslatedString::implode(self::LABEL_SEPARATOR, $labels);
    }

    /**
     * @param Granularity $broaderGranularity
     * @return Member[]
     */
    protected function getParentMembersForGranularity(Granularity $broaderGranularity)
    {
        $parentMembers = array();

        foreach ($this->getMembers() as $member) {
            foreach ($broaderGranularity->getAxes() as $broaderAxis) {
                if ($member->getAxis()->isNarrowerThan($broaderAxis)) {
                    $parentMembers[$broaderAxis->getRef()] = $member->getParentForAxis($broaderAxis);
                } elseif ($member->getAxis() === $broaderAxis) {
                    $parentMembers[$broaderAxis->getRef()] = $member;
                }
            }
        }

        return $parentMembers;
    }

    /**
     * @param Axis[] $axes
     * @return Member[]
     */
    public function getChildMembersForAxes(array $axes)
    {
        $childMembers = array();

        foreach ($axes as $axis) {
            $narrowerAxisRef = $axis->getRef();
            foreach ($this->getMembers() as $cellMember) {
                if ($cellMember->getAxis()->isBroaderThan($axis)) {
                    if (!isset($childMembers[$narrowerAxisRef])) {
                        $childMembers[$narrowerAxisRef] = $cellMember->getChildrenForAxis($axis);
                    } else {
                        $childMembers[$narrowerAxisRef] = array_intersect(
                            $childMembers[$narrowerAxisRef],
                            $cellMember->getChildrenForAxis($axis)
                        );
                    }
                } elseif ($cellMember->getAxis() === $axis) {
                    $childMembers[$narrowerAxisRef] = [$cellMember];
                }
            }
            if (!isset($childMembers[$narrowerAxisRef])) {
                $childMembers[$narrowerAxisRef] = $axis->getOrderedMembers()->toArray();
            }
        }

        return $childMembers;
    }

    /**
     * @param Granularity $broaderGranularity
     * @throws Core_Exception_InvalidArgument The given granularity is not broader than the current
     * @return Cell
     */
    public function getParentCellForGranularity(Granularity $broaderGranularity)
    {
        if (!$this->getGranularity()->isNarrowerThan($broaderGranularity)) {
            throw new Core_Exception_InvalidArgument('The given granularity is not broader than the current.');
        }

        return $broaderGranularity->getCellByMembers($this->getParentMembersForGranularity($broaderGranularity));
    }

    /**
     * @return Cell[]
     */
    public function getParentCells()
    {
        $parentCells = [];

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
     * @param Cell $cell
     * @return bool
     */
    public function isParentOf(Cell $cell)
    {
        return $cell->isChildOf($this);
    }

    /**
     * @param Granularity $narrowerGranularity
     * @throws Core_Exception_InvalidArgument The given granularity is not narrower than the current
     * @return Collection|Selectable|Cell[]
     */
    public function getChildCellsForGranularity(Granularity $narrowerGranularity)
    {
        if (!$narrowerGranularity->isNarrowerThan($this->getGranularity())) {
            throw new Core_Exception_InvalidArgument('The given granularity is not narrower than the current.');
        }

        $criteria = Criteria::create();
        foreach (explode(Workspace::PATH_JOIN, $this->getTag()) as $pathTag) {
            $criteria->andWhere($criteria->expr()->contains('tag', $pathTag));
        }
        $criteria->orderBy(['tag' => 'ASC']);
        return $narrowerGranularity->getCells()->matching($criteria);
    }

    /**
     * @throws Core_Exception_InvalidArgument The given granularity is not narrower than the current
     * @return Cell[]
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
     * @param Cell $cell
     * @return bool
     */
    public function isChildOf(Cell $cell)
    {
        foreach (explode(Workspace::PATH_JOIN, $cell->getTag()) as $pathTag) {
            if (strpos($this->getTag(), $pathTag) === false) {
                return false;
            }
        }
        return true;
    }

    /**
     * @param Granularity $narrowerGranularity
     * @param Core_Model_Query|null $queryParameters
     * @throws Core_Exception_InvalidArgument The given granularity is not narrower than the current
     * @return Cell[]
     */
    public function loadChildCellsForGranularity(
        Granularity $narrowerGranularity,
        Core_Model_Query $queryParameters = null
    ) {
        if (!($this->getGranularity()->isBroaderThan($narrowerGranularity))) {
            throw new Core_Exception_InvalidArgument('The given granularity is not narrower than the current');
        }
        if ($queryParameters === null) {
            $queryParameters = new Core_Model_Query();
            $queryParameters->order->addOrder('tag');
        }

        $childMembersForGranularity = $this->getChildMembersForAxes($narrowerGranularity->getAxes());

        // Si l'un des axes de la granularité ne possède pas d'enfants, alors il n'y a pas de cellules enfantes.
        foreach ($childMembersForGranularity as $childAxisMembersForGranularity) {
            if (empty($childAxisMembersForGranularity)) {
                return [];
            }
        }

        $childMembers = array(
            array(
                'granularity' => $narrowerGranularity,
                'members' => $childMembersForGranularity
            )
        );
        return self::getEntityRepository()->loadByMembers($childMembers, $queryParameters);
    }

    /**
     * @param Granularity $narrowerGranularity
     * @param Core_Model_Query $queryParameters
     * @throws Core_Exception_InvalidArgument The given granularity is not narrower than the current
     * @return int
     */
    public function countTotalChildCellsForGranularity(
        Granularity $narrowerGranularity,
        Core_Model_Query $queryParameters = null
    ) {
        if (!($this->getGranularity()->isBroaderThan($narrowerGranularity))) {
            throw new Core_Exception_InvalidArgument('The given granularity is not narrower than the current.');
        }
        if ($queryParameters === null) {
            $queryParameters = new Core_Model_Query();
            $queryParameters->order->addOrder(self::QUERY_TAG);
        }

        $childMembersForGranularity = $this->getChildMembersForAxes($narrowerGranularity->getAxes());

        // Si l'un des axes de la granularité ne possède pas d'enfants, alors il n'y a pas de cellules enfantes.
        foreach ($childMembersForGranularity as $childAxisMembersForGranularity) {
            if (empty($childAxisMembersForGranularity)) {
                return 0;
            }
        }

        $childMembers = array(
            array(
                'granularity' => $narrowerGranularity,
                'members' => $childMembersForGranularity
            )
        );
        return self::getEntityRepository()->countTotalByMembers($childMembers, $queryParameters);
    }

    /**
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
     * @param Axis $axis
     * @return Cell
     * @throws Core_Exception_InvalidArgument
     */
    public function getPreviousCellForAxis(Axis $axis)
    {
        if (!$this->getGranularity()->hasAxis($axis) || !$axis->isMemberPositioning()) {
            throw new Core_Exception_InvalidArgument(
                'Given axis needs to be used by this cell\'s granularity and allows member positioning.'
            );
        }

        $axisMember = $this->getMemberForAxis($axis);
        if ($axisMember->getPosition() === 1) {
            return null;
        }

        return $this->getGranularity()->getCellByMembers(
            array_merge(
                array_diff($this->getMembers(), [$axisMember]),
                [$axisMember->getPreviousMember()]
            )
        );
    }

    /**
     * @param Axis $axis
     * @return Cell
     * @throws Core_Exception_InvalidArgument
     */
    public function getNextCellForAxis(Axis $axis)
    {
        if (!$this->getGranularity()->hasAxis($axis) || !$axis->isMemberPositioning()) {
            throw new Core_Exception_InvalidArgument(
                'Given axis needs to be used by this cell\'s granularity and allows member positioning.'
            );
        }

        $axisMember = $this->getMemberForAxis($axis);
        if ($axisMember->getPosition() === $axisMember->getLastEligiblePosition()) {
            return null;
        }

        return $this->getGranularity()->getCellByMembers(
            array_merge(
                array_diff($this->getMembers(), [$axisMember]),
                [$axisMember->getNextMember()]
            )
        );
    }

    /**
     * @param string $inventoryStatus
     * @throws Core_Exception
     * @throws Core_Exception_InvalidArgument
     * @see self::INVENTORY_STATUS_ACTIVE
     * @see self::INVENTORY_STATUS_CLOSED
     * @see self::INVENTORY_STATUS_NOTLAUNCHED
     */
    public function setInventoryStatus($inventoryStatus)
    {
        if ($this->getGranularity() !== $this->getWorkspace()->getGranularityForInventoryStatus()) {
            throw new Core_Exception('Inventory status can only be defined in the inventory granularity.');
        }

        if ($this->inventoryStatus !== $inventoryStatus) {
            $acceptedStatus = [
                self::INVENTORY_STATUS_ACTIVE,
                self::INVENTORY_STATUS_CLOSED,
                self::INVENTORY_STATUS_NOTLAUNCHED
            ];
            if (!in_array($inventoryStatus, $acceptedStatus)) {
                throw new Core_Exception_InvalidArgument('Inventory status must be a class constant (INVENTORY_STATUS_[..]).');
            }

            $this->inventoryStatus = $inventoryStatus;

            foreach ($this->getChildCells() as $childCell) {
                $childCell->updateInventoryStatus($this->inventoryStatus);
            }
        }
    }

    /**
     * @param string $inventoryStatus
     */
    private function updateInventoryStatus($inventoryStatus)
    {
        $this->inventoryStatus = $inventoryStatus;
    }

    /**
     * @return string
     * @see self::INVENTORY_STATUS_ACTIVE
     * @see self::INVENTORY_STATUS_CLOSED
     * @see self::INVENTORY_STATUS_NOTLAUNCHED
     */
    public function getInventoryStatus()
    {
        return $this->inventoryStatus;
    }

    /**
     * @param SubCellsGroup $subCellsGroup
     * @throws Core_Exception_InvalidArgument
     */
    public function addSubCellsGroup(SubCellsGroup $subCellsGroup)
    {
        if ($subCellsGroup->getContainerCell() !== $this) {
            throw new Core_Exception_InvalidArgument();
        }

        if (!$this->hasSubCellsGroup($subCellsGroup)) {
            $this->subCellsGroups->add($subCellsGroup);
        }
    }

    /**
     * @param SubCellsGroup $subCellsGroup
     * @return boolean
     */
    public function hasSubCellsGroup(SubCellsGroup $subCellsGroup)
    {
        return $this->subCellsGroups->contains($subCellsGroup);
    }

    /**
     * @param Granularity $inputGranularity
     * @throws Core_Exception_NotFound
     * @throws Core_Exception_TooMany
     * @return SubCellsGroup
     */
    public function getSubCellsGroupForInputGranularity(Granularity $inputGranularity)
    {
        $criteria = Criteria::create();
        $criteria->where($criteria->expr()->eq('inputGranularity', $inputGranularity));
        $subCellsGroup = $this->subCellsGroups->matching($criteria)->toArray();

        if (empty($subCellsGroup)) {
            throw new Core_Exception_NotFound('No SubCellsGroup for input Granularity "'.$inputGranularity.'".');
        } elseif (count($subCellsGroup) > 1) {
            throw new Core_Exception_TooMany('Too many SubCellsGroup for input Granularity "'.$inputGranularity.'".');
        }

        return array_pop($subCellsGroup);
    }

    /**
     * @param SubCellsGroup $subCellsGroup
     */
    public function removeSubCellsGroup(SubCellsGroup $subCellsGroup)
    {
        if ($this->hasSubCellsGroup($subCellsGroup)) {
            $this->subCellsGroups->removeElement($subCellsGroup);
            $subCellsGroup->delete();
        }
    }

    /**
     * @return bool
     */
    public function hasSubCellsGroups()
    {
        return !$this->subCellsGroups->isEmpty();
    }

    /**
     * @return SubCellsGroup[]
     */
    public function getSubCellsGroups()
    {
        return $this->subCellsGroups->toArray();
    }

    /**
     * @see self::INPUT_STATUS_AF_NOT_CONFIGURED
     * @see self::INPUT_STATUS_NOT_STARTED
     * @see self::INPUT_STATUS_INPUT_INCOMPLETE
     * @see self::INPUT_STATUS_CALCULATION_INCOMPLETE
     * @see self::INPUT_STATUS_COMPLETE
     * @see self::INPUT_STATUS_FINISHED
     */
    public function updateInputStatus()
    {
        if ($this->aFInputSetPrimary !== null) {
            switch ($this->aFInputSetPrimary->getStatus()) {
                case PrimaryInputSet::STATUS_FINISHED:
                    $this->inputStatus = self::INPUT_STATUS_FINISHED;
                    break;
                case PrimaryInputSet::STATUS_COMPLETE:
                    $this->inputStatus = self::INPUT_STATUS_COMPLETE;
                    break;
                case PrimaryInputSet::STATUS_CALCULATION_INCOMPLETE:
                    $this->inputStatus = self::INPUT_STATUS_CALCULATION_INCOMPLETE;
                    break;
                case PrimaryInputSet::STATUS_INPUT_INCOMPLETE:
                    $this->inputStatus = self::INPUT_STATUS_INPUT_INCOMPLETE;
                    break;
                default:
                    $this->inputStatus = self::INPUT_STATUS_NOT_STARTED;
                    break;
            }
        } else {
            if (($this->getGranularity()->isInput()) && ($this->getInputAFUsed() !== null)) {
                $this->inputStatus = self::INPUT_STATUS_NOT_STARTED;
            } else {
                $this->inputStatus = self::INPUT_STATUS_AF_NOT_CONFIGURED;
            }
        }
    }

    /**
     * @return string
     * @see self::INPUT_STATUS_AF_NOT_CONFIGURED
     * @see self::INPUT_STATUS_NOT_STARTED
     * @see self::INPUT_STATUS_INPUT_INCOMPLETE
     * @see self::INPUT_STATUS_CALCULATION_INCOMPLETE
     * @see self::INPUT_STATUS_COMPLETE
     * @see self::INPUT_STATUS_FINISHED
     */
    public function getInputStatus()
    {
        return $this->inputStatus;
    }

    /**
     * @param PrimaryInputSet $aFInputSetPrimary
     * @throws Core_Exception_Duplicate
     */
    public function setAFInputSetPrimary(PrimaryInputSet $aFInputSetPrimary = null)
    {
        if ($this->aFInputSetPrimary !== $aFInputSetPrimary) {
            if (($this->aFInputSetPrimary !== null) && ($aFInputSetPrimary !== null)) {
                throw new Core_Exception_Duplicate('InputSetPrimary as already be defined.');
            }
            if ($this->aFInputSetPrimary !== null) {
                $this->aFInputSetPrimary->delete();
                $this->numberOfInconsistenciesInInputSet = 0;
            }
            $this->aFInputSetPrimary = $aFInputSetPrimary;
        }
    }

    /**
     * @return PrimaryInputSet|null
     */
    public function getAFInputSetPrimary()
    {
        return $this->aFInputSetPrimary;
    }

    /**
     * @return PrimaryInputSet|null
     */
    public function getPreviousAFInputSetPrimary()
    {
        $timeAxis = $this->getWorkspace()->getTimeAxis();
        if ($timeAxis && $this->getGranularity()->hasAxis($timeAxis)) {
            $previousCell = $this->getPreviousCellForAxis($timeAxis);
            if ($previousCell) {
                return $previousCell->getAFInputSetPrimary();
            }
        }

        return null;
    }

    /**
     * @return AF
     */
    public function getInputAFUsed()
    {
        $granularity = $this->getGranularity();
        try {
            if ($granularity === $granularity->getInputConfigGranularity()) {
                return $this->getSubCellsGroupForInputGranularity($granularity)->getAF();
            } else {
                return $this->getParentCellForGranularity(
                    $granularity->getInputConfigGranularity()
                )->getSubCellsGroupForInputGranularity($granularity)->getAF();
            }
        } catch (Core_Exception_UndefinedAttribute $e) {
        } catch (Core_Exception_NotFound $e) {
            // Pas d'AF spécifié.
        }
        return null;
    }

    /**
     * @param $number
     * @return $this
     */
    public function setNumberOfInconsistenciesInInputSet($number)
    {
        $this->numberOfInconsistenciesInInputSet = (int) $number;

        return $this;
    }

    /**
     * @return int
     */
    public function getNumberOfInconsistenciesInInputSet()
    {
        return $this->numberOfInconsistenciesInInputSet;
    }

    public function enableDocLibraryForAFInputSetPrimary()
    {
        if (($this->getGranularity()->isInput()) && ($this->docLibraryForAFInputSetPrimary === null)) {
            $this->docLibraryForAFInputSetPrimary = new Library();
        }
    }

    public function disableDocLibraryForAFInputSetPrimary()
    {
        if ((!$this->getGranularity()->isInput()) && ($this->docLibraryForAFInputSetPrimary !== null)) {
            $this->docLibraryForAFInputSetPrimary->delete();
            $this->docLibraryForAFInputSetPrimary = null;
        }
    }

    /**
     * @throws Core_Exception_UndefinedAttribute
     * @return Library
     */
    public function getDocLibraryForAFInputSetPrimary()
    {
        if ($this->docLibraryForAFInputSetPrimary === null) {
            throw new Core_Exception_UndefinedAttribute('The Doc library for the cell has not be set.');
        }
        return $this->docLibraryForAFInputSetPrimary;
    }

    /**
     * @param CellInputComment $comment
     * @return bool
     */
    public function hasCommentForInputSetPrimary(CellInputComment $comment)
    {
        return $this->commentsForAFInputSetPrimary->contains($comment);
    }

    /**
     * @param CellInputComment $comment
     */
    public function addCommentForInputSetPrimary(CellInputComment $comment)
    {
        if (!($this->hasCommentForInputSetPrimary($comment))) {
            $this->commentsForAFInputSetPrimary->add($comment);
        }
    }

    /**
     * @param CellInputComment $comment
     */
    public function removeCommentForInputSetPrimary(CellInputComment $comment)
    {
        if ($this->hasCommentForInputSetPrimary($comment)) {
            $this->commentsForAFInputSetPrimary->removeElement($comment);
        }
    }

    /**
     * @return bool
     */
    public function hasCommentsForInputSetPrimary()
    {
        return !$this->commentsForAFInputSetPrimary->isEmpty();
    }

    /**
     * @return CellInputComment[]
     */
    public function getCommentsForInputSetPrimary()
    {
        return $this->commentsForAFInputSetPrimary->toArray();
    }

    public function createDWCube()
    {
        if (($this->dWCube === null) && ($this->getGranularity()->getCellsGenerateDWCubes())) {
            $this->dWCube = new Cube();
            $this->dWCube->setLabel(clone $this->getLabel());

            OrgaDomainHelper::getETLStructureService()->populateCellDWCube($this);
            OrgaDomainHelper::getOrgaReportFactory()->addGranularityDWReportsToCellDWCube($this);

            // Peuplement du cube avec les résultats existants.
            OrgaDomainHelper::getETLData()->populateDWResultsForCell($this);
        }
    }

    public function deleteDWCube()
    {
        if ($this->dWCube !== null) {
            // Suppression de tous les résultats.
            OrgaDomainHelper::getETLData()->clearDWResultsForCell($this);

            $this->dWCube->delete();
            $this->dWCube = null;
        }
    }

    /**
     * @throws Core_Exception_UndefinedAttribute
     * @return Cube
     */
    public function getDWCube()
    {
        if ($this->dWCube === null) {
            throw new Core_Exception_UndefinedAttribute('DW Cube has not be defined.');
        }
        return $this->dWCube;
    }

    /**
     * @return Cube[]
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
     * @return Cell[]
     */
    public function getPopulatingCells()
    {
        // Renvoie une exception si la cellule ne possède pas de cube de DW.
        if ($this->getGranularity()->getCellsGenerateDWCubes()) {
            $this->getDWCube();
        }

        $populatingCells = [];

        foreach ($this->getWorkspace()->getInputGranularities() as $inputGranularity) {
            if (($inputGranularity === $this->getGranularity()) && ($this->isRelevant())) {
                $populatingCells[] = $this;
            } elseif ($inputGranularity->isNarrowerThan($this->getGranularity())) {
                foreach ($this->getChildCellsForGranularity($inputGranularity) as $inputChildCell) {
                    if ($inputChildCell->isRelevant()) {
                        $populatingCells[] = $inputChildCell;
                    }
                }
            }
        }

        return $populatingCells;
    }

    public function createDWResults()
    {
        foreach ($this->getPopulatedDWCubes() as $dWCube) {
            $this->createDWResultsForDWCube($dWCube);
        }
    }

    /**
     * @param Cube $dWCube
     */
    public function createDWResultsForDWCube(Cube $dWCube)
    {
        if (($this->aFInputSetPrimary === null) || ($this->aFInputSetPrimary->getOutputSet() === null)) {
            return;
        }

        foreach ($this->getAFInputSetPrimary()->getOutputSet()->getElements() as $outputElement) {
            $classificationIndicatorRef = $outputElement->getContextIndicator()->getIndicator()->getLibrary()
                . '_' . $outputElement->getContextIndicator()->getIndicator()->getRef();
            try {
                $dWIndicator = $dWCube->getIndicatorByRef($classificationIndicatorRef);
            } catch (Core_Exception_NotFound $e) {
                // Indexation selon l'indicateur de classification non trouvée. Impossible de créer le résultat.
                continue;
            }

            $dWResult = new Result($dWIndicator);
            $dWResult->setValue($outputElement->getValue());

            foreach ($outputElement->getIndexes() as $outputIndex) {
                try {
                    $classificationLibrary = $outputIndex->getAxis()->getLibrary();
                    $dWAxis = $dWCube->getAxisByRef(
                        'c_' . $classificationLibrary->getId() . '_' . $outputIndex->getRefAxis()
                    );
                    $dWMember = $dWAxis->getMemberByRef($outputIndex->getRefMember());
                    $dWResult->addMember($dWMember);
                } catch (Core_Exception_NotFound $e) {
                    // Indexation selon classification non trouvée.
                }

                foreach ($outputIndex->getMember()->getAllParents() as $classifParentMember) {
                    try {
                        $dWBroaderAxis = $dWCube->getAxisByRef(
                            'c_' . $classificationLibrary->getId() . '_' . $classifParentMember->getAxis()->getRef()
                        );
                        $dWParentMember = $dWBroaderAxis->getMemberByRef($classifParentMember->getRef());
                        $dWResult->addMember($dWParentMember);
                    } catch (Core_Exception_NotFound $e) {
                        // Indexation selon classification non trouvée.
                    }
                }
            }

            /** @var Member[] $indexingMembers */
            $indexingMembers = array();
            foreach ($this->getMembers() as $member) {
                array_push($indexingMembers, $member);
                $indexingMembers = array_merge($indexingMembers, $member->getAllParents());
            }
            foreach ($indexingMembers as $indexingMember) {
                try {
                    $dWAxis = $dWCube->getAxisByRef('o_' . $indexingMember->getAxis()->getRef());
                    $dWMember = $dWAxis->getMemberByRef($indexingMember->getRef());
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

    public function deleteDWResults()
    {
        foreach ($this->dWResults as $dWResult) {
            $this->dWResults->removeElement($dWResult);
            $dWResult->delete();
        }
    }

    /**
     * @param Cube $dWCube
     */
    public function deleteDWResultsForDWCube(Cube $dWCube)
    {
        $criteria = Criteria::create()->where(Criteria::expr()->eq('dWCube', $dWCube));
        foreach ($this->dWResults->matching($criteria) as $dWResult) {
            $this->dWResults->removeElement($dWResult);
            $dWResult->delete();
        }
    }

    /**
     * @return \Orga\Domain\ACL\AbstractCellRole[]
     */
    public function getAllRoles()
    {
        return $this->roles->toArray();
    }

    /**
     * Pas de gestion des roles dans le model, tout passe par l'ACL Manager.
     * @see Orga\Domain\Service\OrgaACLManager
     * @param AbstractCellRole $role
     */
    public function addRole(AbstractCellRole $role)
    {
        $this->roles[] = $role;
    }

    /**
     * @return \Orga\Domain\ACL\CellAdminRole[]
     */
    public function getAdminRoles()
    {
        return $this->roles->filter(
            function (AbstractCellRole $role) {
                return $role instanceof CellAdminRole;
            }
        );
    }

    /**
     * @return \Orga\Domain\ACL\CellManagerRole[]
     */
    public function getManagerRoles()
    {
        return $this->roles->filter(
            function (AbstractCellRole $role) {
                return $role instanceof CellManagerRole;
            }
        );
    }

    /**
     * @return CellContributorRole[]
     */
    public function getContributorRoles()
    {
        return $this->roles->filter(
            function (AbstractCellRole $role) {
                return $role instanceof CellContributorRole;
            }
        );
    }

    /**
     * @return CellObserverRole[]
     */
    public function getObserverRoles()
    {
        return $this->roles->filter(
            function (AbstractCellRole $role) {
                return $role instanceof CellObserverRole;
            }
        );
    }

    /**
     * @return string Représentation textuelle de l'unité
     */
    public function __toString()
    {
        return $this->getMembersHashKey();
    }
}
