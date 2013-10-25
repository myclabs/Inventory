<?php

namespace User\Domain\ACL\Role;

use User\Domain\ACL\Action\DefaultAction;
use User\Domain\ACL\Authorization\RepositoryAuthorization;
use User\Domain\ACL\Authorization\UserAuthorization;
use User\Domain\ACL\Role;
use User\Domain\User;

/**
 * "User" role that every user have.
 */
class UserRole extends Role
{
    public function __construct(User $user)
    {
        $this->user = $user;
    }

    public function getAuthorizations()
    {
        return [
            // User can view, edit and delete himself
            new UserAuthorization($this->user, DefaultAction::VIEW(), $this->user),
            new UserAuthorization($this->user, DefaultAction::EDIT(), $this->user),
            new UserAuthorization($this->user, DefaultAction::DELETE(), $this->user),
            // User can view the repository
            new RepositoryAuthorization($this->user, DefaultAction::VIEW()),
        ];
    }
}
