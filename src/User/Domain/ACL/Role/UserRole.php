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
    public function buildAuthorizations()
    {
        $this->authorizations->clear();

        // User can view, edit and delete himself
        UserAuthorization::create($this, $this->user, Action::VIEW(), $this->user);
        UserAuthorization::create($this, $this->user, Action::EDIT(), $this->user);
        UserAuthorization::create($this, $this->user, Action::DELETE(), $this->user);

        // User can view the repository
        $repository = NamedResource::loadByName('repository');
        NamedResourceAuthorization::create($this, $this->user, Action::VIEW(), $repository);
    }

    public static function getLabel()
    {
        return __('User', 'role', 'roleUser');
    }
}
