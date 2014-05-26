<?php
/**
 * Classe Orga_Model_Axis
 * @author valentin.claras
 * @author diana.dragusin
 * @package    Orga
 * @subpackage Model
 */

use Core\Translation\TranslatedString;
use Doctrine\Common\Collections\Collection;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Criteria;
use Doctrine\Common\Collections\Selectable;

/**
 * Objet métier définissant un axe organisationnel au sein d'un organization.
 * @package    Orga
 * @subpackage Model
 */
class Orga_Model_Axis extends Core_Model_Entity
{
    use Core_Strategy_Ordered;

    // Constantes de tris et de filtres.
    const QUERY_NARROWER_TAG = 'narrowerTag';
    const QUERY_BROADER_TAG = 'broaderTag';
    const QUERY_REF = 'ref';
    const QUERY_LABEL = 'label';
    const QUERY_POSITION = 'position';
    const QUERY_NARROWER = 'directNarrower';
    const QUERY_ORGANIZATION = 'organization';


    /**
     * Identifiant unique de l'axe.
     *
     * @var int
     */
    protected $id = null;

    /**
     * Référence unique (au sein d'un organization) de l'axe.
     *
     * @var string
     */
    protected $ref = null;

    /**
     * Label de l'axe.
     *
     * @var TranslatedString
     */
    protected $label;

    /**
     * Organization contenant l'axe.
     *
     * @var Orga_Model_Organization
     */
    protected $organization = null;

    /**
     * Tag identifiant l'axe dans la hiérarchie des narrowers de l'organization.
     *
     * @var string
     */
    protected $narrowerTag = null;

    /**
     * Tag identifiant l'axe dans la hiérarchie des broaders de l'organization.
     *
     * @var string
     */
    protected $broaderTag = null;

    /**
     * Axe narrower de l'axe courant.
     *
     * @var Orga_Model_Axis
     */
    protected $directNarrower = null;

    /**
     * Collection des Axis broader de l'axe courant.
     *
     * @var Collection|Orga_Model_Axis[]
     */
    protected $directBroaders = null;

    /**
     * Définit si l'Axis courant contextualise les Member.
     *
     * @var bool
     */
    protected $contextualizing = false;

    /**
     * Définit si l'Axis courant permet le positionnement des Member. (ou ordre alphabétique)
     *
     * @var bool
     */
    protected $memberPositioning = false;

    /**
     * Collection des Member de l'Axis courant.
     *
     * @var Collection|Orga_Model_Member[]
     */
    protected $members = null;

    /**
     * Collection des granularités utilisant l'axe.
     *
     * @var Collection|Orga_Model_Granularity[]
     */
    protected $granularities = null;


    /**
     * Constructeur de la classe Axis.
     *
     * @param Orga_Model_Organization $organization
     * @param string $ref
     * @param Orga_Model_Axis $directNarrowerAxis
     *
     * @throws Core_Exception_Duplicate
     * @throws Core_Exception_InvalidArgument
     */
    public function __construct(Orga_Model_Organization $organization, $ref, Orga_Model_Axis $directNarrowerAxis = null)
    {
        $this->label = new TranslatedString();
        $this->directBroaders = new ArrayCollection();
        $this->members = new ArrayCollection();
        $this->granularities = new ArrayCollection();

        $this->organization = $organization;
        $this->directNarrower = $directNarrowerAxis;

        Core_Tools::checkRef($ref);
        if ($ref === 'global') {
            throw new Core_Exception_InvalidArgument('An Axis ref cannot be "global".');
        }
        try {
            $this->getOrganization()->getAxisByRef($ref);
            throw new Core_Exception_Duplicate('An Axis with ref "'.$ref.'" already exists in the Organization');
        } catch (Core_Exception_NotFound $e) {
        }
        $this->ref = $ref;
        $this->organization->addAxis($this);

        if ($directNarrowerAxis !== null) {
            foreach ($directNarrowerAxis->getOrderedMembers() as $member) {
                $member->setPosition();
            }
            $directNarrowerAxis->directBroaders->add($this);
            foreach ($directNarrowerAxis->getOrderedMembers() as $member) {
                $member->setPosition();
            }
        }

        $this->setPosition();
        $this->updateNarrowerTag();
        $this->updateBroaderTag();

        if ($directNarrowerAxis !== null) {
            foreach ($directNarrowerAxis->getMembers() as $member) {
                $member->updateParentsMembers();
            }
        }
    }

