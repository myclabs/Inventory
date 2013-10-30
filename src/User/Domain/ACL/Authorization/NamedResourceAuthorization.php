<?php

namespace User\Domain\ACL\Authorization;

use User\Domain\ACL\Action;
use User\Domain\ACL\Resource\NamedResource;
use User\Domain\User;

/**
 * Autorisation d'accès à une ressource nommée.
 *
 * @author matthieu.napoli
 */
class NamedResourceAuthorization extends Authorization
{
    protected $resource;

    public function __construct(User $user, Action $action, NamedResource $resource)
    {
        $this->user = $user;
        $this->setAction($action);
        $this->resource = $resource;

        $this->resource->addToACL($this);
    }

    /**
     * @return Resource
     */
    public function getResource()
    {
        return $this->resource;
    }
}
