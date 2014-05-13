<?php
/**
 * Classe Orga_Model_Member
 * @author valentin.claras
 * @author diana.dragusin
 * @package    Orga
 * @subpackage Model
 */

use Core\Translation\TranslatedString;
use Doctrine\Common\Collections\Collection;
use Doctrine\Common\Collections\ArrayCollection;
use Mnapoli\Translated\StringConcatenation;

/**
 * Definit un membre d'un axe.
 * @package    Orga
 * @subpackage Model
 */
class Orga_Model_Member extends Core_Model_Entity
{
    use Core_Strategy_Ordered;

    // Constantes de tris et de filtres.
    const QUERY_TAG = 'tag';
    const QUERY_REF = 'ref';
    const QUERY_PARENTMEMBERS_HASHKEY = 'parentMembersHashKey';
    const QUERY_LABEL = 'label';
    const QUERY_AXIS = 'axis';
    // Constantes de séparation de la memberHashKey et de la ref du membre.
    const COMPLETEREF_JOIN = '#';


    /**
     * Identifiant unique du Member.
     *
     * @var string
     */
    protected  $id = null;

    /**
     * Référence unique (au sein des membres contextualisant parent) du Member.
     *
     * @var string
     */
    protected $ref = null;

    /**
     * Référence représentant les membres contextualisant parents du Member.
     *
     * @var string
     */
    protected $parentMembersHashKey = '';

    /**
     * Label du Member.
     *
     * @var TranslatedString
     */
    protected $label;

    /**
     * Axis auqel appartient le Member.
     *
     * @var Orga_Model_Axis
     */
    protected $axis;

    /**
     * Tag identifiant le membre dans la hiérarchie de l'organization.
     *
     * @var string
     */
    protected $tag = null;

    /**
     * Collection des Member parents du Member courant.
     *
     * @var Collection|Orga_Model_Member[]
     */
    protected $directParents;

    /**
     * Collection des Member enfants du Member courant.
     *
     * @var Collection|Orga_Model_Member[]
     */
    protected $directChildren;

    /**
     * Collection des Cell utilisant ce Member.
     *
     * @var Collection|Orga_Model_Cell[]
     */
    protected $cells;