    /**
     * Renvoi les valeurs du contexte pour l'objet.
     * .
     * @return array
     */
    protected function getContext()
    {
        return array('organization' => $this->organization, 'directNarrower' => $this->directNarrower);
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
     * Met à jour les hashKey des membres et des cellules.
     */
    public function updateTags()
    {
        $this->updateNarrowerTag();
        $this->updateBroaderTag();
        foreach ($this->getMembers() as $member) {
            $member->updateTags();
        }
    }

    /**
     * Met à jour les hashKey des membres et des cellules.
     */
    public function updateTagsAndHierarchy()
    {
        $this->updateNarrowerTag();
        $this->updateBroaderTag();
        foreach ($this->getMembers() as $member) {
            $member->updateTagsAndHierarchy();
        }
    }

    /**
     * Renvoie l'id de l'Axis.
     *
     * @return string
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Définit la référence de l'axe. Ne peut pas être "global".
     *
     * @param string $ref
     *
     * @throws Core_Exception_InvalidArgument
     * @throws Core_Exception_Duplicate
     */
    public function setRef($ref)
    {
        if ($ref === 'global') {
            throw new Core_Exception_InvalidArgument('An Axis ref cannot be "global".');
        } elseif ($this->ref !== $ref) {
            try {
                $this->getOrganization()->getAxisByRef($ref);
                throw new Core_Exception_Duplicate('An Axis with ref "'.$ref.'" already exists in the Organization');
            } catch (Core_Exception_NotFound $e) {
                $this->ref = $ref;
                $this->updateTags();
                foreach ($this->getGranularities() as $granularity) {
                    $granularity->updateRef();
                }
            }
        }
    }

    /**
     * Renvoie la référence de l'axe.
     *
     * @return String
     */
    public function getRef()
    {
        return $this->ref;
    }

    /**
     * Renvoie le label de l'axe.
     *
     * @return TranslatedString
     */
    public function getLabel()
    {
        return $this->label;
    }

    /**
     * Renvoie le organization de l'axe.
     *
     * @return Orga_Model_Organization
     */
    public function getOrganization()
    {
        return $this->organization;
    }

    /**
     * Retire (supprime) l'axe de l'organization.
     *
     * Nécessaire pour supprimer sa position.
     */
    public function removeFromOrganization()
    {
        $this->getOrganization()->removeAxis($this);
        $this->deletePosition();
    }

    /**
     * @return string
     */
    public function getAxisTag()
    {
        return $this->getPosition() . '-' . $this->getRef();
    }

    /**
     * Mets à jour le tag des narrowers de l'axe.
     */
    public function updateNarrowerTag()
    {
        $this->narrowerTag = Orga_Model_Organization::PATH_SEPARATOR;
        if ($this->getDirectNarrower() !== null) {
            $this->narrowerTag = $this->getDirectNarrower()->getNarrowerTag();
        }
        $this->narrowerTag .= $this->getAxisTag() . Orga_Model_Organization::PATH_SEPARATOR;

        foreach ($this->getDirectBroaders() as $directBroaderAxis) {
            $directBroaderAxis->updateNarrowerTag();
        }
    }

    /**
     * Renvoie le tag des narrowers l'axe.
     *
     * @return string
     */
    public function getNarrowerTag()
    {
        return $this->narrowerTag;
    }

    /**
     * Mets à jour le tag des broaders de l'axe.
     */
    public function updateBroaderTag()
    {
        $this->broaderTag = Orga_Model_Organization::PATH_SEPARATOR;
        if ($this->hasDirectBroaders()) {
            $broaderPathTags = [];
            $criteriaDESC = Doctrine\Common\Collections\Criteria::create();
            $criteriaDESC->orderBy(['narrowerTag' => 'ASC']);
            foreach ($this->getDirectBroaders()->matching($criteriaDESC) as $directBroaderAxis) {
                foreach (explode(Orga_Model_Organization::PATH_JOIN, $directBroaderAxis->getBroaderTag()) as $broaderPathTag) {
                    $broaderPathTags[] = $broaderPathTag;
                }
            }
            $pathLink = $this->getAxisTag() . Orga_Model_Organization::PATH_SEPARATOR . Orga_Model_Organization::PATH_JOIN;
            $this->broaderTag = implode($pathLink, $broaderPathTags);
        }
        $this->broaderTag .= $this->getAxisTag() . Orga_Model_Organization::PATH_SEPARATOR;

        if ($this->getDirectNarrower() !== null) {
            $this->getDirectNarrower()->updateBroaderTag();
        }

        foreach ($this->getGranularities() as $granularity) {
            $granularity->updateTag();
        }
    }

    /**
     * Renvoie le tag des broaders de l'axe.
     *
     * @return string
     */
    public function getBroaderTag()
    {
        return $this->broaderTag;
    }

    /**
     * Permet une surcharge facile pour lancer des évents après qu'un objet ait été déplacé.
     */
    protected function hasMove()
    {
        $this->updateTags();
        $this->getOrganization()->orderGranularities();
    }

    /**
     * Permet d'ordonner des Axis entre eux.
     *
     * @param Orga_Model_Axis $a
     * @param Orga_Model_Axis $b
     *
     * @return int 1, 0 ou -1
     */
    public static function firstOrderAxes(Orga_Model_Axis $a, Orga_Model_Axis $b)
    {
        return strcmp($a->getNarrowerTag(), $b->getNarrowerTag());
    }

    /**
     * Permet d'ordonner des Axis entre eux.
     *
     * @param Orga_Model_Axis $a
     * @param Orga_Model_Axis $b
     *
     * @return int 1, 0 ou -1
     */
    public static function lastOrderAxes(Orga_Model_Axis $a, Orga_Model_Axis $b)
    {
        if (strpos($a->getNarrowerTag(), $b->getNarrowerTag()) !== false) {
            return -1;
        } elseif (strpos($b->getNarrowerTag(), $a->getNarrowerTag()) !== false) {
            return 1;
        }
        return self::firstOrderAxes($a, $b);
    }

    /**
     * Définit l'axe narrower de l'axe courant.
     *
     * @param Orga_Model_Axis $newDirectNarrowerAxis
     *
     * @throws Core_Exception_InvalidArgument
     */
    public function moveTo(Orga_Model_Axis $newDirectNarrowerAxis = null)
    {
        if ($this->getDirectNarrower() !== $newDirectNarrowerAxis) {
            if ($newDirectNarrowerAxis !== null && $newDirectNarrowerAxis->isBroaderThan($this)) {
                throw new Core_Exception_InvalidArgument('The given Axis is broader than the current one.');
            }

            $oldDirectNarrowerAxis = $this->getDirectNarrower();
            if ($oldDirectNarrowerAxis !== null) {
                if ($oldDirectNarrowerAxis->hasDirectBroader($this)) {
                    $oldDirectNarrowerAxis->directBroaders->removeElement($this);
                    foreach ($oldDirectNarrowerAxis->getMembers() as $member) {
                        $member->updateParentsMembers();
                    }
                    // La mise à jour du broaderTag du oldNarrrowerAxis est faites lors du deletePosition de cet axe.
                }
            }

            $this->deletePosition();
            $this->directNarrower = $newDirectNarrowerAxis;

            if ($newDirectNarrowerAxis !== null) {
                if (!($newDirectNarrowerAxis->hasDirectBroader($this))) {
                    $newDirectNarrowerAxis->directBroaders->add($this);
                    foreach ($newDirectNarrowerAxis->getMembers() as $member) {
                        $member->updateParentsMembers();
                    }
                    // La mise à jour du broaderTag du narrrowerAxis est faites lors du setPosition de cet axe.
                }
            }

            // L'update des tags est effectué pas hasMove().
            $this->setPosition();
            $this->updateTagsAndHierarchy();
        }
    }

    /**
     * Renvoie l'axe narrower de l'axe courant.
     *
     * @return Orga_Model_Axis
     */
    public function getDirectNarrower()
    {
        return $this->directNarrower;
    }

    /**
     * Retourne tous les narrowers de l'Axis dans l'ordre de première exploration.
     *
     * @return Orga_Model_Axis[]
     */
    public function getAllNarrowers()
    {
        $criteria = Criteria::create();
        foreach (explode(Orga_Model_Organization::PATH_JOIN, $this->getBroaderTag()) as $pathTag) {
            $criteria->andWhere(
                Criteria::expr()->contains('broaderTag', $pathTag)
            );
        }
        $criteria->andWhere(
            Criteria::expr()->neq('broaderTag', $this->getBroaderTag())
        );
        $criteria->orderBy(['narrowerTag' => 'DESC']);
        return $this->getOrganization()->getAxes()->matching($criteria)->toArray();
    }

    /**
     * Vérifie si un Axis donné est un broader de l'axe courant.
     *
     * @param Orga_Model_Axis $broaderAxis
     *
     * @return boolean
     */
    public function hasDirectBroader(Orga_Model_Axis $broaderAxis)
    {
        return $this->directBroaders->contains($broaderAxis);
    }

    /**
     * Indique si l'Axis possède des broaders directs.
     *
     * @return bool
     */
    public function hasDirectBroaders()
    {
        return !$this->directBroaders->isEmpty();
    }

    /**
     * Retourne l'ensemble des broaders directs de l'Axis.
     *
     * @return Collection|Selectable|Orga_Model_Axis[]
     */
    public function getDirectBroaders()
    {
        $criteria = Criteria::create();
        $criteria->orderBy(['narrowerTag' => 'ASC']);
        return $this->directBroaders->matching($criteria);
    }

    /**
     * Retourne tous les broaders de l'Axis dans l'ordre de première exploration.
     *
     * @return Orga_Model_Axis[]
     */
    public function getAllBroadersFirstOrdered()
    {
        $criteria = Criteria::create();
        $criteria->where(
            Criteria::expr()->contains('narrowerTag', $this->getNarrowerTag())
        );
        $criteria->andWhere(
            Criteria::expr()->neq('narrowerTag', $this->getNarrowerTag())
        );
        $criteria->orderBy(['narrowerTag' => 'ASC']);
        return $this->getOrganization()->getAxes()->matching($criteria)->toArray();
    }

    /**
     * Retourne tous les broaders de l'Axis dans l'ordre de dernière exploration.
     *
     * @return Orga_Model_Axis[]
     */
    public function getAllBroadersLastOrdered()
    {
        $broaders = $this->getAllBroadersFirstOrdered();
        @usort($broaders, ['Orga_Model_Axis', 'lastOrderAxes']);
        return $broaders;
    }

    /**
     * Définit si l'Axis contextualise ses membres.
     *
     * @param bool $contextualizing
     */
    public function setContextualize($contextualizing)
    {
        if ($this->contextualizing !== $contextualizing) {
            $this->contextualizing = $contextualizing;

            foreach ($this->getMembers() as $member) {
                $member->updateDirectChildrenMembersParentMembersHashKey();
            }
        }
    }

    /**
     * Indique si l'axe contextualise les Member.
     *
     * @return bool
     */
    public function isContextualizing()
    {
        return $this->contextualizing;
    }

    /**
     * Définit si l'Axis permet le positionnement de ses membres.
     *
     * @param bool $memberPositioning
     */
    public function setMemberPositioning($memberPositioning)
    {
        if ($this->memberPositioning !== $memberPositioning) {
            $this->memberPositioning = $memberPositioning;

            foreach ($this->getMembers() as $member) {
                $member->updateTags();
            }
        }
    }

    /**
     * Indique si l'axe permet le positionnement de ses membres.
     *
     * @return bool
     */
    public function isMemberPositioning()
    {
        return $this->memberPositioning;
    }

    /**
     * Ajoute une Member à l'Axis.
     *
     * @param Orga_Model_Member $member
     *
     * @throws Core_Exception_InvalidArgument
     */
    public function addMember(Orga_Model_Member $member)
    {
        if ($member->getAxis() !== $this) {
            throw new Core_Exception_InvalidArgument();
        }

        if (!$this->hasMember($member)) {
            $this->members->add($member);
            foreach ($this->getGranularities() as $granularity) {
                $granularity->generateCellsFromNewMember($member);
            }
            foreach ($member->getCells() as $cell) {
                $cell->enable();
            }
        }
    }

    /**
     * Vérifie si le Member passé fait partie de l'Axis.
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
     * Retourne un tableau contenant les members de l'Axis.
     *
     * @param string $completeRef
     *
     * @throws Core_Exception_NotFound
     * @throws Core_Exception_TooMany
     *
     * @return Orga_Model_Member
     */
    public function getMemberByCompleteRef($completeRef)
    {
        $refParts = explode(Orga_Model_Member::COMPLETEREF_JOIN, $completeRef);
        $baseRef = (isset($refParts[0]) ? $refParts[0] : '');
        $parentMembersHashKey = (isset($refParts[1]) ? $refParts[1] : null);
        $criteria = \Doctrine\Common\Collections\Criteria::create();
        $criteria->where($criteria->expr()->eq('ref', $baseRef));
        $criteria->andWhere($criteria->expr()->eq('parentMembersHashKey', $parentMembersHashKey));
        $member = $this->members->matching($criteria)->toArray();

        if (empty($member)) {
            throw new Core_Exception_NotFound('No Member matching ref "'.$baseRef.'".');
        } else {
            if (count($member) > 1) {
                throw new Core_Exception_TooMany('Too many Member matching ref "'.$baseRef.'".');
            }
        }

        return array_pop($member);
    }

    /**
     * Supprime le Member donné de la collection de l'Axis.
     *
     * @param Orga_Model_Member $member
     */
    public function removeMember(Orga_Model_Member $member)
    {
        if ($this->hasMember($member)) {
            /** @var Orga_Model_Cell[] $memberCellsChildCells */
            $memberCellsChildCells = [];
            foreach ($member->getCells() as $cell) {
                foreach ($cell->getChildCells() as $childCell) {
                    // Inutile de mettre à jour les cellules possédant ce membre.
                    if (!$childCell->hasMember($member)) {
                        $memberCellsChildCells[] = $childCell;
                    }
                }
            }

            if ($this->isMemberPositioning()) {
                $member->setPosition($member->getLastEligiblePosition());
            }
            $this->members->removeElement($member);
            foreach ($member->getDirectChildren() as $directChildMember) {
                $directChildMember->removeDirectParentForAxis($member);
            }
            foreach ($this->granularities as $granularity) {
                $granularity->removeCellsFromMember($member);
            }

            foreach ($memberCellsChildCells as $childCell) {
                $childCell->updateHierarchy();
            }
            $member->removeFromAxis();
        }
    }

    /**
     * Vérifie si l'Axis possède des Member.
     *
     * @return bool
     */
    public function hasMembers()
    {
        return !$this->members->isEmpty();
    }

    /**
     * Retourne un tableau contenant les members de l'Axis.
     *
     * @return Collection|Selectable|Orga_Model_Member[]
     */
    public function getMembers()
    {
        return $this->members;
    }

    /**
     * Retourne un tableau contenant les members de l'Axis.
     *
     * @return Collection|Selectable|Orga_Model_Member[]
     */
    public function getOrderedMembers()
    {
        $criteria = Criteria::create();
        if ($this->isMemberPositioning()) {
            $criteria->orderBy(['parentMembersHashKey' => 'ASC', 'position' => 'ASC']);
        } else {
            $criteria->orderBy(['ref' => 'ASC']);
        }
        return $this->members->matching($criteria);
    }

    /**
     * Ajoute une Granularity à l'Axis ourrant.
     *
     * @param Orga_Model_Granularity $granularity
     *
     * @throws Core_Exception_InvalidArgument
     */
    public function addGranularity(Orga_Model_Granularity $granularity)
    {
        if (!$granularity->hasAxis($this)) {
            throw new Core_Exception_InvalidArgument();
        }

        if (!$this->hasGranularity($granularity)) {
            $this->granularities->add($granularity);
        }
    }

    /**
     * Vérifie si une Granularity donnée fait partit de la collection de celles utilisant l'Axis.
     *
     * @param Orga_Model_Granularity $granularity
     *
     * @return boolean
     */
    public function hasGranularity(Orga_Model_Granularity $granularity)
    {
        return $this->granularities->contains($granularity);
    }

    /**
     * Vérifie que l'Axis possède au moins une Granularity.
     *
     * @return bool
     */
    public function hasGranularities()
    {
        return !$this->granularities->isEmpty();
    }

    /**
     * Renvoie toute les Granularity utilisant l'Axis courant.
     *
     * @return Collection|Orga_Model_Granularity[]
     */
    public function getGranularities()
    {
        return $this->granularities;
    }

    /**
     * Vérifie si l'Axis courant est narrower de l'Axis donné.
     *
     * @param Orga_Model_Axis $axis
     *
     * @return bool
     */
    public function isNarrowerThan(Orga_Model_Axis $axis)
    {
        return ((strpos($axis->narrowerTag, $this->narrowerTag) !== false) && ($axis !== $this));
    }

    /**
     * Vérifie si l'Axis courant est broader de l'Axis donné.
     *
     * @param Orga_Model_Axis $axis
     *
     * @return bool
     */
    public function isBroaderThan(Orga_Model_Axis $axis)
    {
        return $axis->isNarrowerThan($this);
    }

    /**
     * Vérifie si l'Axis courant n'est ni narrower ni broader d'un des Axis donnés.
     *
     * @param Orga_Model_Axis[] $axes
     *
     * @return bool
     */
    public function isTransverse($axes)
    {
        foreach ($axes as $axis) {
            if ($axis->isBroaderThan($this) ||  $this->isBroaderThan($axis) || ($this === $axis)) {
                return false;
            }
        }
        return true;
    }

    /**
     * @return string Représentation textuelle de l'Axis
     */
    public function __toString()
    {
        return $this->getRef();
    }
}
