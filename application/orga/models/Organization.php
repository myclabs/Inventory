<?php
/**
 * Classe Orga_Model_Organization
 * @author     valentin.claras
 * @author     maxime.fourt
 * @package    Orga
 * @subpackage Model
 */

use Account\Domain\Account;
use Classification\Domain\ContextIndicator;
use Core\Translation\TranslatedString;
use Doctrine\Common\Collections\Collection;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Criteria;
use Doctrine\Common\Collections\Selectable;
use MyCLabs\ACL\Model\EntityResource;
use Orga\Model\ACL\OrganizationAdminRole;

/**
 * Organization.
 * @package    Orga
 * @subpackage Model
 */
class Orga_Model_Organization extends Core_Model_Entity implements EntityResource
{
    // Constantes de tris et de filtres.
    const QUERY_ACCOUNT = 'account';
    // Constantes de path des Axis, Member, Granularity et Cell.
    const PATH_SEPARATOR = '/';
    const PATH_JOIN = '&';

    /**
     * Identifiant unique de l'Organization.
     *
     * @var string
     */
    protected $id = null;

    /**
     * Account possédant l'organization.
     *
     * @var Account
     */
    protected $account = null;

    /**
     * Label de l'Organization.
     *
     * @var TranslatedString
     */
    protected $label;

    /**
     * Collection des Axis de l'Organization.
     *
     * @var Collection|Orga_Model_Axis[]
     */
    protected $axes = null;

    /**
     * Collection des Granularity de l'Organization.
     *
     * @var Collection|Orga_Model_Granularity[]
     */
    protected $granularities = null;

    /**
     * Granularity organisationnelle où est spécifiée le statut des inventaires.
     *
     * @var Orga_Model_Granularity
     */
    protected $granularityForInventoryStatus = null;

    /**
     * Collection des ContextIndicator utilisés par l'Organization
     *
     * @var Collection|ContextIndicator[]
     */
    protected $contextIndicators;

    /**
     * Liste des roles administrateurs sur cette organisation.
     *
     * @var OrganizationAdminRole[]|Collection
     */
    protected $adminRoles;


    /**
     * Constructeur de la classe Organization.
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
     * Renvoie l'id de l'Organization.
     *
     * @return string
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Renvoie l'Account auquel appartient l'Organization.
     *
     * @return Account
     */
    public function getAccount()
    {
        return $this->account;
    }

    /**
     * Renvoie le label textuel du projet.
     *
     * @return TranslatedString
     */
    public function getLabel()
    {
        return $this->label;
    }

    /**
     * Ajoute un Axis à la collection de l'Organization.
     *
     * @param Orga_Model_Axis $axis
     *
     * @throws Core_Exception_InvalidArgument
     * @throws Core_Exception_Duplicate
     */
    public function addAxis(Orga_Model_Axis $axis)
    {
        if ($axis->getOrganization() !== $this) {
            throw new Core_Exception_InvalidArgument();
        }
        if (!$this->hasAxis($axis)) {
            $this->axes->add($axis);
        }
    }

    /**
     * Vérifie si l'Axis donné appartient à ceux de l'Organization.
     *
     * @param Orga_Model_Axis $axis
     *
     * @return boolean
     */
    public function hasAxis(Orga_Model_Axis $axis)
    {
        return $this->axes->contains($axis);
    }

    /**
     * Retourne un Axis du organization en fonction de la ref donnée.
     *
     * @param string $ref
     *
     * @throws Core_Exception_NotFound
     * @throws Core_Exception_TooMany
     *
     * @return Orga_Model_Axis
     */
    public function getAxisByRef($ref)
    {
        $criteria = Doctrine\Common\Collections\Criteria::create();
        $criteria->where($criteria->expr()->eq('ref', $ref));
        $axis = $this->axes->matching($criteria)->toArray();

        if (count($axis) === 0) {
            throw new Core_Exception_NotFound('No Axis in Organization matching ref "'.$ref.'".');
        } elseif (count($axis) > 1) {
            throw new Core_Exception_TooMany('Too many Axis in Organization matching "'.$ref.'".');
        }

        return array_pop($axis);
    }

