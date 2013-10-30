<?php

namespace User\Domain\ACL\Role;

use User\Domain\ACL\Action;
use User\Domain\ACL\Authorization\NamedResourceAuthorization;
use User\Domain\ACL\Authorization\UserAuthorization;
use User\Domain\ACL\Resource\NamedResource;
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
        $repository = NamedResource::loadByName('repository');

        return [
            // User can view, edit and delete himself
            new UserAuthorization($this->user, Action::VIEW(), $this->user),
            new UserAuthorization($this->user, Action::EDIT(), $this->user),
            new UserAuthorization($this->user, Action::DELETE(), $this->user),
            // User can view the repository
            new NamedResourceAuthorization($this->user, Action::VIEW(), $repository),
        ];
    }
}
