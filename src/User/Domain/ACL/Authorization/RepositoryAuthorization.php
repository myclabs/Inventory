<?php

namespace User\Domain\ACL\Authorization;

use User\Domain\ACL\Action\Action;
use User\Domain\ACL\Authorization;
use User\Domain\User;

/**
 * Autorisation d'accès à un référentiel.
 *
 * @author matthieu.napoli
 */
class RepositoryAuthorization extends Authorization
{
    public function __construct(User $user, Action $action)
    {
        $this->user = $user;
        $this->action = $action;
    }
}
