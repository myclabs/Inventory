<?php

namespace User\Domain\ACL\ResourceListener;

use User\Domain\ACL\Authorization\UserAuthorization;
use User\Domain\User;

/**
 * Listens to events on the User entity.
 */
class UserListener
{
    public function preRemove(User $user)
    {
        // Cascade remove sur les autorisations portant sur cette ressource
        foreach (UserAuthorization::loadByResource($user) as $authorization) {
            $authorization->delete();
        }
    }
}
