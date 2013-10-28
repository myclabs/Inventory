<?php

namespace Orga\Model\ACL\Role;

use Orga\Model\ACL\OrganizationAuthorization;
use Orga_Model_Organization;
use User\Domain\ACL\Action;
use User\Domain\ACL\Role;
use User\Domain\User;

/**
 * Organization administrator.
 */
class OrganizationAdminRole extends Role
{
    protected $organization;

    public function __construct(User $user, Orga_Model_Organization $organization)
    {
        $this->user = $user;
        $this->organization = $organization;
    }

    public function getAuthorizations()
    {
        $authorizations = [];

        $authorizations[] = new OrganizationAuthorization($this->user, Action::VIEW(), $this->organization);
        $authorizations[] = new OrganizationAuthorization($this->user, Action::EDIT(), $this->organization);
        $authorizations[] = new OrganizationAuthorization($this->user, Action::DELETE(), $this->organization);

        return $authorizations;
    }
}
