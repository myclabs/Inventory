<?php

namespace Orga\Domain;

use Core\Translation\TranslatedString;
use Core_Exception;
use Core_Exception_Duplicate;
use Core_Exception_InvalidArgument;
use Core_Exception_NotFound;
use Core_Exception_TooMany;
use Core_Exception_UndefinedAttribute;
use Core_Model_Entity;
use Core_Strategy_Ordered;
use Core_Tools;
use Doctrine\Common\Collections\Collection;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Criteria;
use Doctrine\Common\Collections\Selectable;
use Doctrine\ORM\PersistentCollection;

/**
 * Member
 *
 * @author valentin.claras
 */
class Member extends Core_Model_Entity
{
    use Core_Strategy_Ordered;

    // Constantes de tris et de filtres.
    const QUERY_TAG = 'tag';
    const QUERY_REF = 'ref';
    const QUERY_PARENTMEMBERS_HASHKEY = 'parentMembersHashKey';
    const QUERY_LABEL = 'label';
    const QUERY_POSITION = 'position';
    const QUERY_AXIS = 'axis';
    // Constantes de séparation de la memberHashKey et de la ref du membre.
    const COMPLETEREF_JOIN = '#';


    /**
     * @var string
     */
    protected $id = null;

    /**
     * @var string
     */
    protected $ref = null;

    /**
     * @var string
     */
    protected $parentMembersHashKey = '';

    /**
     * @var TranslatedString
     */
    protected $label;

    /**
     * @var Axis
     */
    protected $axis;

    /**
     * @var string
     */
    protected $tag = null;

    /**
     * @var Collection|Member[]
     */
    protected $directParents;

    /**
     * @var Collection|Member[]
     */
    protected $directChildren;

    /**
     * @var Collection|Cell[]
     */
    protected $cells;


    /**
     * @param Axis $axis
     * @param string $ref
     * @param Member[] $directParentMembers
     * @throws Core_Exception_Duplicate
     * @throws Core_Exception_InvalidArgument
     */
    public function __construct(Axis $axis, $ref, array $directParentMembers = [])
    {
        $this->label = new TranslatedString();
        $this->directParents = new ArrayCollection();
        $this->directChildren = new ArrayCollection();
        $this->cells = new ArrayCollection();

        $this->axis = $axis;

        foreach ($directParentMembers as $directParentMember) {
            if (!($this->hasDirectParent($directParentMember))) {
                if ($directParentMember->getAxis()->getDirectNarrower() !== $this->getAxis()) {
                    throw new Core_Exception_InvalidArgument('A direct parent Member needs to comes from a broader axis.');
                }
                try {
                    $this->getDirectParentForAxis($directParentMember->getAxis());
                    throw new Core_Exception_InvalidArgument('A direct parent from the same axis as already be given as parent.');
                } catch (Core_Exception_NotFound $e) {
                    $this->directParents->add($directParentMember);
                }
            }
        }
        if ($this->directParents->count() !== count($this->axis->getDirectBroaders())) {
            throw new Core_Exception_InvalidArgument('A member needs one parent for each broader axis of his own axis.');
        }
        foreach ($directParentMembers as $directParentMember) {
            $directParentMember->directChildren->add($this);
        }
        $this->updateParentMembersHashKeys();

        Core_Tools::checkRef($ref);
        try {
            $this->getAxis()->getMemberByCompleteRef($ref . self::COMPLETEREF_JOIN . $this->parentMembersHashKey);
            throw new Core_Exception_Duplicate('A Member with ref "' . $ref . '" already exists in this Axis.');
        } catch (Core_Exception_NotFound $e) {
            $this->ref = $ref;
        }

        $this->setPosition();
        $this->updateTags();

        $axis->addMember($this);
    }

    /**
     * @return array
     */
    public function getContext()
    {
        return array('axis' => $this->axis, 'parentMembersHashKey' => $this->parentMembersHashKey);
    }

    /**
     * Fonction appelée avant un delete de l'objet (défini dans le mapper).
     */
    public function preDelete()
    {
        if (($this->getAxis() !== null) && ($this->getAxis()->hasMember($this))) {
            $this->removeFromAxis();
        }
    }

