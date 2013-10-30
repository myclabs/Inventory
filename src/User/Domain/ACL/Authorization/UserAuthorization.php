<?php

namespace User\Domain\ACL\Authorization;

use Core_Model_Query;
use User\Domain\ACL\Action;
use User\Domain\ACL\Authorization\Authorization;
use User\Domain\User;

/**
 * Autorisation d'accès à un utilisateur.
 *
 * @author matthieu.napoli
 */
class UserAuthorization extends Authorization
{
    /**
     * @var User
     */
    protected $resource;

    /**
     * @param User   $user
     * @param Action $action
     * @param User   $resource
     */
    public function __construct(User $user, Action $action, User $resource)
    {
        $this->user = $user;
        $this->setAction($action);
        $this->resource = $resource;

        $this->resource->addToACL($this);
    }

    /**
     * @return User
     */
    public function getResource()
    {
        return $this->resource;
    }
}