<?php

namespace User\Domain\ACL\Authorization;

use Core_Model_Query;
use User\Domain\ACL\Action;
use User\Domain\ACL\Authorization;
use User\Domain\User;

/**
 * Autorisation d'accès à un utilisateur.
 *
 * @author matthieu.napoli
 */
class UserAuthorization extends Authorization
{
    /**
     * @var User|null
     */
    protected $resource;

    /**
     * @param User      $user
     * @param Action    $action
     * @param User|null $resource Can be null, for example with the "CREATE" action
     */
    public function __construct(User $user, Action $action, User $resource = null)
    {
        $this->user = $user;
        $this->action = $action;
        $this->resource = $resource;
    }

    /**
     * @return User|null
     */
    public function getResource()
    {
        return $this->resource;
    }

    /**
     * @param User $resource
     * @return self[]
     */
    public static function loadByResource(User $resource)
    {
        $query = new Core_Model_Query();
        $query->filter->addCondition('resource', $resource);
        return self::loadList($query);
    }
}