    /**
     * Fonction appelée après un load de l'objet (défini dans le mapper).
     */
    public function postLoad()
    {
        $this->updateCachePosition();
    }

    /**
     * @param Member[] $contextualizingParentMembers
     * @return string
     */
    public static function buildParentMembersHashKey(array $contextualizingParentMembers)
    {
        @usort($contextualizingParentMembers, [Member::class, 'orderMembers']);
        $parentMembersRef = [];

        foreach ($contextualizingParentMembers as $parentMember) {
            $parentMembersRef[] = $parentMember->getRef();
        }

        return sha1(implode(self::COMPLETEREF_JOIN, $parentMembersRef));
    }

    /**
     * @param Member $a
     * @param Member $b
     * @return int 1, 0 ou -1
     */
    public static function orderMembers(Member $a, Member $b)
    {
        if ($a->getAxis() === $b->getAxis()) {
            return strcmp($a->getRef(), $b->getRef());
        }
        return Axis::firstOrderAxes($a->getAxis(), $b->getAxis());
    }

    /**
     * @param string $ref
     * @throws Core_Exception_TooMany
     */
    protected function checkRefUniqueness($ref = null)
    {
        if ($ref === null) {
            $ref = $this->getRef();
        }
        $completeRef = $ref . self::COMPLETEREF_JOIN . $this->getParentMembersHashKey();

        try {
            $memberFound = $this->getAxis()->getMemberByCompleteRef($completeRef);

            if ($memberFound !== $this) {
                throw new Core_Exception_TooMany(
                    'Ref "' . $this->getRef() . '" already used with same contextualizing parent members.'
                );
            }
        } catch (Core_Exception_NotFound $e) {
            // Pas de membre trouvé.
        }
    }

    protected function hasMove()
    {
        $this->updateTags();
    }

    public function updateParentMembersHashKeys()
    {

        $this->parentMembersHashKey = self::buildParentMembersHashKey($this->getContextualizingParents());

        $this->updateDirectChildrenMembersParentMembersHashKey();
    }

