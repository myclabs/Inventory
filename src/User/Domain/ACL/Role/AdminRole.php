<?php

namespace User\Domain\ACL\Role;

use Orga\ACL\Authorization\OrganizationAuthorization;
use Orga_Model_Organization;
use User\Domain\ACL\Action\DefaultAction;
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
        $authorizations[] = new UserAuthorization($this->user, DefaultAction::CREATE());

        // Admin can view, edit, delete, undelete, allow everyone except himself
        /** @var User[] $allUsers */
        $allUsers = User::loadList();
        foreach ($allUsers as $target) {
            if ($target === $this->user) {
                continue;
            }
            $authorizations[] = new UserAuthorization($this->user, DefaultAction::VIEW(), $target);
            $authorizations[] = new UserAuthorization($this->user, DefaultAction::EDIT(), $target);
            $authorizations[] = new UserAuthorization($this->user, DefaultAction::DELETE(), $target);
            $authorizations[] = new UserAuthorization($this->user, DefaultAction::UNDELETE(), $target);
            $authorizations[] = new UserAuthorization($this->user, DefaultAction::ALLOW(), $target);
        }

        // Admin can edit the repository
        $authorizations[] = new RepositoryAuthorization($this->user, DefaultAction::EDIT());

        // Admin can create new organizations
        $authorizations[] = new OrganizationAuthorization($this->user, DefaultAction::CREATE());

        // Admin can view, edit, delete, allow on all organizations
        /** @var Orga_Model_Organization[] $organizations */
        $organizations = Orga_Model_Organization::loadList();
        foreach ($organizations as $organization) {
            $authorizations[] = new OrganizationAuthorization($this->user, DefaultAction::VIEW(), $organization);
            $authorizations[] = new OrganizationAuthorization($this->user, DefaultAction::EDIT(), $organization);
            $authorizations[] = new OrganizationAuthorization($this->user, DefaultAction::DELETE(), $organization);
            $authorizations[] = new OrganizationAuthorization($this->user, DefaultAction::ALLOW(), $organization);
        }

        return $authorizations;
    }
}