    /**
     * Retire un Axis de ceux de l'Organization.
     *
     * @param Orga_Model_Axis $axis
     */
    public function removeAxis(Orga_Model_Axis $axis)
    {
        if ($this->hasAxis($axis)) {
            $narrowerAxis = $axis->getDirectNarrower();
            // Déplacement des broaders au narrower.
            foreach ($axis->getDirectBroaders() as $broaderAxis) {
                $broaderAxis->moveTo($narrowerAxis);
            }
            // Déplacement de l'axe à la racine pour retirer du narrower et placer à la fin.
            if ($narrowerAxis !== null) {
                $axis->moveTo();
                foreach ($axis->getMembers() as $axisMember) {
                    foreach ($axisMember->getDirectChildren() as $childMember) {
                        $childMember->removeDirectParentForAxis($axisMember);
                    }
                }
            } else {
                $axis->setPosition($axis->getLastEligiblePosition());
            }

            $this->axes->removeElement($axis);
            $axis->removeFromOrganization();

            // Suppression des granularités liés.
            foreach ($axis->getGranularities() as $granularity) {
                $this->removeGranularity($granularity);
            }
        }
    }

    /**
     * Vérifie si le Organization ossède au moins un Axis.
     *
     * @return bool
     */
    public function hasAxes()
    {
        return !$this->axes->isEmpty();
    }

    /**
     * Renvoie les Axis de l'Organization.
     *
     * @return Collection|Selectable|Orga_Model_Axis[]
     */
    public function getAxes()
    {
        return $this->axes;
    }

    /**
     * Retourne un tableau contenant les Axis racines de l'Organization.
     *
     * @return Orga_Model_Axis[]
     */
    public function getRootAxes()
    {
        $criteria = Doctrine\Common\Collections\Criteria::create();
        $criteria->where(Doctrine\Common\Collections\Criteria::expr()->isNull('directNarrower'));
        $criteria->orderBy(['position' => 'ASC']);
        return $this->axes->matching($criteria)->toArray();
    }

    /**
     * Retourne un tableau contenant les Axis de l'Organization ordonnés par première exploration.
     *
     * @return Orga_Model_Axis[]
     */
    public function getFirstOrderedAxes()
    {
        $criteria = Doctrine\Common\Collections\Criteria::create();
        $criteria->orderBy(['narrowerTag' => 'ASC']);
        return $this->axes->matching($criteria)->toArray();
    }

    /**
     * Retourne un tableau contenant les Axis de l'Organization ordonnés par dernière exploration.
     *
     * @return Orga_Model_Axis[]
     */
    public function getLastOrderedAxes()
    {
        $axes = $this->getFirstOrderedAxes();
        @usort($axes, ['Orga_Model_Axis', 'lastOrderAxes']);
        return $axes;
    }

    public function orderGranularities()
    {
        $granularities = array();
        foreach ($this->getGranularities() as $granularity) {
            $granularities[spl_object_hash($granularity)] = array(
                'granularity' => $granularity,
                'position'    => ''
            );
        }
        foreach ($this->getFirstOrderedAxes() as $axis) {
            foreach ($this->getGranularities() as $granularity) {
                if (!$axis->hasGranularity($granularity)) {
                    $granularities[spl_object_hash($granularity)]['position'] .= '1';
                } else {
                    $granularities[spl_object_hash($granularity)]['position'] .= '0';
                }
            }
        }

        /** @var Orga_Model_Granularity[] $orderedGranularities */
        $orderedGranularities = array();
        foreach ($granularities as $granularity) {
            $orderedGranularities[$granularity['position']] = $granularity['granularity'];
        }
        ksort($orderedGranularities);

        $position = 1;
        foreach (array_reverse($orderedGranularities) as $orderedGranularity) {
            $orderedGranularity->setPosition($position);
            $position++;
        }
    }

    /**
     * Ajoute une Granularity au Organization
     *
     * @param Orga_Model_Granularity $granularity
     *
     * @throws Core_Exception_InvalidArgument
     */
    public function addGranularity(Orga_Model_Granularity $granularity)
    {
        if ($granularity->getOrganization() !== $this) {
            throw new Core_Exception_InvalidArgument();
        }

        if (!$this->hasGranularity($granularity)) {
            $this->granularities->add($granularity);
            $this->orderGranularities();
        }
    }

    /**
     * Vérifie que la Granularity donnée appartient à celles de l'Organization.
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
     * Retourne une Granularity du organization en fonction de la ref donnée.
     *
     * @param string $ref
     *
     * @throws Core_Exception_NotFound
     * @throws Core_Exception_TooMany
     *
     * @return Orga_Model_Granularity
     */
    public function getGranularityByRef($ref)
    {
        $criteria = Doctrine\Common\Collections\Criteria::create();
        $criteria->where($criteria->expr()->eq('ref', $ref));
        $granularity = $this->granularities->matching($criteria)->toArray();

        if (empty($granularity)) {
            throw new Core_Exception_NotFound('No Granularity in Organization matching ref "'.$ref.'".');
        } elseif (count($granularity) > 1) {
            throw new Core_Exception_TooMany('Too many Granularity in Organization matching ref "'.$ref.'".');
        }

        return array_pop($granularity);
    }

