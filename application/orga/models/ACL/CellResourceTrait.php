<?php

namespace Orga\Model\ACL;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\Common\Collections\Selectable;
use Orga\Model\ACL\Role\AbstractCellRole;
use Orga\Model\ACL\Role\CellAdminRole;
use Orga\Model\ACL\Role\CellContributorRole;
use Orga\Model\ACL\Role\CellManagerRole;
use Orga\Model\ACL\Role\CellObserverRole;
use Orga_Model_Cell;
use User\Domain\ACL\Authorization\Authorization;
use User\Domain\ACL\Resource\ResourceTrait;

/**
 * Cell resource trait helper.
 *
 * @property Authorization[]|Collection|Selectable $acl
 */
trait CellResourceTrait
{
    use ResourceTrait;

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

    protected function constructACL()
    {
        $this->acl = new ArrayCollection();
        $this->adminRoles = new ArrayCollection();
        $this->managerRoles = new ArrayCollection();
        $this->contributorRoles = new ArrayCollection();
        $this->observerRoles = new ArrayCollection();
    }

    protected function updateACL()
    {
        // Supprime les autorisations héritées
        $this->acl->forAll(function (CellAuthorization $authorization) {
            if (! $authorization->isRoot()) {
                $this->acl->removeElement($authorization);
            }
        });

        // Hérite des ressources parent
        foreach ($this->getParentCells() as $parentCell) {
            foreach ($parentCell->getRootACL() as $parentAuthorization) {
                // L'autorisation sera automatiquement ajoutée à $this->acl
                CellAuthorization::createChildAuthorization($parentAuthorization, $this);
            }
        }
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
     * @return Orga_Model_Cell[]
     */
    abstract public function getParentCells();
}
