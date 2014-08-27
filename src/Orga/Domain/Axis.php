<?php

namespace Orga\Domain;

use Core\Translation\TranslatedString;
use Core_Exception;
use Core_Exception_Duplicate;
use Core_Exception_InvalidArgument;
use Core_Exception_NotFound;
use Core_Exception_TooMany;
use Core_Model_Entity;
use Core_Strategy_Ordered;
use Core_Tools;
use Doctrine\Common\Collections\Collection;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Criteria;
use Doctrine\Common\Collections\Selectable;
use Doctrine;
use Orga\Domain\Service\OrgaDomainHelper;

/**
 * Axis
 *
 * @author valentin.claras
 */
class Axis extends Core_Model_Entity
{
    use Core_Strategy_Ordered;

    // Constantes de tris et de filtres.
    const QUERY_NARROWER_TAG = 'narrowerTag';
    const QUERY_BROADER_TAG = 'broaderTag';
    const QUERY_REF = 'ref';
    const QUERY_LABEL = 'label';
    const QUERY_POSITION = 'position';
    const QUERY_NARROWER = 'directNarrower';
    const QUERY_WORKSPACE = 'workspace';


    /**
     * @var int
     */
    protected $id = null;

    /**
     * @var string
     */
    protected $ref = null;

    /**
     * @var TranslatedString
     */
    protected $label;

    /**
     * @var Workspace
     */
    protected $workspace = null;

    /**
     * @var string
     */
    protected $narrowerTag = null;

    /**
     * @var string
     */
    protected $broaderTag = null;

    /**
     * @var Axis
     */
    protected $directNarrower = null;

    /**
     * @var Collection|Axis[]
     */
    protected $directBroaders = null;

    /**
     * @var bool
     */
    protected $contextualizing = false;

    /**
     * @var bool
     */
    protected $memberPositioning = false;

    /**
     * @var Collection|Member[]
     */
    protected $members = null;

    /**
     * @var Collection|Granularity[]
     */
    protected $granularities = null;


    /**
     * @param Workspace $workspace
     * @param string $ref
     * @param Axis $directNarrowerAxis
     * @throws Core_Exception_Duplicate
     * @throws Core_Exception_InvalidArgument
     */
    public function __construct(Workspace $workspace, $ref, Axis $directNarrowerAxis = null)
    {
        $this->label = new TranslatedString();
        $this->directBroaders = new ArrayCollection();
        $this->members = new ArrayCollection();
        $this->granularities = new ArrayCollection();

        $this->workspace = $workspace;
        $this->directNarrower = $directNarrowerAxis;

        // Vérification de l'unicité de la ref.
        //  Pas de set Ref qui est fait pour la modifier.
        Core_Tools::checkRef($ref);
        if ($ref === 'global') {
            throw new Core_Exception_InvalidArgument('An Axis ref cannot be "global".');
        }
        try {
            $this->getWorkspace()->getAxisByRef($ref);
            throw new Core_Exception_Duplicate('An Axis with ref "' . $ref . '" already exists in the Workspace');
        } catch (Core_Exception_NotFound $e) {
        }
        $this->ref = $ref;
        $this->workspace->addAxis($this);

        if ($directNarrowerAxis !== null) {
            $directNarrowerAxis->directBroaders->add($this);
        }

        // Ajout de la position.
        $this->setPosition();
        $this->updateNarrowerTag();
        $this->updateBroaderTag();

        if ($directNarrowerAxis !== null) {
            // Désactivation des cellules dont les membres n'ont pas encore de parent pour cet axe.
            //  Pas de mise à jour des tags des membres qui ne sont pas modifié par l'axe seul.
            foreach ($directNarrowerAxis->getMembers() as $member) {
                $member->disableCells();
            }
        }
    }

    /**
     * @return array
     */
    protected function getContext()
    {
        return array('workspace' => $this->workspace, 'directNarrower' => $this->directNarrower);
    }

