<?php
/**
 * Classe Orga_Model_Member
 * @author valentin.claras
 * @author diana.dragusin
 * @package    Orga
 * @subpackage Model
 */

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;

/**
 * Definit un membre d'un axe.
 * @package    Orga
 * @subpackage Model
 */
class Orga_Model_Member extends Core_Model_Entity
{

    // Constantes de tris et de filtres.
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
     */
    public function __construct()
    {
        $this->directParents = new ArrayCollection();
        $this->directChildren = new ArrayCollection();
        $this->cells = new ArrayCollection();
    }

    /**
     * Renvoi les valeurs du contexte pour l'objet.
     * .
     * @return array
     */
    protected function getContext()
    {
        return array('parentMembersHashKey' => $this->parentMembersHashKey, 'axis' => $this->axis);
    }

    /**
     * Fonction appelé avant un persist de l'objet (défini dans le mapper).
     */
    public function preSave()
    {
        $this->checkRefUniqueness();
    }

    /**
     * Fonction appelé avant un update de l'objet (défini dans le mapper).
     */
    public function preUpdate()
    {
        $this->checkRefUniqueness();
    }

    /**
     * Charge l'objet en fonction de sa ref complète et son Axis.
     *
     * @param string $completeRef
     * @param Orga_Model_Axis $axis
     *
     * @return Orga_Model_Member
     */
    public static function loadByCompleteRefAndAxis($completeRef, $axis)
    {
        return $axis->getMemberByCompleteRef($completeRef);
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
        uasort(
            $contextualizingParentMembers,
            function (Orga_Model_Member $a, Orga_Model_Member $b) {
                return $a->getAxis()->getGlobalPosition() - $b->getAxis()->getGlobalPosition();
            }
        );
        $parentMembersRef = [];

        foreach ($contextualizingParentMembers as $parentMember) {
            $parentMembersRef[] = $parentMember->getRef();
        }

        return implode('|', $parentMembersRef);
    }

    /**
     * Vérifie que la ref est unique.
     *
     * @throw Core_Exception_TooMany
     */
    protected function checkRefUniqueness()
    {
        try {
            $member = $this->getAxis()->getMemberByCompleteRef($this->getCompleteRef());

            if ($member !== $this) {
                throw new Core_Exception_TooMany(
                    'Ref "'.$this->getCompleteRef().'" already used with same contextualizing parent members.'
                );
            }
        } catch (Core_Exception_NotFound $e) {
            // Pas de membre trouvé.
        }
    }

    /**
     * Met à jour la hatsKey des cellules du membre.
     */
    protected function updateCellsMembersHashKey()
    {
        foreach ($this->cells as $cell) {
            $cell->updateMembersHashKey();
        }
    }

    /**
     * Met à jour la pertinence des cellules du membre et de leurs cellules enfants.
     */
    protected function updateCellsAllParentsRelevant()
    {
        foreach ($this->cells as $cell) {
            $cell->updateAllParentsRelevant();
            foreach ($cell->getChildCells() as $childCell) {
                $childCell->updateAllParentsRelevant();
            }
        }
    }

    /**
     * Permet une surcharge facile pour lancer des évents après qu'un objet ait été déplacé.
     */
    protected function hasMove()
    {
        $this->updateCellsMembersHashKey();
    }

    /**
     * Mets à jour la hashKey des membres parents contextualisants.
     */
    public function updateParentMembersHashKey()
    {
        $this->parentMembersHashKey = self::buildParentMembersHashKey($this->getContextualizingParents());

        foreach ($this->getDirectChildren() as $childMember) {
            $childMember->updateParentMembersHashKey();
        }

        foreach ($this->getCells() as $cell) {
            $cell->updateMembersHashKey();
        }
    }

    /**
     * Définit la référence du Member.
     *
     * @param string $ref
     */
    public function setRef($ref)
    {
        $this->ref = $ref;

        if ($this->axis !== null) {
            $this->updateParentMembersHashKey();
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
     * Définit l'Axis du Member.
     *
     * @param Orga_Model_Axis $axis
     */
    public function setAxis(Orga_Model_Axis $axis = null)
    {
        if ($this->axis !== $axis) {
            if ($this->axis !== null) {
                $this->axis->removeMember($this);
            }
            $this->axis = $axis;
            $this->updateParentMembersHashKey();
            if ($axis !== null) {
                $axis->addMember($this);
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
     * Ajoute un Member donné aux parents directs du Member courant.
     *
     * @param Orga_Model_Member $parentMember
     */
    public function addDirectParent(Orga_Model_Member $parentMember)
    {
        if (!($this->hasDirectParent($parentMember))) {
            $this->directParents->add($parentMember);
            $parentMember->addDirectChild($this);
            $this->updateParentMembersHashKey();
            $this->updateCellsAllParentsRelevant();
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
     * @param Orga_Model_Member $parentMember
     */
    public function removeDirectParent(Orga_Model_Member $parentMember)
    {
        if ($this->hasDirectParent($parentMember)) {
            $this->directParents->removeElement($parentMember);
            $parentMember->removeDirectChild($this);
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
     * @return Orga_Model_Member[]
     */
    public function getDirectParents()
    {
        return $this->directParents->toArray();
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
     * Renvoie le Member parent pour l'Axis donné.
     *
     * @param Orga_Model_Axis $axis
     *
     * @throws Core_Exception_NotFound No parent member for the given axis
     * @return Orga_Model_Member[]
     */
    public function getParentForAxis(Orga_Model_Axis $axis)
    {
        foreach ($this->directParents as $directParent) {
            if ($directParent->getAxis() === $axis) {
                return $directParent;
            } elseif ($axis->isBroaderThan($directParent->getAxis())) {
                return $directParent->getParentForAxis($axis);
            }
        }
        throw new Core_Exception_NotFound('There is no parent member for the given axis');
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
        return $this->directChildren->toArray();
    }

    /**
     * Renvoie un tableau contenant tous les Member enfants de Member courant.
     *
     * @return Orga_Model_Member[]
     */
    public function getAllChildren()
    {
        $children = [];
        foreach ($this->directChildren as $directChild) {
            $children[] = $directChild;
            foreach ($directChild->getAllChildren() as $recursiveChildren) {
                $children[] = $recursiveChildren;
            }
        }
        return $children;
    }

    /**
     * Renvoie les Member enfants pour l'Axis donné.
     *
     * @param Orga_Model_Axis $axis
     *
     * @return Orga_Model_Member[]
     */
    public function getChildrenForAxis(Orga_Model_Axis $axis)
    {
        if ($this->getAxis()->getDirectNarrower() === $axis) {
            return $this->getDirectChildren();
        } else {
            $children = [];
            foreach ($this->directChildren as $directChild) {
                $children = array_merge($children, $directChild->getChildrenForAxis($axis));
            }
            return array_unique($children, SORT_REGULAR);
        }
    }

    /**
     * Ajoute une Cell à celles utilisant le Member courant.
     *
     * @param Orga_Model_Cell $cell
     */
    public function addCell(Orga_Model_Cell $cell)
    {
        if (! $this->hasCell($cell)) {
            $this->cells->add($cell);
            $cell->addMember($this);
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
     * Retire la Cell donnée des Cells utilisant ce Member.
     *
     * @param Orga_Model_Cell $cell
     */
    public function removeCell(Orga_Model_Cell $cell)
    {
        if ($this->hasCell($cell)) {
            $this->cells->removeElement($cell);
            $cell->removeMember($this);
        }
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
     * @return string Représentation textuelle de l'unité
     */
    public function __toString()
    {
        return $this->getCompleteRef();
    }

}
