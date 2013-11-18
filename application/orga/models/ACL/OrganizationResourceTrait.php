<?php

namespace Orga\Model\ACL;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\Common\Collections\Selectable;
use Orga\Model\ACL\Role\OrganizationAdminRole;
use User\Domain\ACL\Authorization\Authorization;
use User\Domain\ACL\Resource\ResourceTrait;

/**
 * Organization resource trait helper.
 *
 * @property Authorization[]|Collection|Selectable $acl
 */
trait OrganizationResourceTrait
{
    use ResourceTrait;

    /**
     * @var OrganizationAuthorization[]|Collection
     */
    protected $acl;

    /**
     * Liste des roles administrateurs sur cette organisation.
     *
     * @var OrganizationAdminRole[]|Collection
     */
    protected $adminRoles;

    protected function constructACL()
    {
        $this->acl = new ArrayCollection();
        $this->adminRoles = new ArrayCollection();
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