    /**
     * Retire la Granularity donnée de celles de l'Organization.
     *
     * @param Orga_Model_Granularity $granularity
     */
    public function removeGranularity(Orga_Model_Granularity $granularity)
    {
        if ($this->hasGranularity($granularity)) {
            $cellChildCells = [];
            foreach ($granularity->getCells() as $cell) {
                $cellChildCells[$cell->getMembersHashKey()] = $cell->getChildCells();
            }

            $this->granularities->removeElement($granularity);

            /** @var Orga_Model_Cell[] $childCells */
            foreach ($cellChildCells as $childCells) {
                foreach ($childCells as $childCell) {
                    $childCell->updateHierarchy();
                }
            }
        }
    }

    /**
     * Vérifie que le Organization possède au moins une Granularity.
     *
     * @return bool
     */
    public function hasGranularities()
    {
        return !$this->granularities->isEmpty();
    }

    /**
     * Renvoie un tableau des Granularity de l'Organization.
     *
     * @return Collection|Selectable|Orga_Model_Granularity[]
     */
    public function getGranularities()
    {
        return $this->granularities;
    }

    /**
     * Renvoie un tableau ordonné des Granularity de l'Organization.
     *
     * @return Collection|Selectable|Orga_Model_Granularity[]
     */
    public function getOrderedGranularities()
    {
        $criteria = Doctrine\Common\Collections\Criteria::create();
        $criteria->orderBy(['position' => 'ASC']);
        return $this->granularities->matching($criteria);
    }

    /**
     * Spécifie la Granularity où est spécifié le statut des inventaires.
     *
     * @param Orga_Model_Granularity $granularity
     *
     * @throws Core_Exception_InvalidArgument
     */
    public function setGranularityForInventoryStatus(Orga_Model_Granularity $granularity = null)
    {
        if ($this->granularityForInventoryStatus !== $granularity) {
            if ($this->granularityForInventoryStatus !== null) {
                foreach ($this->granularityForInventoryStatus->getCells() as $cell) {
                    $cell->setInventoryStatus(Orga_Model_Cell::STATUS_NOTLAUNCHED);
                }
            }
            $this->granularityForInventoryStatus = $granularity;
        }
    }

    /**
     * Renvoie l'instance de la Granularity où est spécifié le statut des inventaires.
     *
     * @throws Core_Exception_UndefinedAttribute
     *
     * @return Orga_Model_Granularity
     */
    public function getGranularityForInventoryStatus()
    {
        if ($this->granularityForInventoryStatus === null) {
            throw new Core_Exception_UndefinedAttribute('Granularity for inventory status has not been chosen.');
        }
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
     * @return Classification\Domain\Axis[]
     */
    public function getClassificationAxes()
    {
        /** @var Classification\Domain\Axis[] $classificationAxes */
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
                    /** @var Classification\Domain\Axis $a */
                    /** @var Classification\Domain\Axis $b */
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
     * Renvoie les Granularity de saisie.
     *
     * @return Orga_Model_Granularity[]
     */
    public function getInputGranularities()
    {
        $criteria = Criteria::create();
        $criteria->where(Criteria::expr()->neq('inputConfigGranularity', null));
        $criteria->orderBy(['position' => 'ASC']);
        return $this->granularities->matching($criteria)->toArray();
    }

    /**
     * Retourne la cellule globale de la structure organisationelle.
     *
     * @return Orga_Model_Cell
     */
    public function getGlobalCell()
    {
        return $this->getGranularityByRef('global')->getCellByMembers([]);
    }

    /**
     * @return OrganizationAdminRole[]
     */
    public function getAdminRoles()
    {
        return $this->adminRoles;
    }

    /**
     * API utilisée uniquement par OrganizationAdminRole
     *
     * @param OrganizationAdminRole $adminRole
     */
    public function addAdminRole(OrganizationAdminRole $adminRole)
    {
        $this->adminRoles->add($adminRole);
    }

    /**
     * API utilisée uniquement par OrganizationAdminRole
     *
     * @param OrganizationAdminRole $adminRole
     */
    public function removeAdminRole(OrganizationAdminRole $adminRole)
    {
        $this->adminRoles->removeElement($adminRole);
    }
}
