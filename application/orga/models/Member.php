<?php
/**
 * Classe Orga_Model_Member
 * @author valentin.claras
 * @author diana.dragusin
 * @package    Orga
 * @subpackage Model
 */
use Doctrine\Common\Collections\Collection;
use Doctrine\Common\Collections\ArrayCollection;

/**
 * Definit un membre d'un axe.
 * @package    Orga
 * @subpackage Model
 */
class Orga_Model_Member extends Core_Model_Entity
{
    use Core_Strategy_Ordered;
    use Core_Model_Entity_Translatable;

    // Constantes de tris et de filtres.
    const QUERY_TAG = 'tag';
    const QUERY_REF = 'ref';
    const QUERY_PARENTMEMBERS_HASHKEY = 'parentMembersHashKey';
    const QUERY_LABEL = 'label';
    const QUERY_AXIS = 'axis';


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
     * @var string
     */
    protected $label = null;

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
     * @param Orga_Model_Member[] $directParentMembers
     *
     * @throws Core_Exception_InvalidArgument
     */
    public function __construct(Orga_Model_Axis $axis, array $directParentMembers)
    {
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
            $directParentMember->addDirectChild($this);
        }
        $this->setPosition();
        $this->updateHashKeys();

        $axis->addMember($this);
    }

    /**
     * Renvoi les valeurs du contexte pour l'objet.
     * .
     * @return array
     */
    protected function getContext()
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
        // Suppression des erreurs avec '@' dans le cas ou des proxies sont utilisées.
        @uasort($contextualizingParentMembers, array('Orga_Model_Member', 'orderMembers'));
        $parentMembersRef = [];

        foreach ($contextualizingParentMembers as $parentMember) {
            $parentMembersRef[] = $parentMember->getRef();
        }

        return implode('|', $parentMembersRef);
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
        if ($a->getAxis() === $b->getAxis())  {
            return strcmp($a->tag, $b->tag);
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
                    'Ref "'.$this->getCompleteRef().'" already used with same contextualizing parent members.'
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
        $this->updateHashKeys();
    }

    /**
     * Met à jour les hashKey des membres et des cellules.
     */
    public function updateHashKeys()
    {
        $this->updateTag();
        $this->updateParentMembersHashKey();
        $this->updateCellsHierarchy();
    }

    /**
     * Mets à jour la hashKey des membres parents contextualisants.
     */
    public function updateParentMembersHashKey()
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
            $childMember->updateParentMembersHashKey();
        }
    }

    /**
     * Met à jour la pertinence des cellules du membre et de leurs cellules enfants.
     */
    protected function updateCellsHierarchy()
    {
        foreach ($this->cells as $cell) {
            $cell->updateHierarchy();
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
     */
    public function setRef($ref)
    {
        $this->ref = $ref;

        $this->updateHashKeys();
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
        return $this->ref . '#' . $this->parentMembersHashKey;
    }

    /**
     * Définit le label du Member.
     *
     * @param string $label
     */
    public function setLabel($label)
    {
        $this->label = $label;
    }

    /**
     * Renvois le label du Member.
     *
     * @return string
     */
    public function getLabel()
    {
        return $this->label;
    }

    /**
     * Renvoie le label étendu (avec le label étendu des parents).
     *
     * @return string
     */
    public function getExtendedLabel()
    {
        $broaderLabelParts = [];

        foreach ($this->getContextualizingParents() as $contextualizingParentMember) {
            $broaderLabelParts[] = $contextualizingParentMember->getExtendedLabel();
        }

        return $this->label . ((count($broaderLabelParts) > 0) ? ' (' . implode(', ', $broaderLabelParts) . ')' : '');
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
     * Mets à jour le tag du membre.
     */
    public function updateTag()
    {
        if (!$this->hasDirectParents()) {
            $this->tag = '/';
        } else {
            $pathTags = [];
            foreach ($this->getDirectParents() as $directParentMember) {
                foreach (explode('|', $directParentMember->getTag()) as $pathTag) {
                    $pathTags[] = $pathTag;
                }
            }
            $this->tag = implode($this->getMemberTag() . '/|', $pathTags);
        }
        $this->tag .= $this->getMemberTag() . '/';

        foreach ($this->getDirectChildren() as $directChildMember) {
            $directChildMember->updateTag();
        }
    }

    /**
     * @return string
     */
    public function getMemberTag()
    {
        return $this->getAxis()->getTag() . ':' . ($this->getAxis()->isMemberPositionning() ? $this->getPosition() . '-' : '') . $this->getRef();
    }

    /**
     * Renvoie le tag de l'axe.
     *
     * @return string
     */
    public function getTag()
    {
        return $this->tag;
    }

    /**
     * Ajoute un Member donné aux parents directs du Member courant.
     *
     * @param Orga_Model_Member $directParentMember
     *
     * @throws Core_Exception_InvalidArgument
     */
    public function addDirectParent(Orga_Model_Member $directParentMember)
    {
        if (!($this->hasDirectParent($directParentMember))) {
            if ($directParentMember->getAxis()->getDirectNarrower() !== $this->getAxis()) {
                throw new Core_Exception_InvalidArgument('A direct parent Member needs to comes from a broader axis');
            }
            try {
                $oldDirectParentMember = $this->getDirectParentForAxis($directParentMember->getAxis());
                $this->deletePosition();
                $this->directParents->removeElement($oldDirectParentMember);
                $oldDirectParentMember->removeDirectChild($this);
            } catch (Core_Exception_NotFound $e) {
                $this->deletePosition();
                // Pas d'ancien membre parent pour cet axe.
            }
            $this->directParents->add($directParentMember);
            $directParentMember->addDirectChild($this);
            $this->addPosition();
            $this->updateHashKeys();
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
     * Retire un Member donné des parents directs du Member courant.
     *
     * @param Orga_Model_Member $directParentMember
     */
    public function removeDirectParent(Orga_Model_Member $directParentMember)
    {
        if ($this->hasDirectParent($directParentMember)) {
            $this->deletePosition();
            $this->directParents->removeElement($directParentMember);
            $directParentMember->removeDirectChild($this);
            $this->addPosition();
            $this->updateHashKeys();
        }
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
        if ($this->axis->hasDirectBroader($axis)) {
            throw new Core_Exception_InvalidArgument('The given Axis is not a broader of the Member\'s Axis');
        }

        $criteria = Doctrine\Common\Collections\Criteria::create();
        $criteria->where($criteria->expr()->eq('axis', $axis));
        $member = $this->directParents->matching($criteria)->toArray();

        if (count($member) === 0) {
            throw new Core_Exception_NotFound("No direct parent Member matching Axis " . $axis->getRef());
        } else if (count($member) > 1) {
            throw new Core_Exception_TooMany("Too many direct parent Member matching Axis " . $axis->getRef());
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
        foreach ($this->directParents as $directParent) {
            $parents[] = $directParent;
            foreach ($directParent->getAllParents() as $recursiveParents) {
                $parents[] = $recursiveParents;
            }
        }
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
        if (!$this->axis->isNarrowerThan($axis)) {
            throw new Core_Exception_InvalidArgument('The given Axis is not a broader of the Member\'s Axis');
        }

        $parentMemberTag = $axis->getTag() . ':' . ($axis->isMemberPositionning() ? '[0-9]+-' : '');
        $parentMemberPathTags = preg_match('#([^|]*\/'.$parentMemberTag.'[a-z]+\/)#', $this->tag);

        $criteria = Doctrine\Common\Collections\Criteria::create();
        foreach ($parentMemberPathTags as $pathTag) {
            $criteria->andWhere($criteria->expr()->contains('tag', $pathTag));
        }
        $member = $axis->getMembers()->matching($criteria)->toArray();

        if (count($member) === 0) {
            throw new Core_Exception_NotFound("No parent Member matching Axis " . $axis->getRef());
        } else if (count($member) > 1) {
            throw new Core_Exception_TooMany("Too many direct parent Member matching Axis " . $axis->getRef());
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
     * Ajoute un Member donné aux enfants directs du Member courant.
     *
     * @param Orga_Model_Member $childMember
     */
    public function addDirectChild(Orga_Model_Member $childMember)
    {
        if (!($this->hasDirectChild($childMember))) {
            $this->directChildren->add($childMember);
            $childMember->addDirectParent($this);
        }
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
     * Retire un Member donné des enfants directs du Member courant.
     *
     * @param Orga_Model_Member $childMember
     */
    public function removeDirectChild(Orga_Model_Member $childMember)
    {
        if ($this->hasDirectChild($childMember)) {
            $this->directChildren->removeElement($childMember);
            $childMember->removeDirectParent($this);
        }
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
     * @return Orga_Model_Member[]
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
        if (!$this->axis->isBroaderThan($axis)) {
            throw new Core_Exception_InvalidArgument('The given Axis is not a narrower of the Member\'s Axis');
        }

        $criteria = Doctrine\Common\Collections\Criteria::create();
        foreach (explode('|', $this->tag) as $pathTag) {
            $criteria->andWhere($criteria->expr()->contains('tag', $pathTag));
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
     * @return Orga_Model_Cell[]
     */
    public function getCells()
    {
        return $this->cells->toArray();
    }

    /**
     * @return string Représentation textuelle du member
     */
    public function __toString()
    {
        return $this->getCompleteRef();
    }

}
