<?php

namespace User\Domain\ACL\Role;

use Orga\Model\ACL\OrganizationAuthorization;
use Orga_Model_Organization;
use User\Domain\ACL\Action;
use User\Domain\ACL\Authorization\RepositoryAuthorization;
use User\Domain\ACL\Authorization\UserAuthorization;
use User\Domain\ACL\Role;
use User\Domain\User;

/**
 * Application administrator.
 */
class AdminRole extends Role
{
    public function __construct(User $user)
    {
        $this->user = $user;
    }

    public function getAuthorizations()
    {
        $authorizations = [];

        // Admin can create new users
        $authorizations[] = new UserAuthorization($this->user, Action::CREATE());

        // Admin can view, edit, delete, undelete, allow everyone except himself
        /** @var User[] $allUsers */
        $allUsers = User::loadList();
        foreach ($allUsers as $target) {
            if ($target === $this->user) {
                continue;
            }
            $authorizations[] = new UserAuthorization($this->user, Action::VIEW(), $target);
            $authorizations[] = new UserAuthorization($this->user, Action::EDIT(), $target);
            $authorizations[] = new UserAuthorization($this->user, Action::DELETE(), $target);
            $authorizations[] = new UserAuthorization($this->user, Action::UNDELETE(), $target);
            $authorizations[] = new UserAuthorization($this->user, Action::ALLOW(), $target);
        }

        // Admin can edit the repository
        $authorizations[] = new RepositoryAuthorization($this->user, Action::EDIT());

        // Admin can create new organizations
        $authorizations[] = new OrganizationAuthorization($this->user, Action::CREATE());

        // Admin can view, edit, delete, allow on all organizations
        /** @var Orga_Model_Organization[] $organizations */
        $organizations = Orga_Model_Organization::loadList();
        foreach ($organizations as $organization) {
            $authorizations[] = new OrganizationAuthorization($this->user, Action::VIEW(), $organization);
            $authorizations[] = new OrganizationAuthorization($this->user, Action::EDIT(), $organization);
            $authorizations[] = new OrganizationAuthorization($this->user, Action::DELETE(), $organization);
            $authorizations[] = new OrganizationAuthorization($this->user, Action::ALLOW(), $organization);
        }

        return $authorizations;
    }
}