    /**
     * Constructeur de la classe Member.
     *
     * @param Orga_Model_Axis $axis
     * @param string $ref
     * @param Orga_Model_Member[] $directParentMembers
     *
     * @throws Core_Exception_Duplicate
     * @throws Core_Exception_InvalidArgument
     */
    public function __construct(Orga_Model_Axis $axis, $ref, array $directParentMembers=[])
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
            throw new Core_Exception_Duplicate('A Member with ref "'.$ref.'" already exists in this Axis');
        } catch (Core_Exception_NotFound $e) {
            $this->ref = $ref;
        }

        $this->setPosition();
        $this->updateTagsAndHierarchy();

        $axis->addMember($this);
    }

    /**
     * Renvoi les valeurs du contexte pour l'objet.
     * .
     * @return array
     */
    public function getContext()
    {
        return array('axis' => $this->axis, 'parentMembersHashKey' => $this->parentMembersHashKey);
    }

    /**
     * Fonction appelée avant un persist de l'objet (défini dans le mapper).
     */
    public function preSave()
    {
        $this->checkRefUniqueness();
    }

    /**
     * Fonction appelée avant un update de l'objet (défini dans le mapper).
     */
    public function preUpdate()
    {
        $this->checkRefUniqueness();
    }

    /**
     * Fonction appelée avant un delete de l'objet (défini dans le mapper).
     */
    public function preDelete()
    {
        $this->deletePosition();
    }

    /**
     * Fonction appelée après un load de l'objet (défini dans le mapper).
     */
    public function postLoad()
    {
        $this->updateCachePosition();
    }

    /**
     * Construit une chaine de caractère représentant les membres parents contextualisants.
     *
     * @param Orga_Model_Member[] $contextualizingParentMembers
     *
     * @return string
     */
    public static function buildParentMembersHashKey($contextualizingParentMembers)
    {
        @usort($contextualizingParentMembers, [Orga_Model_Member::class, 'orderMembers']);
        $parentMembersRef = [];

        foreach ($contextualizingParentMembers as $parentMember) {
            $parentMembersRef[] = $parentMember->getRef();
        }

        return sha1(implode(self::COMPLETEREF_JOIN, $parentMembersRef));
    }

    /**
     * Permet d'ordonner des Member entre eux.
     *
     * @param Orga_Model_Member $a
     * @param Orga_Model_Member $b
     *
     * @return int 1, 0 ou -1
     */
    public static function orderMembers(Orga_Model_Member $a, Orga_Model_Member $b)
    {
        if ($a->getAxis() === $b->getAxis()) {
            if ($a->getAxis()->isMemberPositioning()) {
                return strcmp($a->getTag(), $b->getTag());
            } else {
                return strcmp($a->getLabel(), $b->getLabel());
            }
        }
        return Orga_Model_Axis::firstOrderAxes($a->getAxis(), $b->getAxis());
    }

    /**
     * Vérifie que la ref est unique.
     *
     * @throw Core_Exception_TooMany
     */
    protected function checkRefUniqueness()
    {
        try {
            $memberFound = $this->getAxis()->getMemberByCompleteRef($this->getCompleteRef());

            if ($memberFound !== $this) {
                throw new Core_Exception_TooMany(
                    'Ref "'.$this->getRef().'" already used with same contextualizing parent members.'
                );
            }
        } catch (Core_Exception_NotFound $e) {
            // Pas de membre trouvé.
        }
    }

    /**
     * Permet une surcharge facile pour lancer des évents après qu'un objet ait été déplacé.
     */
    protected function hasMove()
    {
        $this->updateTags();
    }

    /**
     * Mets à jour la hashKey des membres parents contextualisants.
     */
    public function updateParentMembersHashKeys()
    {
        $this->parentMembersHashKey = self::buildParentMembersHashKey($this->getContextualizingParents());

        $this->updateDirectChildrenMembersParentMembersHashKey();
    }

    /**
     * Mets à jour la hashKey des membres enfants.
     */
    public function updateDirectChildrenMembersParentMembersHashKey()
    {
        foreach ($this->getDirectChildren() as $childMember) {
            $childMember->updateParentMembersHashKeys();
        }
    }

    /**
     * Renvoie l'id du Member.
     *
     * @return string
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Définit la référence du Member.
     *
     * @param string $ref
     *
     * @throws Core_Exception_Duplicate
     */
    public function setRef($ref)
    {
        if ($this->ref !== $ref) {
            try {
                $this->getAxis()->getMemberByCompleteRef($ref . self::COMPLETEREF_JOIN . $this->parentMembersHashKey);
                throw new Core_Exception_Duplicate('A Member with ref "'.$ref.'" already exists in this Axis');
            } catch (Core_Exception_NotFound $e) {
                $this->ref = $ref;
                $this->updateTags();
            }
        }
    }

    /**
     * Renvois la référence du Member.
     *
     * @return string
     */
    public function getRef()
    {
        return $this->ref;
    }

    /**
     * Renvois la hashKey des membres parents contextualisants du Member.
     *
     * @return string
     */
    public function getParentMembersHashKey()
    {
        return $this->parentMembersHashKey;
    }

    /**
     * Renvois la référence complète (ref#parentMemberHashKey) du Member.
     *
     * @return string
     */
    public function getCompleteRef()
    {
        return $this->getRef() . self::COMPLETEREF_JOIN . $this->getParentMembersHashKey();
    }

    /**
     * Renvois le label du Member.
     *
     * @return TranslatedString
     */
    public function getLabel()
    {
        return $this->label;
    }

    /**
     * Renvoie le label étendu (avec le label étendu des parents).
     *
     * @return TranslatedString
     */
    public function getExtendedLabel()
    {
        $broaderLabelParts = [];
        foreach ($this->getContextualizingParents() as $contextualizingParentMember) {
            $broaderLabelParts[] = $contextualizingParentMember->getExtendedLabel();
        }

        if ((count($broaderLabelParts) > 0)) {
            $postfix = StringConcatenation::fromArray([
                ' (',
                StringConcatenation::implode(', ', $broaderLabelParts),
                ')',
            ]);
        } else {
            $postfix = '';
        }

        return $this->getLabel()->concat($postfix);
    }

    /**
     *
     */
    public function removeFromAxis()
    {
        if ($this->axis !== null) {
            if ($this->axis->hasMember($this)) {
                $this->axis->removeMember($this);
            } else {
                $this->axis = null;
            }
        }
    }

    /**
     * Renvoie l'Axis du Member.
     *
     * @throws Core_Exception_UndefinedAttribute
     * @return Orga_Model_Axis
     */
    public function getAxis()
    {
        if ($this->axis === null) {
            throw new Core_Exception_UndefinedAttribute('The Axis has not been defined yet.');
        }
        return $this->axis;
    }

    /**
     * @return string
     */
    public function getMemberTag()
    {
        return $this->getAxis()->getAxisTag() . ':' . ($this->getAxis()->isMemberPositioning() ? $this->getPosition() . '-' : '') . $this->getRef();
    }

    /**
     * Mets à jour le tag du membre.
     *  Ne devrait pas être utilisé.
     */
    public function updateTag()
    {
        $this->tag = Orga_Model_Organization::PATH_SEPARATOR;
        if ($this->hasDirectParents()) {
            $pathTags = [];
            foreach ($this->getDirectParents() as $directParentMember) {
                foreach (explode(Orga_Model_Organization::PATH_JOIN, $directParentMember->getTag()) as $pathTag) {
                    $pathTags[] = $pathTag;
                }
            }
            $pathLink = $this->getMemberTag() . Orga_Model_Organization::PATH_SEPARATOR . Orga_Model_Organization::PATH_JOIN;
            $this->tag = implode($pathLink, $pathTags);
        }
        $this->tag .=  $this->getMemberTag() . Orga_Model_Organization::PATH_SEPARATOR;
    }

    /**
     * Mets à jour le tag du membre, de ses cellules, et ceux des enfants.
     */
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
     * Mets à jour le tag du membre, de ses cellules, et ceux des enfants.
     */
    public function updateTagsAndHierarchy()
    {
        $this->updateTag();

        foreach ($this->getCells() as $cell) {
            $cell->updateTags();
        }

        foreach ($this->getDirectChildren() as $directChildMember) {
            $directChildMember->updateTagsAndHierarchy();
        }

        foreach ($this->getCells() as $cell) {
            $cell->updateHierarchy();
        }
    }

    /**
     * Renvoie le tag du membre.
     *
     * @return string
     */
    public function getTag()
    {
        return $this->tag;
    }

    public function updateParentsMembers()
    {
        if ($this->getDirectParents()->count() !== count($this->getAxis()->getDirectBroaders())) {
            $this->disableCells();
        } else {
            $this->enableCells();
        }
    }

    /**
     * Désactive chaque cellules.
     */
    protected function disableCells()
    {
        foreach ($this->getCells() as $cell) {
            $cell->disable();
        }
    }

    /**
     * Active chaque cellules.
     */
    protected function enableCells()
    {
        foreach ($this->getCells() as $cell) {
            $cell->enable();
        }
    }

    /**
     * Ajoute un Member donné aux parents directs du Member courant.
     *
     * @param Orga_Model_Member $newDirectParentMemberForAxis
     *
     * @throws Core_Exception_InvalidArgument
     */
    public function setDirectParentForAxis(Orga_Model_Member $newDirectParentMemberForAxis)
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
            foreach ($this->getAxis()->getOrganization()->getGranularities() as $granularity) {
                $granularity->getCells()->toArray();
            }
            $this->updateTagsAndHierarchy();
        }
    }

    /**
     * Retire un Member donné des parents directs du Member courant.
     *
     * @param Orga_Model_Member $directParentMemberForAxis
     */
    public function removeDirectParentForAxis(Orga_Model_Member $directParentMemberForAxis)
    {
        if ($this->hasDirectParent($directParentMemberForAxis)) {
            $this->deletePosition();
            $this->directParents->removeElement($directParentMemberForAxis);
            if ($directParentMemberForAxis->hasDirectChild($this)) {
                $directParentMemberForAxis->directChildren->removeElement($this);
            }
            $this->updateParentMembersHashKeys();
            $this->addPosition();
            $this->updateTagsAndHierarchy();
            $this->disableCells();
        }
    }

    /**
     * Vérifie si le Member courant possède le Member donné en tant que parent direct.
     *
     * @param Orga_Model_Member $parentMember
     *
     * @return boolean
     */
    public function hasDirectParent(Orga_Model_Member $parentMember)
    {
        return $this->directParents->contains($parentMember);
    }

    /**
     * Vérifie que le Member courant possède au moins un Member parent direct.
     *
     * @return bool
     */
    public function hasDirectParents()
    {
        return !$this->directParents->isEmpty();
    }

    /**
     * Renvoie un tableau des Member parents directs.
     *
     * @return Collection|Orga_Model_Member[]
     */
    public function getDirectParents()
    {
        return $this->directParents;
    }

    /**
     * Renvoie le membre parent direct correspondant à l'axe donné.
     *
     * @param Orga_Model_Axis $axis
     *
     * @throws Core_Exception_InvalidArgument
     * @throws Core_Exception_NotFound
     * @throws Core_Exception_TooMany
     *
     * @return Orga_Model_Member
     */
    public function getDirectParentForAxis(Orga_Model_Axis $axis)
    {
        if (!$this->getAxis()->hasDirectBroader($axis)) {
            throw new Core_Exception_InvalidArgument('The given Axis is not a direct broader of the Member\'s Axis');
        }

        //@todo Doctrine : Problème de matching ManyToMany sr des PersistentCollection.
        if ($this->directParents instanceof \Doctrine\ORM\PersistentCollection) {
            foreach ($this->getDirectParents() as $directParent) {
                if ($directParent->getAxis() === $axis) {
                    return $directParent;
                }
            }
            throw new Core_Exception_NotFound('No direct parent Member matching Axis "'.$axis->getRef().'".');
        }

        $criteria = Doctrine\Common\Collections\Criteria::create();
        $criteria->where($criteria->expr()->eq('axis', $axis));
        $member = $this->getDirectParents()->matching($criteria)->toArray();

        if (count($member) === 0) {
            throw new Core_Exception_NotFound('No direct parent Member matching Axis "'.$axis->getRef().'".');
        } else if (count($member) > 1) {
            throw new Core_Exception_TooMany('Too many direct parent Member matching Axis "'.$axis->getRef().'".');
        }

        return array_pop($member);
    }

    /**
     * Renvoie un tableau contenant tous les Member parents de Member courant.
     *
     * @return Orga_Model_Member[]
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
        @usort($parents, [Orga_Model_Member::class, 'orderMembers']);
        return $parents;
    }

    /**
     * Renvoie le membre parent correspondant à l'axe donné.
     *
     * @param Orga_Model_Axis $axis
     *
     * @throws Core_Exception_InvalidArgument
     * @throws Core_Exception_NotFound
     * @throws Core_Exception_TooMany
     *
     * @return Orga_Model_Member
     */
    public function getParentForAxis(Orga_Model_Axis $axis)
    {
        if (!$this->getAxis()->isNarrowerThan($axis)) {
            throw new Core_Exception_InvalidArgument('The given Axis is not a broader of the Member\'s Axis');
        }

        $s = Orga_Model_Organization::PATH_SEPARATOR;
        $j = Orga_Model_Organization::PATH_JOIN;
        $parentMemberTag = $axis->getAxisTag() . ':' . ($axis->isMemberPositioning() ? '[0-9]+-' : '');
        preg_match('#([^\\'.$j.']*\\'.$s.$parentMemberTag.'[a-z0-9_]+\\'.$s.')#', $this->getTag(), $parentMemberPathTags);
        array_shift($parentMemberPathTags);

        if (count($parentMemberPathTags) > 0) {
            $criteria = Doctrine\Common\Collections\Criteria::create();
            foreach ($parentMemberPathTags as $pathTag) {
                $criteria->andWhere($criteria->expr()->contains('tag', $pathTag));
            }
            $member = $axis->getOrderedMembers()->matching($criteria)->toArray();
        } else {
            $member = [];
        }

        if (count($member) === 0) {
            throw new Core_Exception_NotFound('No parent Member matching Axis "'.$axis->getRef().'".');
        } else if (count($member) > 1) {
            throw new Core_Exception_TooMany('Too many direct parent Member matching Axis "'.$axis->getRef().'".');
        }

        return array_pop($member);
    }

    /**
     * Renvoie un tableau des Member parents du Member courant appartenant à un Axis contextualisant.
     *
     * @return Orga_Model_Member[]
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
     * Vérifie si le Member courant possède le Member donné en tant qu'enfant direct.
     *
     * @param Orga_Model_Member $childMember
     *
     * @return boolean
     */
    public function hasDirectChild(Orga_Model_Member $childMember)
    {
        return $this->directChildren->contains($childMember);
    }

    /**
     * Vérifie que le Member courant possède au moins un Member enfant direct.
     *
     * @return bool
     */
    public function hasDirectChildren()
    {
        return !$this->directChildren->isEmpty();
    }

    /**
     * Renvoie un tableau des Member enfants directs.
     *
     * @return Collection|Orga_Model_Member[]
     */
    public function getDirectChildren()
    {
        return $this->directChildren;
    }

    /**
     * Renvoie les Member enfants pour l'Axis donné.
     *
     * @param Orga_Model_Axis $axis
     *
     * @throws Core_Exception_InvalidArgument
     *
     * @return Orga_Model_Member[]
     */
    public function getChildrenForAxis(Orga_Model_Axis $axis)
    {
        if (!$this->getAxis()->isBroaderThan($axis)) {
            throw new Core_Exception_InvalidArgument('The given Axis is not a narrower of the Member\'s Axis');
        }

        $criteria = Doctrine\Common\Collections\Criteria::create();
        foreach (explode(Orga_Model_Organization::PATH_JOIN, $this->getTag()) as $pathTag) {
            $criteria->andWhere($criteria->expr()->contains('tag', $pathTag));
        }
        if ($axis->isMemberPositioning()) {
            $criteria->orderBy(['parentMembersHashKey' => 'ASC', 'position' => 'ASC']);
        } else {
            $criteria->orderBy(['label' => 'ASC']);
        }
        return $axis->getMembers()->matching($criteria)->toArray();
    }

    /**
     * Ajoute une Cell à celles utilisant le Member courant.
     *
     * @param Orga_Model_Cell $cell
     *
     * @throws Core_Exception_InvalidArgument
     */
    public function addCell(Orga_Model_Cell $cell)
    {
        if (!$cell->hasMember($this)) {
            throw new Core_Exception_InvalidArgument();
        }

        if (!$this->hasCell($cell)) {
            $this->cells->add($cell);
        }
    }

    /**
     * Vérifie si la Cell donnée appartient aux cells utilisant ce Member.
     *
     * @param Orga_Model_Cell $cell
     *
     * @return boolean
     */
    public function hasCell(Orga_Model_Cell $cell)
    {
        return $this->cells->contains($cell);
    }

    /**
     * Vérifie si le Member possède au moins une Cell..
     *
     * @return bool
     */
    public function hasCells()
    {
        return !$this->cells->isEmpty();
    }

    /**
     * Renvoie un tableau des Cells.
     *
     * @return Collection|Orga_Model_Cell[]
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
