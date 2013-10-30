<?php

namespace User\Domain\ACL\Role;

use Orga\Model\ACL\OrganizationAuthorization;
use Orga_Model_Organization;
use User\Domain\ACL\Action;
use User\Domain\ACL\Authorization\NamedResourceAuthorization;
use User\Domain\ACL\Authorization\UserAuthorization;
use User\Domain\ACL\Resource\NamedResource;
use User\Domain\ACL\Role;
use User\Domain\User;

/**
 * Application administrator.
 */
class AdminRole extends Role
{
    public function buildAuthorizations()
    {
        $this->authorizations->clear();

        // Admin can create new users
        $allUsersResource = NamedResource::loadByName(User::class);
        NamedResourceAuthorization::create($this, $this->user, Action::CREATE(), $allUsersResource);

        // Admin can view, edit, delete, undelete, allow everyone except himself
        $allUsers = User::loadList();
        foreach ($allUsers as $target) {
            /** @var User $target */
            if ($target === $this->user) {
                continue;
            }
            UserAuthorization::create($this, $this->user, Action::VIEW(), $target);
            UserAuthorization::create($this, $this->user, Action::EDIT(), $target);
            UserAuthorization::create($this, $this->user, Action::DELETE(), $target);
            UserAuthorization::create($this, $this->user, Action::UNDELETE(), $target);
            UserAuthorization::create($this, $this->user, Action::ALLOW(), $target);
        }

        // Admin can edit the repository
        $repository = NamedResource::loadByName('repository');
        NamedResourceAuthorization::create($this, $this->user, Action::EDIT(), $repository);

        // Admin can create new organizations
        $organizationsResource = NamedResource::loadByName(Orga_Model_Organization::class);
        NamedResourceAuthorization::create($this, $this->user, Action::CREATE(), $organizationsResource);

        // Admin can view, edit, delete, allow on all organizations
        $organizations = Orga_Model_Organization::loadList();
        foreach ($organizations as $organization) {
            /** @var Orga_Model_Organization $organization */
            OrganizationAuthorization::create($this, $this->user, Action::VIEW(), $organization);
            OrganizationAuthorization::create($this, $this->user, Action::EDIT(), $organization);
            OrganizationAuthorization::create($this, $this->user, Action::DELETE(), $organization);
            OrganizationAuthorization::create($this, $this->user, Action::ALLOW(), $organization);
        }
    }
}
