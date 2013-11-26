<?php

namespace Orga\Model\ACL;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\Common\Collections\Selectable;
use Orga\Model\ACL\Role\AccountAdminRole;
use User\Domain\ACL\Authorization\Authorization;
use User\Domain\ACL\Resource\ResourceTrait;

/**
 * Account resource trait helper.
 *
 * @property Authorization[]|Collection|Selectable $acl
 */
trait AccountResourceTrait
{
    use ResourceTrait;

    /**
     * @var AccountAuthorization[]|Collection
     */
    protected $acl;

    /**
     * Liste des roles administrateurs sur ce compte.
     *
     * @var AccountAdminRole[]|Collection
     */
    protected $adminRoles;

    protected function constructACL()
    {
        $this->acl = new ArrayCollection();
        $this->adminRoles = new ArrayCollection();
    }

    /**
     * @return AccountAdminRole[]
     */
    public function getAdminRoles()
    {
        return $this->adminRoles;
    }

    /**
     * API utilisée uniquement par AccountAdminRole
     *
     * @param AccountAdminRole $adminRole
     */
    public function addAdminRole(AccountAdminRole $adminRole)
    {
        $this->adminRoles->add($adminRole);
    }

    /**
     * API utilisée uniquement par AccountAdminRole
     *
     * @param AccountAdminRole $adminRole
     */
    public function removeAdminRole(AccountAdminRole $adminRole)
    {
        $this->adminRoles->removeElement($adminRole);
    }
}