    /**
     * Fonction appelée avant un delete de l'objet (défini dans le mapper).
     */
    public function preDelete()
    {
        if (($this->getWorkspace() !== null) && ($this->getWorkspace()->hasAxis($this))) {
            $this->removeFromWorkspace();
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
     * @return string
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param string $ref
     * @throws Core_Exception_InvalidArgument
     * @throws Core_Exception_Duplicate
     */
    public function setRef($ref)
    {
        if ($ref === 'global') {
            throw new Core_Exception_InvalidArgument('An Axis ref cannot be "global".');
        } elseif ($this->ref !== $ref) {
            try {
                $this->getWorkspace()->getAxisByRef($ref);
                throw new Core_Exception_Duplicate('An Axis with ref "' . $ref . '" already exists in the Workspace');
            } catch (Core_Exception_NotFound $e) {
                $this->ref = $ref;
                // Mise à jour des Tags des axes et des membres.
                $this->updateTags();
                // Mise à jour des ref des granularités.
                foreach ($this->getGranularities() as $granularity) {
                    $granularity->updateRef();
                }
            }
        }
    }

    /**
     * @return String
     */
    public function getRef()
    {
        return $this->ref;
    }

    /**
     * @return TranslatedString
     */
    public function getLabel()
    {
        return $this->label;
    }

    /**
     * @return Workspace
     */
    public function getWorkspace()
    {
        return $this->workspace;
    }

    public function removeFromWorkspace()
    {
        if ($this->getWorkspace() !== null) {
            $this->getWorkspace()->removeAxis($this);

            // Suppression de la position.
            $this->deletePosition();

            // Détachement de l'axe du Workspace.
            $this->workspace = null;
        }
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
        $this->narrowerTag = Workspace::PATH_SEPARATOR;
        if ($this->getDirectNarrower() !== null) {
            $this->narrowerTag = $this->getDirectNarrower()->getNarrowerTag();
        }
        $this->narrowerTag .= $this->getAxisTag() . Workspace::PATH_SEPARATOR;

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
        $this->broaderTag = Workspace::PATH_SEPARATOR;
        if ($this->hasDirectBroaders()) {
            $broaderPathTags = [];
            $criteriaTagASC = Doctrine\Common\Collections\Criteria::create();
            $criteriaTagASC->orderBy(['narrowerTag' => 'ASC']);
            foreach ($this->getDirectBroaders()->matching($criteriaTagASC) as $directBroaderAxis) {
                foreach (explode(Workspace::PATH_JOIN, $directBroaderAxis->getBroaderTag()) as $broaderPathTag) {
                    $broaderPathTags[] = $broaderPathTag;
                }
            }
            $pathLink = $this->getAxisTag() . Workspace::PATH_SEPARATOR . Workspace::PATH_JOIN;
            $this->broaderTag = implode($pathLink, $broaderPathTags);
        }
        $this->broaderTag .= $this->getAxisTag() . Workspace::PATH_SEPARATOR;

        if ($this->getDirectNarrower() !== null) {
            $this->getDirectNarrower()->updateBroaderTag();
        }

        // Mise à jour des tags des granularités, basés sur ceux des axes.
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
     * Mets à jour le NarrowerTag, le BroaderTag, et les tags des membres.
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
     * Permet une surcharge facile pour lancer des évents après qu'un objet ait été déplacé.
     */
    protected function hasMove()
    {
        $this->updateTags();
        $this->getWorkspace()->orderGranularities();
    }

    /**
     * @param Axis $a
     * @param Axis $b
     * @return int 1, 0 ou -1
     */
    public static function firstOrderAxes(Axis $a, Axis $b)
    {
        return strcmp($a->getNarrowerTag(), $b->getNarrowerTag());
    }

    /**
     * @param Axis $a
     * @param Axis $b
     * @return int 1, 0 ou -1
     */
    public static function lastOrderAxes(Axis $a, Axis $b)
    {
        if (strpos($a->getNarrowerTag(), $b->getNarrowerTag()) !== false) {
            return -1;
        } elseif (strpos($b->getNarrowerTag(), $a->getNarrowerTag()) !== false) {
            return 1;
        }
        return self::firstOrderAxes($a, $b);
    }

    /**
     * @param Axis $newDirectNarrowerAxis
     * @throws Core_Exception_InvalidArgument
     */
    public function moveTo(Axis $newDirectNarrowerAxis = null)
    {
        if ($this->getDirectNarrower() !== $newDirectNarrowerAxis) {
            if (($newDirectNarrowerAxis !== null)
                && (($newDirectNarrowerAxis === $this) || ($newDirectNarrowerAxis->isBroaderThan($this)))) {
                throw new Core_Exception_InvalidArgument(
                    'The given Axis is equal or broader than the current one.'
                );
            }

            // Vérification de la non contextualisation de l'axe ou ses broaders.
            if ($this->isContextualizing()) {
                throw new Core_Exception_InvalidArgument(
                    'The moving axis must not be contextualizing.'
                );
            } else {
                foreach ($this->getAllBroadersFirstOrdered() as $broaderAxis) {
                    if ($broaderAxis->isContextualizing()) {
                        throw new Core_Exception_InvalidArgument(
                            'Broaders of the moving axis must not be contextualizing.'
                        );
                    }
                }
            }

            // Vérification de la possibilité de collision des axes des granularités.
            foreach ($this->getWorkspace()->getGranularities() as $granularity) {
                foreach ($granularity->getAxes() as $granularityAxis) {
                    // Si l'axe d'une granularité est celui-là ou l'un de ses broader, on vérifie.
                    if (($granularityAxis === $this) || ($granularityAxis->isBroaderThan($this))) {
                        foreach ($granularity->getAxes() as $collisionAxis) {
                            // Si la granularité comporte aussi le nouveau narrower
                            //  ou l'un de ses narrower, on stop le déplacement.
                            if (($collisionAxis === $newDirectNarrowerAxis)
                                || (($newDirectNarrowerAxis !== null)
                                    && ($collisionAxis->isNarrowerThan($newDirectNarrowerAxis)))
                            ) {
                                throw new Core_Exception_InvalidArgument(
                                    'Moving this Axis would broke the granularities.'
                                );
                            }
                        }
                    }
                }
            }

            $oldDirectNarrowerAxis = $this->getDirectNarrower();
            if ($oldDirectNarrowerAxis !== null) {
                // Suprression des membres parents pour l'ancien narrower.
                foreach ($this->getMembers() as $member) {
                    foreach ($member->getDirectChildren() as $childMember) {
                        $childMember->removeDirectParentForAxis($member);
                    }
                }
                // Retirement effective de l'axe de l'ancien narrower.
                $oldDirectNarrowerAxis->directBroaders->removeElement($this);
                // Vérification de l'état des cellules des membres.
                foreach ($oldDirectNarrowerAxis->getMembers() as $oldNarrowerMember) {
                    $oldNarrowerMember->enableCells();
                }
                // La mise à jour du broaderTag du oldDirectNarrowerAxis se fait lors du deletePosition de cet axe.
            }

            // DeletePosition.
            $this->deletePosition();
            $this->directNarrower = $newDirectNarrowerAxis;

            if ($newDirectNarrowerAxis !== null) {
                $newDirectNarrowerAxis->directBroaders->add($this);
                foreach ($newDirectNarrowerAxis->getMembers() as $newNarrowerMember) {
                    $newNarrowerMember->enableCells();
                }
                // La mise à jour du broaderTag du narrrowerAxis est faites lors du setPosition de cet axe.
            }

            // Ajout de la position.
            $this->setPosition();
            // L'ajout de la position ne déclenche pas le hasMove();
            $this->updateTags();
            $this->getWorkspace()->orderGranularities();

            /** @var Granularity[] $changedGranularities */
            $changedGranularities = [];
            // Récupération des granularités dont les axes ont été modifiés.
            if ($oldDirectNarrowerAxis !== null) {
                /** @var Axis[] $oldBranchAxes */
                $oldBranchAxes = $oldDirectNarrowerAxis->getAllNarrowers();
                $oldBranchAxes[] = $oldDirectNarrowerAxis;
                foreach ($oldBranchAxes as $oldBranchAxis) {
                    $changedGranularities = array_merge(
                        $changedGranularities,
                        $oldBranchAxis->getGranularities()->toArray()
                    );
                }
            }
            /** @var Axis[] $newBranchAxes */
            $newBranchAxes = $this->getAllNarrowers();
            $newBranchAxes[] = $this;
            foreach ($newBranchAxes as $newBranchAxis) {
                $changedGranularities = array_merge(
                    $changedGranularities,
                    $newBranchAxis->getGranularities()->toArray()
                );
            }
            $changedGranularities = array_unique($changedGranularities);
            usort($changedGranularities, [Granularity::class, 'orderGranularities']);

            // Mise à jour de la hiérarchie des cellules des granularités modifiées.
            foreach ($changedGranularities as $changedGranularity) {
                $changedGranularity->updateCellsHierarchy();
            }
        }
    }

    /**
     * @return Axis
     */
    public function getDirectNarrower()
    {
        return $this->directNarrower;
    }

    /**
     * @return Axis[]
     */
    public function getAllNarrowers()
    {
        $criteria = Criteria::create();
        foreach (explode(Workspace::PATH_JOIN, $this->getBroaderTag()) as $pathTag) {
            $criteria->andWhere(
                Criteria::expr()->contains('broaderTag', $pathTag)
            );
        }
        $criteria->andWhere(
            Criteria::expr()->neq('broaderTag', $this->getBroaderTag())
        );
        $criteria->orderBy(['narrowerTag' => 'DESC']);
        return $this->getWorkspace()->getAxes()->matching($criteria)->toArray();
    }

    /**
     * @param Axis $broaderAxis
     * @return boolean
     */
    public function hasDirectBroader(Axis $broaderAxis)
    {
        return $this->directBroaders->contains($broaderAxis);
    }

    /**
     * @return bool
     */
    public function hasDirectBroaders()
    {
        return !$this->directBroaders->isEmpty();
    }

    /**
     * @return Collection|Selectable|Axis[]
     */
    public function getDirectBroaders()
    {
        $criteria = Criteria::create();
        $criteria->orderBy(['narrowerTag' => 'ASC']);
        return $this->directBroaders->matching($criteria);
    }

    /**
     * @return Axis[]
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
        return $this->getWorkspace()->getAxes()->matching($criteria)->toArray();
    }

    /**
     * @return Axis[]
     */
    public function getAllBroadersLastOrdered()
    {
        $broaders = $this->getAllBroadersFirstOrdered();
        @usort($broaders, ['Orga\Domain\Axis', 'lastOrderAxes']);
        return $broaders;
    }

    /**
     * @param bool $contextualizing
     * @throws Core_Exception_Duplicate
     */
    public function setContextualize($contextualizing)
    {
        if ($this->contextualizing !== $contextualizing) {
            // Suppression de l'ordre des membres des axes narrowers.
            foreach ($this->getAllNarrowers() as $narrowerAxis) {
                foreach ($narrowerAxis->getMembers() as $childMember) {
                    $childMember->setPosition();
                }
            }

            if (!$contextualizing) {
                // Passage à false pour récupérer les nouveaux contextes des enfants.
                $this->contextualizing = false;

                // Vérification de l'unicité des refs des membres enfants.
                foreach ($this->getAllNarrowers() as $narrowerAxis) {
                    // Recherche de ref double dans le nouveau context pour chaque axe narrower.
                    $contextsForRef = [];
                    foreach ($narrowerAxis->getMembers() as $childMember) {
                        $childMemberRef = $childMember->getRef();
                        $childContextualizingParents = $childMember->getContextualizingParents();
                        if (isset($contextsForRef[$childMemberRef])) {
                            if (in_array($childContextualizingParents, $contextsForRef[$childMemberRef], true)) {
                                $this->contextualizing = true;
                                throw new Core_Exception_Duplicate(
                                    'Can\'t change contextualizing context, members exist with the same ref.'
                                );
                            }
                        } else {
                            $contextsForRef[$childMemberRef] = [];
                        }
                        $contextsForRef[$childMemberRef][] = $childContextualizingParents;
                    }
                }
            } else {
                $this->contextualizing = true;
            }

            // Mise à jour des parentMembersHashKey.
            foreach ($this->getMembers() as $member) {
                $member->updateDirectChildrenMembersParentMembersHashKey();
            }

            // Suppression de l'ordre des membres des axes narrowers.
            foreach ($this->getAllNarrowers() as $narrowerAxis) {
                foreach ($narrowerAxis->getMembers() as $childMember) {
                    $childMember->setPosition();
                }
            }
        }
    }

    /**
     * @return bool
     */
    public function isContextualizing()
    {
        return $this->contextualizing;
    }

    /**
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
     * @return bool
     */
    public function isMemberPositioning()
    {
        return $this->memberPositioning;
    }

    /**
     * @param Member $member
     * @throws Core_Exception_InvalidArgument
     */
    public function addMember(Member $member)
    {
        if ($member->getAxis() !== $this) {
            throw new Core_Exception_InvalidArgument();
        }

        if (!$this->hasMember($member)) {
            $this->members->add($member);

            // Création des nouvelles cellules dans l'ordre des granularités.
            foreach ($this->getOrderedGranularities() as $granularity) {
                $granularity->generateCellsFromNewMember($member);
            }
        }
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
     * @param string $completeRef
     * @throws Core_Exception_NotFound
     * @throws Core_Exception_TooMany
     * @return Member
     */
    public function getMemberByCompleteRef($completeRef)
    {
        $refParts = explode(Member::COMPLETEREF_JOIN, $completeRef);
        $baseRef = (isset($refParts[0]) ? $refParts[0] : '');
        $parentMembersHashKey = (isset($refParts[1]) ? $refParts[1] : null);
        $criteria = \Doctrine\Common\Collections\Criteria::create();
        $criteria->where($criteria->expr()->eq('ref', $baseRef));
        $criteria->andWhere($criteria->expr()->eq('parentMembersHashKey', $parentMembersHashKey));
        $member = $this->members->matching($criteria)->toArray();

        if (empty($member)) {
            throw new Core_Exception_NotFound('No Member matching ref "' . $baseRef . '".');
        } else {
            if (count($member) > 1) {
                throw new Core_Exception_TooMany('Too many Member matching ref "' . $baseRef . '".');
            }
        }

        return array_pop($member);
    }

    /**
     * @param Member $member
     */
    public function removeMember(Member $member)
    {
        if ($this->hasMember($member)) {
            /** @var Cell[] $memberCellsChildCells */
            $memberCellsChildCells = [];
            // Recherche des cellules enfants dont la hiérarchie va être modifiée.
            foreach ($member->getCells() as $cell) {
                foreach ($cell->getChildCells() as $childCell) {
                    // Inutile de mettre à jour les cellules possédant ce membre.
                    if (!$childCell->hasMember($member)) {
                        $memberCellsChildCells[] = $childCell;
                    }
                }
            }

            // Suppression des cellules.
            foreach ($this->getGranularities() as $granularity) {
                $granularity->removeCellsFromMember($member);
            }

            // Suppression de la position.
            $member->setPosition();
            // Suppression du membre effective.
            $this->members->removeElement($member);

            // Suppression des parents du membre.
            foreach ($member->getDirectParents() as $parentMember) {
                $member->removeDirectParentForAxis($parentMember);
            }
            // Suppression du membre en tant que parent.
            foreach ($member->getDirectChildren() as $directChildMember) {
                $directChildMember->removeDirectParentForAxis($member);
            }

            /** @var Cell $childCell */
            foreach (array_unique($memberCellsChildCells) as $childCell) {
                $childCell->updateHierarchy();
            }

            $member->removeFromAxis();

            // Recacul de la cohérence des saisie si nécéssaire.
            if ($this->getWorkspace()->getTimeAxis() === $this) {
                OrgaDomainHelper::getCellInputUpdater()->updateInconsistencyForWorkspace($this->getWorkspace());
            }
        }
    }

    /**
     * @return bool
     */
    public function hasMembers()
    {
        return !$this->members->isEmpty();
    }

    /**
     * @return Collection|Selectable|Member[]
     */
    public function getMembers()
    {
        return $this->members;
    }

    /**
     * @return Collection|Selectable|Member[]
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
     * @param Granularity $granularity
     * @throws Core_Exception_InvalidArgument
     */
    public function addGranularity(Granularity $granularity)
    {
        if (!$granularity->hasAxis($this)) {
            throw new Core_Exception_InvalidArgument();
        }

        if (!$this->hasGranularity($granularity)) {
            $this->granularities->add($granularity);
        }
    }

    /**
     * @param Granularity $granularity
     * @return boolean
     */
    public function hasGranularity(Granularity $granularity)
    {
        return $this->granularities->contains($granularity);
    }

    /**
     * @param Granularity $granularity
     * @throws Core_Exception_InvalidArgument
     */
    public function removeGranularity(Granularity $granularity)
    {
        if ($granularity->getWorkspace() !== null) {
            throw new Core_Exception_InvalidArgument();
        }

        if ($this->hasGranularity($granularity)) {
            $this->granularities->removeElement($granularity);
        }
    }

    /**
     * @return bool
     */
    public function hasGranularities()
    {
        return !$this->granularities->isEmpty();
    }

    /**
     * @return Collection|Granularity[]
     */
    public function getGranularities()
    {
        return $this->granularities;
    }

    /**
     * @return Collection|Granularity[]
     */
    public function getOrderedGranularities()
    {
        $granularities = $this->getGranularities()->toArray();
        usort($granularities, [Granularity::class, 'orderGranularities']);
        return $granularities;
    }

    /**
     * @param Axis $axis
     * @return bool
     */
    public function isNarrowerThan(Axis $axis)
    {
        return ((strpos($axis->narrowerTag, $this->narrowerTag) !== false) && ($axis !== $this));
    }

    /**
     * @param Axis $axis
     * @return bool
     */
    public function isBroaderThan(Axis $axis)
    {
        return $axis->isNarrowerThan($this);
    }

    /**
     * @param Axis[] $axes
     * @return bool
     */
    public function isTransverse($axes)
    {
        foreach ($axes as $axis) {
            if ($axis->isBroaderThan($this) || $this->isBroaderThan($axis) || ($this === $axis)) {
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