    public function updateDirectChildrenMembersParentMembersHashKey()
    {
        foreach ($this->getDirectChildren() as $childMember) {
            $childMember->updateParentMembersHashKeys();
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
     * @param string $ref
     * @throws Core_Exception_Duplicate
     */
    public function setRef($ref)
    {
        if ($this->ref !== $ref) {
            $this->checkRefUniqueness($ref);
            $this->ref = $ref;
            $this->updateTags();
        }
    }

    /**
     * @return string
     */
    public function getRef()
    {
        return $this->ref;
    }

    /**
     * @return string
     */
    public function getParentMembersHashKey()
    {
        return $this->parentMembersHashKey;
    }

    /**
     * @return string
     */
    public function getCompleteRef()
    {
        return $this->getRef() . self::COMPLETEREF_JOIN . $this->getParentMembersHashKey();
    }

    /**
     * @return TranslatedString
     */
    public function getLabel()
    {
        return $this->label;
    }

    /**
     * @return TranslatedString
     */
    public function getExtendedLabel()
    {
        $broaderLabelParts = [];
        foreach ($this->getContextualizingParents() as $contextualizingParentMember) {
            $broaderLabelParts[] = $contextualizingParentMember->getExtendedLabel();
        }

        if ((count($broaderLabelParts) > 0)) {
            $postfix = TranslatedString::join([' (', TranslatedString::implode(', ', $broaderLabelParts), ')']);
        } else {
            $postfix = '';
        }

        return $this->getLabel()->concat($postfix);
    }

    /**
     * @throws Core_Exception_UndefinedAttribute
     * @return Axis
     */
    public function getAxis()
    {
        return $this->axis;
    }

    public function removeFromAxis()
    {
        if ($this->axis !== null) {
            $this->axis->removeMember($this);

            // Suppression de la position.
            $this->deletePosition();

            // Détachement du membre de l'axe.
            $this->axis = null;
        }
    }

    /**
     * @return string
     */
    public function getMemberTag()
    {
        return $this->getAxis()->getAxisTag() . ':'
        . ($this->getAxis()->isMemberPositioning() ? $this->getPosition() . '-' : '') . $this->getRef();
    }

    protected function updateTag()
    {
        $this->tag = Workspace::PATH_SEPARATOR;
        if ($this->hasDirectParents()) {
            $pathTags = [];
            foreach ($this->getDirectParents() as $directParentMember) {
                foreach (explode(Workspace::PATH_JOIN, $directParentMember->getTag()) as $pathTag) {
                    $pathTags[] = $pathTag;
                }
            }
            $pathLink = $this->getMemberTag() . Workspace::PATH_SEPARATOR . Workspace::PATH_JOIN;
            $this->tag = implode($pathLink, $pathTags);
        }
        $this->tag .= $this->getMemberTag() . Workspace::PATH_SEPARATOR;
    }

    public function updateTags()
    {
        $this->updateTag();

        foreach ($this->getCells() as $cell) {
            $cell->updateTag();
            $cell->updateMembersHashKey();
        }

        foreach ($this->getDirectChildren() as $directChildMember) {
            $directChildMember->updateTags();
        }
    }

    /**
     * @return string
     */
    public function getTag()
    {
        return $this->tag;
    }

    public function updateCellsHierarchy()
    {
        /** @var Granularity $axisGranularity */
        foreach ($this->getAxis()->getOrderedGranularities() as $axisGranularity) {
            /** @var Cell $granularityMemberCell */
            foreach ($axisGranularity->getCellsByMembers([$this]) as $granularityMemberCell) {
                $granularityMemberCell->updateHierarchy();
            }
        }

        foreach ($this->getDirectChildren() as $directChildMember) {
            $directChildMember->updateCellsHierarchy();
        }
    }

    public function disableCells()
    {
        foreach ($this->getCells() as $cell) {
            $cell->disable();
        }
        foreach ($this->getDirectChildren() as $directChild) {
            $directChild->disableCells();
        }

    }

    public function enableCells()
    {
        foreach ($this->getCells() as $cell) {
            $cell->enable();
        }
        foreach ($this->getDirectChildren() as $directChild) {
            $directChild->enableCells();
        }
    }

    /**
     * @param Member $newDirectParentMemberForAxis
     * @throws Core_Exception_InvalidArgument
     */
    public function setDirectParentForAxis(Member $newDirectParentMemberForAxis)
    {
        if (!($this->hasDirectParent($newDirectParentMemberForAxis))) {
            if ($newDirectParentMemberForAxis->getAxis()->getDirectNarrower() !== $this->getAxis()) {
                throw new Core_Exception_InvalidArgument('A direct parent Member needs to comes from a broader axis');
            }
            $this->deletePosition();
            try {
                $oldDirectParentMemberForAxis = $this->getDirectParentForAxis($newDirectParentMemberForAxis->getAxis());
                $this->directParents->removeElement($oldDirectParentMemberForAxis);
                if ($oldDirectParentMemberForAxis->hasDirectChild($this)) {
                    $oldDirectParentMemberForAxis->directChildren->removeElement($this);
                }
            } catch (Core_Exception_NotFound $e) {
                // Pas d'ancien membre parent pour cet axe.
            }
            $this->directParents->add($newDirectParentMemberForAxis);
            if (!($newDirectParentMemberForAxis->hasDirectChild($this))) {
                $newDirectParentMemberForAxis->directChildren->add($this);
            }
            $this->updateParentMembersHashKeys();
            $this->addPosition();
            // Charge les collections de cellules pour tout faire en mémoire et éviter les erreurs.
            foreach ($this->getAxis()->getWorkspace()->getGranularities() as $granularity) {
                $granularity->getCells()->toArray();
            }
            $this->updateTags();
            $this->updateCellsHierarchy();
        }
    }

    /**
     * @param Member $directParentMemberForAxis
     */
    public function removeDirectParentForAxis(Member $directParentMemberForAxis)
    {
        if ($this->hasDirectParent($directParentMemberForAxis)) {
            $this->deletePosition();
            $this->directParents->removeElement($directParentMemberForAxis);
            if ($directParentMemberForAxis->hasDirectChild($this)) {
                $directParentMemberForAxis->directChildren->removeElement($this);
            }
            $this->updateParentMembersHashKeys();
            $this->addPosition();
            $this->updateTags();
            $this->updateCellsHierarchy();
        }
    }

    /**
     * @param Member $parentMember
     * @return boolean
     */
    public function hasDirectParent(Member $parentMember)
    {
        return $this->directParents->contains($parentMember);
    }

    /**
     * @return bool
     */
    public function hasDirectParents()
    {
        return !$this->directParents->isEmpty();
    }

    /**
     * @return Collection|Selectable|Member[]
     */
    public function getDirectParents()
    {
        return $this->directParents;
    }

    /**
     * @param Axis $axis
     * @throws Core_Exception_InvalidArgument
     * @throws Core_Exception_NotFound
     * @throws Core_Exception_TooMany
     * @return Member
     */
    public function getDirectParentForAxis(Axis $axis)
    {
        if (!$this->getAxis()->hasDirectBroader($axis)) {
            throw new Core_Exception_InvalidArgument('The given Axis is not a direct broader of the Member\'s Axis');
        }

        // Un matching sur une PersistentCollection (manyToMany) non initialisée produit une erreur.
        //@todo Supprimer l'initialisation lorsque le problème sera corrigé.
        $this->directParents->toArray();

        $criteria = Criteria::create();
        $criteria->where($criteria->expr()->eq('axis', $axis));
        $member = $this->directParents->matching($criteria)->toArray();

        if (count($member) === 0) {
            throw new Core_Exception_NotFound('No direct parent Member matching Axis "' . $axis->getRef() . '".');
        } elseif (count($member) > 1) {
            throw new Core_Exception_TooMany('Too many direct parent Member matching Axis "' . $axis->getRef() . '".');
        }

        return array_pop($member);
    }

    /**
     * @return Member[]
     */
    public function getAllParents()
    {
        $parents = array();
        foreach ($this->getDirectParents() as $directParent) {
            $parents[] = $directParent;
            foreach ($directParent->getAllParents() as $recursiveParents) {
                $parents[] = $recursiveParents;
            }
        }
        @usort($parents, [Member::class, 'orderMembers']);
        return $parents;
    }

    /**
     * @param Axis $axis
     * @throws Core_Exception_InvalidArgument
     * @throws Core_Exception_NotFound
     * @throws Core_Exception_TooMany
     * @return Member
     */
    public function getParentForAxis(Axis $axis)
    {
        if (!$this->getAxis()->isNarrowerThan($axis)) {
            throw new Core_Exception_InvalidArgument('The given Axis is not a broader of the Member\'s Axis');
        }

        $s = Workspace::PATH_SEPARATOR;
        $j = Workspace::PATH_JOIN;
        // Recherche de la partie du tag du membre correspondant à l'axe parent.
        $parentMemberTag = $axis->getAxisTag() . ':' . ($axis->isMemberPositioning() ? '[0-9]+-' : '');
        preg_match(
            '#([^\\' . $j . ']*\\' . $s . $parentMemberTag . '[a-z0-9_]+\\' . $s . ')#',
            $this->getTag(),
            $parentMemberPathTags
        );
        array_shift($parentMemberPathTags);

        if (count($parentMemberPathTags) > 0) {
            $criteria = Criteria::create();
            foreach ($parentMemberPathTags as $pathTag) {
                $criteria->andWhere($criteria->expr()->contains('tag', $pathTag));
            }
            $member = $axis->getOrderedMembers()->matching($criteria)->toArray();
        } else {
            $member = [];
        }

        if (count($member) === 0) {
            throw new Core_Exception_NotFound('No parent Member matching Axis "' . $axis->getRef() . '".');
        } elseif (count($member) > 1) {
            throw new Core_Exception_TooMany('Too many direct parent Member matching Axis "' . $axis->getRef() . '".');
        }

        return array_pop($member);
    }

    /**
     * @return Member[]
     */
    public function getContextualizingParents()
    {
        $contextualizingParentMembers = [];

        foreach ($this->getDirectParents() as $parentMember) {
            if ($parentMember->getAxis()->isContextualizing()) {
                $contextualizingParentMembers[] = $parentMember;
            }
            $contextualizingParentMembers = array_merge(
                $contextualizingParentMembers,
                $parentMember->getContextualizingParents()
            );
        }

        return $contextualizingParentMembers;
    }

    /**
     * @param Member $childMember
     * @return boolean
     */
    public function hasDirectChild(Member $childMember)
    {
        return $this->directChildren->contains($childMember);
    }

    /**
     * @return bool
     */
    public function hasDirectChildren()
    {
        return !$this->directChildren->isEmpty();
    }

    /**
     * @return Collection|Member[]
     */
    public function getDirectChildren()
    {
        return $this->directChildren;
    }

    /**
     * @param Axis $axis
     * @throws Core_Exception_InvalidArgument
     * @return Member[]
     */
    public function getChildrenForAxis(Axis $axis)
    {
        if (!$this->getAxis()->isBroaderThan($axis)) {
            throw new Core_Exception_InvalidArgument('The given Axis is not a narrower of the Member\'s Axis');
        }

        $criteria = Criteria::create();
        // Recherche des membres possédant les même chemins (les parties du tag) que le membre.
        foreach (explode(Workspace::PATH_JOIN, $this->getTag()) as $pathTag) {
            $criteria->andWhere($criteria->expr()->contains('tag', $pathTag));
        }
        if ($axis->isMemberPositioning()) {
            $criteria->orderBy(['parentMembersHashKey' => 'ASC', 'position' => 'ASC']);
        } else {
            $criteria->orderBy(['ref' => 'ASC']);
        }
        return $axis->getMembers()->matching($criteria)->toArray();
    }

    /**
     * @return Member
     * @throws Core_Exception_InvalidArgument
     */
    public function getPreviousMember()
    {
        if (!$this->getAxis()->isMemberPositioning()) {
            throw new Core_Exception_InvalidArgument(
                'This member needs to come from an axis allowing members positioning.'
            );
        }

        if ($this->getPosition() === 1) {
            return null;
        }

        return Member::loadByPositionAndContext(
            ($this->getPosition() - 1),
            $this->getContext()
        );
    }

    /**
     * @return Member
     * @throws Core_Exception_InvalidArgument
     */
    public function getNextMember()
    {
        if (!$this->getAxis()->isMemberPositioning()) {
            throw new Core_Exception_InvalidArgument(
                'This member needs to come from an axis allowing members positioning.'
            );
        }

        if ($this->getPosition() === $this->getLastEligiblePosition()) {
            return null;
        }

        return Member::loadByPositionAndContext(
            ($this->getPosition() + 1),
            $this->getContext()
        );
    }

    /**
     * @param Cell $cell
     * @throws Core_Exception_InvalidArgument
     */
    public function addCell(Cell $cell)
    {
        if (!$cell->hasMember($this)) {
            throw new Core_Exception_InvalidArgument();
        }

        if (!$this->hasCell($cell)) {
            $this->cells->add($cell);
        }
    }

    /**
     * @param Cell $cell
     * @return boolean
     */
    public function hasCell(Cell $cell)
    {
        return $this->cells->contains($cell);
    }

    /**
     * @param Cell $cell
     * @throws Core_Exception_InvalidArgument
     */
    public function removeCell(Cell $cell)
    {
        if (($cell->getGranularity() !== null) && ($cell->getWorkspace() !== null)) {
            throw new Core_Exception_InvalidArgument();
        }

        if ($this->hasCell($cell)) {
            $this->cells->removeElement($cell);
        }
    }

    /**
     * @return bool
     */
    public function hasCells()
    {
        return !$this->cells->isEmpty();
    }

    /**
     * @return Collection|Cell[]
     */
    public function getCells()
    {
        return $this->cells;
    }

    /**
     * @return string Représentation textuelle du member
     */
    public function __toString()
    {
        return $this->getCompleteRef();
    }
}
