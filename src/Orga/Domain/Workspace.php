<?php

namespace Orga\Domain;

use Account\Domain\Account;
use Classification\Domain\Axis as ClassificationAxis;
use Classification\Domain\ContextIndicator;
use Core\Translation\TranslatedString;
use Core_Exception_InvalidArgument;
use Core_Exception_NotFound;
use Core_Exception_TooMany;
use Core_Exception_UndefinedAttribute;
use Core_Model_Entity;
use Doctrine\Common\Collections\Collection;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Criteria;
use Doctrine\Common\Collections\Selectable;
use MyCLabs\ACL\Model\EntityResource;
use Orga\Domain\Service\OrgaDomainHelper;
use Orga\Domain\ACL\WorkspaceAdminRole;

/**
 * Workspace
 *
 * @author valentin.claras
 */
class Workspace extends Core_Model_Entity implements EntityResource
{
    // Constantes de path des Axis, Member, Granularity et Cell.
    const PATH_SEPARATOR = '/';
    const PATH_JOIN = '&';

    /**
     * @var string
     */
    protected $id = null;

    /**
     * @var Account
     */
    protected $account = null;

    /**
     * @var TranslatedString
     */
    protected $label;

    /**
     * @var Collection|Axis[]
     */
    protected $axes = null;

    /**
     * @var Axis
     */
    protected $timeAxis = null;

    /**
     * @var Collection|Granularity[]
     */
    protected $granularities = null;

    /**
     * @var Granularity
     */
    protected $granularityForInventoryStatus = null;

    /**
     * @var Collection|ContextIndicator[]
     */
    protected $contextIndicators;

    /**
     * @var Collection|\Orga\Domain\ACL\WorkspaceAdminRole[]
     */
    protected $adminRoles;


    /**
     * @param Account $account
     */
    public function __construct(Account $account)
    {
        $this->label = new TranslatedString();
        $this->axes = new ArrayCollection();
        $this->granularities = new ArrayCollection();
        $this->contextIndicators = new ArrayCollection();
        $this->adminRoles = new ArrayCollection();

        $this->account = $account;
    }

