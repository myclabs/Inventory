<?php

namespace User\Domain\ACL\Role;

use User\Domain\ACL\Action;
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
            new UserAuthorization($this->user, Action::VIEW(), $this->user),
            new UserAuthorization($this->user, Action::EDIT(), $this->user),
            new UserAuthorization($this->user, Action::DELETE(), $this->user),
            // User can view the repository
            new RepositoryAuthorization($this->user, Action::VIEW()),
        ];
    }
}