    /**
     * Fonction appelée avant un delete de l'objet (défini dans le mapper).
     */
    public function preDelete()
    {
        foreach ($this->getAxes() as $axis) {
            $this->removeAxis($axis);
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
     * @return Account
     */
    public function getAccount()
    {
        return $this->account;
    }

    /**
     * @return TranslatedString
     */
    public function getLabel()
    {
        return $this->label;
    }

    /**
     * @param Axis $axis
     * @throws Core_Exception_InvalidArgument
     */
    public function addAxis(Axis $axis)
    {
        if ($axis->getWorkspace() !== $this) {
            throw new Core_Exception_InvalidArgument('addAxis should only be used when instantiating a new Axis.');
        }
        if (!$this->hasAxis($axis)) {
            $this->axes->add($axis);
        }
    }

    /**
     * @param Axis $axis
     * @return bool
     */
    public function hasAxis(Axis $axis)
    {
        return $this->axes->contains($axis);
    }

    /**
     * @param string $ref
     * @throws Core_Exception_NotFound
     * @throws Core_Exception_TooMany
     * @return Axis
     */
    public function getAxisByRef($ref)
    {
        $criteria = Criteria::create();
        $criteria->where($criteria->expr()->eq('ref', $ref));
        $axis = $this->axes->matching($criteria)->toArray();

        if (count($axis) === 0) {
            throw new Core_Exception_NotFound('No Axis in Workspace matching ref "' . $ref . '".');
        } elseif (count($axis) > 1) {
            throw new Core_Exception_TooMany('Too many Axis in Workspace matching "' . $ref . '".');
        }

        return array_pop($axis);
    }

    /**
     * @param Axis $axis
     */
    public function removeAxis(Axis $axis)
    {
        if ($this->hasAxis($axis)) {
            // Désactivacion du caractère contexualisant des axes.
            $axis->setContextualize(false);

            // Suppression des granularités liés.
            foreach ($this->getGranularities() as $granularity) {
                if ($granularity->hasAxis($axis)) {
                    $this->removeGranularity($granularity);
                }
            }

            // Suppression du time Axis éventuel.
            if ($axis === $this->getTimeAxis()) {
                $this->setTimeAxis(null);
            }

            $narrowerAxis = $axis->getDirectNarrower();
            // Cache des membres parents/enfants afin de les réassocier.
            if ($narrowerAxis !== null) {
                $parentsChildrenMembersLinks = [];
                foreach ($axis->getMembers() as $axisMember) {
                    $parentsChildrenMembersLinks[$axisMember->getCompleteRef()] = [
                        'childrenMembers' => $axisMember->getDirectChildren()->toArray(),
                        'parentsMembers' => $axisMember->getDirectParents()->toArray()
                    ];
                }
            }
            // Déplacement des axes broaders sur le narrower (potentiellement à la racine).
            foreach ($axis->getDirectBroaders() as $broaderAxis) {
                $broaderAxis->moveTo($narrowerAxis);
                // Insértion à la position actuel de l'axe pour consérver le même ordre de la structure.
                $broaderAxis->setPosition($axis->getPosition());
            }
            // Réassociation des enfants et des parents.
            if ($narrowerAxis !== null) {
                foreach ($parentsChildrenMembersLinks as $axisMemberArray) {
                    /** @var Member $childMember */
                    foreach ($axisMemberArray['childrenMembers'] as $childMember) {
                        /** @var Member $parentMember */
                        foreach ($axisMemberArray['parentsMembers'] as $parentMember) {
                            $childMember->setDirectParentForAxis($parentMember);
                        }
                    }
                }
            }

            // Déplacement de l'axe à la racine pour le retirer du narrower et placer à la fin.
            if ($narrowerAxis !== null) {
                $axis->moveTo();
            } else {
                $axis->setPosition($axis->getLastEligiblePosition());
            }

            // Suppression des membres.
            foreach ($axis->getMembers() as $member) {
                $axis->removeMember($member);
            }

            // Suppression de l'axe effective.
            $this->axes->removeElement($axis);
            $axis->removeFromWorkspace();
        }
    }

    /**
     * @return bool
     */
    public function hasAxes()
    {
        return !$this->axes->isEmpty();
    }

    /**
     * @return Collection|Selectable|Axis[]
     */
    public function getAxes()
    {
        return $this->axes;
    }

    /**
     * @return Axis[]
     */
    public function getRootAxes()
    {
        $criteria = Criteria::create();
        $criteria->where(Criteria::expr()->isNull('directNarrower'));
        $criteria->orderBy(['position' => 'ASC']);
        return $this->axes->matching($criteria)->toArray();
    }

    /**
     * @return Axis[]
     */
    public function getFirstOrderedAxes()
    {
        $criteria = Criteria::create();
        $criteria->orderBy(['narrowerTag' => 'ASC']);
        return $this->axes->matching($criteria)->toArray();
    }

    /**
     * @return Axis[]
     */
    public function getLastOrderedAxes()
    {
        $axes = $this->getFirstOrderedAxes();
        @usort($axes, ['Orga\Domain\Axis', 'lastOrderAxes']);
        return $axes;
    }

    /**
     * @param Axis $axis
     */
    public function setTimeAxis(Axis $axis = null)
    {
        $this->timeAxis = $axis;

        OrgaDomainHelper::getCellInputUpdater()->updateInconsistencyForWorkspace($this);
    }

    /**
     * @return Axis
     */
    public function getTimeAxis()
    {
        return $this->timeAxis;
    }

    /**
     * Ordonne les granularités.
     */
    public function orderGranularities()
    {
        $granularities = [];
        // Définition du tableau d'indexation.
        foreach ($this->getGranularities() as $granularity) {
            $granularities[spl_object_hash($granularity)] = array(
                'granularity' => $granularity,
                'position' => ''
            );
        }

        // Calcul de l'index "binaire" en fonction des axes.
        foreach ($this->getFirstOrderedAxes() as $axis) {
            foreach ($this->getGranularities() as $granularity) {
                if (!$axis->hasGranularity($granularity)) {
                    $granularities[spl_object_hash($granularity)]['position'] .= '1';
                } else {
                    $granularities[spl_object_hash($granularity)]['position'] .= '0';
                }
            }
        }

        /** @var Granularity[] $orderedGranularities */
        $orderedGranularities = [];
        // Création d'un nouveau tableau référançant chaque granularité par son index "binaire".
        foreach ($granularities as $granularity) {
            $orderedGranularities[$granularity['position']] = $granularity['granularity'];
        }
        ksort($orderedGranularities);

        $position = 1;
        // Définition de la position dans l'ordre inverse.
        foreach (array_reverse($orderedGranularities) as $orderedGranularity) {
            $orderedGranularity->setPosition($position);
            $position++;
        }
    }

    /**
     * @param Granularity $granularity
     * @throws Core_Exception_InvalidArgument
     */
    public function addGranularity(Granularity $granularity)
    {
        if ($granularity->getWorkspace() !== $this) {
            throw new Core_Exception_InvalidArgument();
        }

        if (!$this->hasGranularity($granularity)) {
            $this->granularities->add($granularity);
            $this->orderGranularities();

            // Mise à jour de la hiérarchie des granularité plus fine.
            //  Possibilité de cellules parentes manquantes nécéssitant la "désactivation".
            foreach ($granularity->getNarrowerGranularities() as $narrowerGranularity) {
                $narrowerGranularity->updateCellsHierarchy();
            }
        }
    }

    /**
     * @param Granularity $granularity
     * @return bool
     */
    public function hasGranularity(Granularity $granularity)
    {
        return $this->granularities->contains($granularity);
    }

    /**
     * @param string $ref
     * @throws Core_Exception_NotFound
     * @throws Core_Exception_TooMany
     * @return Granularity
     */
    public function getGranularityByRef($ref)
    {
        $criteria = Criteria::create();
        $criteria->where($criteria->expr()->eq('ref', $ref));
        $granularity = $this->granularities->matching($criteria)->toArray();

        if (empty($granularity)) {
            throw new Core_Exception_NotFound('No Granularity in Workspace matching ref "' . $ref . '".');
        } elseif (count($granularity) > 1) {
            throw new Core_Exception_TooMany('Too many Granularity in Workspace matching ref "' . $ref . '".');
        }

        return array_pop($granularity);
    }

    /**
     * @param Granularity $granularity
     */
    public function removeGranularity(Granularity $granularity)
    {
        if ($this->hasGranularity($granularity)) {
            // Suppression de la granularité des inventaires éventuelle.
            if ($this->getGranularityForInventoryStatus() === $granularity) {
                $this->setGranularityForInventoryStatus();
            }
            // Suppression des saisies.
            if ($granularity->isInput()) {
                $granularity->setInputConfigGranularity();
            }
            foreach ($granularity->getInputGranularities() as $inputGranularity) {
                $inputGranularity->setInputConfigGranularity();
            }

            $this->granularities->removeElement($granularity);
            $this->orderGranularities();

            // Mise à jour de la hiérarchie des granularité plus fine.
            //  Possibilité de disparition de cellules parentes manquantes provoquant la "réactivation".
            foreach ($granularity->getNarrowerGranularities() as $narrowerGranularity) {
                $narrowerGranularity->updateCellsHierarchy();
            }

            $granularity->removeFromWorkspace();
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
     * @return Collection|Selectable|Granularity[]
     */
    public function getGranularities()
    {
        return $this->granularities;
    }

    /**
     * @return Collection|Selectable|Granularity[]
     */
    public function getOrderedGranularities()
    {
        $criteria = Criteria::create();
        $criteria->orderBy(['position' => 'ASC']);
        return $this->granularities->matching($criteria);
    }

    /**
     * @return Granularity[]
     */
    public function getACLGranularities()
    {
        $criteria = Criteria::create();
        $criteria->where(Criteria::expr()->eq('cellsWithACL', true));
        $criteria->orderBy(['position' => 'ASC']);
        return $this->granularities->matching($criteria)->toArray();
    }

    /**
     * @return Granularity[]
     */
    public function getInputGranularities()
    {
        $criteria = Criteria::create();
        $criteria->where(Criteria::expr()->neq('inputConfigGranularity', null));
        $criteria->orderBy(['position' => 'ASC']);
        return $this->granularities->matching($criteria)->toArray();
    }

    /**
     * @param Granularity $granularity
     */
    public function setGranularityForInventoryStatus(Granularity $granularity = null)
    {
        if ($this->granularityForInventoryStatus !== $granularity) {
            // Reset Cells InventoryStatus.
            if ($this->granularityForInventoryStatus !== null) {
                foreach ($this->granularityForInventoryStatus->getCells() as $cell) {
                    $cell->setInventoryStatus(Cell::INVENTORY_STATUS_NOTLAUNCHED);
                }
            }

            $this->granularityForInventoryStatus = $granularity;

            // Changements des granularités du suivi de l'inventaire qui ne sont plus narrower de la nouvelle.
            foreach ($this->getGranularities() as $workspaceGranularity) {
                if (($granularity === null) || (!$workspaceGranularity->isNarrowerThan($granularity))) {
                    $workspaceGranularity->setCellsMonitorInventory(false);
                }
            }
        }
    }

    /**
     * @return Granularity
     */
    public function getGranularityForInventoryStatus()
    {
        return $this->granularityForInventoryStatus;
    }

    /**
     * @param ContextIndicator $contextIndicator
     */
    public function addContextIndicator(ContextIndicator $contextIndicator)
    {
        if (!$this->hasContextIndicator($contextIndicator)) {
            $this->contextIndicators->add($contextIndicator);
        }
    }

    /**
     * @param ContextIndicator $contextIndicator
     * @return bool
     */
    public function hasContextIndicator(ContextIndicator $contextIndicator)
    {
        return $this->contextIndicators->contains($contextIndicator);
    }

    /**
     * @param ContextIndicator $contextIndicator
     */
    public function removeContextIndicator(ContextIndicator $contextIndicator)
    {
        if ($this->hasContextIndicator($contextIndicator)) {
            $this->contextIndicators->removeElement($contextIndicator);
        }
    }

    /**
     * @return bool
     */
    public function hasContextIndicators()
    {
        return !$this->contextIndicators->isEmpty();
    }

    /**
     * @return Collection|ContextIndicator[]
     */
    public function getContextIndicators()
    {
        return $this->contextIndicators;
    }

    /**
     * @return ClassificationAxis[]
     */
    public function getClassificationAxes()
    {
        /** @var ClassificationAxis[] $classificationAxes */
        $classificationAxes = [];

        foreach ($this->getContextIndicators() as $classificationContextIndicator) {
            foreach ($classificationContextIndicator->getAxes() as $classificationAxis) {
                $classificationAxes[] = $classificationAxis;
            }
        }

        if (!empty($classificationAxes)) {
            $classificationAxes = array_unique($classificationAxes);
            foreach ($classificationAxes as $indexClassificationAxis => $classificationAxis) {
                foreach ($classificationAxes as $checkClassificationAxis) {
                    if ($classificationAxis->isBroaderThan($checkClassificationAxis)) {
                        unset($classificationAxes[$indexClassificationAxis]);
                        continue 2;
                    }
                }
            }
            usort(
                $classificationAxes,
                function ($a, $b) {
                    /** @var ClassificationAxis $a */
                    /** @var ClassificationAxis $b */
                    if ($a->getLibrary()->getId() < $b->getLibrary()->getId()) {
                        return -1;
                    }
                    if ($a->getLibrary()->getId() > $b->getLibrary()->getId()) {
                        return 1;
                    }
                    if ($a->getPosition() < $b->getPosition()) {
                        return -1;
                    }
                    if ($a->getPosition() > $b->getPosition()) {
                        return 1;
                    }
                    return strcasecmp($a->getRef(), $b->getRef());
                }
            );
        }

        return $classificationAxes;
    }

    /**
     * @return \Orga\Domain\ACL\WorkspaceAdminRole[]
     */
    public function getAdminRoles()
    {
        return $this->adminRoles;
    }

    /**
     * @param \Orga\Domain\ACL\WorkspaceAdminRole $adminRole
     */
    public function addAdminRole(WorkspaceAdminRole $adminRole)
    {
        $this->adminRoles->add($adminRole);
    }

    /**
     * @param \Orga\Domain\ACL\WorkspaceAdminRole $adminRole
     */
    public function removeAdminRole(WorkspaceAdminRole $adminRole)
    {
        $this->adminRoles->removeElement($adminRole);
    }
}
