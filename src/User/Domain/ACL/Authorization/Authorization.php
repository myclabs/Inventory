<?php

namespace User\Domain\ACL\Authorization;

use Core_Model_Entity;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use User\Domain\ACL\Action;
use User\Domain\ACL\Resource\Resource;
use User\Domain\ACL\Role;
use User\Domain\User;

/**
 * Autorisation d'accès à une ressource.
 *
 * @author matthieu.napoli
 */
abstract class Authorization extends Core_Model_Entity
{
    /**
     * @var int
     */
    protected $id;

    /**
     * Role créateur de l'autorisation.
     *
     * @var Role
     */
    protected $role;

    /**
     * @var User
     */
    protected $user;

    /**
     * On ne peut pas utiliser le type Action, les Criterias ne filtrent pas sur des VO (comparaison de type ===).
     * @var string
     */
    protected $actionId;

    /**
     * @var Resource
     */
    protected $resource;

    /**
     * Héritage des droits entre ressources.
     *
     * @var static
     */
    protected $parentAuthorization;

    /**
     * @var static[]|Collection
     */
    protected $childAuthorizations;

    /**
     * Crée une autorisation qui hérite de celle-ci.
     *
     * @param Role                               $role
     * @param User                               $user
     * @param Action                             $action
     * @param \User\Domain\ACL\Resource\Resource $resource
     * @return static
     */
    public static function create(Role $role, User $user, Action $action, Resource $resource)
    {
        /** @var self $authorization */
        $authorization = new static($role, $user, $action, $resource);

        // Ajoute au role
        $role->addAuthorization($authorization);

        // Ajoute à l'utilisateur
        $user->addAuthorization($authorization);

        // Ajoute à la ressource
        $resource->addToACL($authorization);

        return $authorization;
    }

    /**
     * @param Role                               $role
     * @param User                               $user
     * @param Action                             $action
     * @param \User\Domain\ACL\Resource\Resource $resource
     */
    private function __construct(Role $role, User $user, Action $action, Resource $resource)
    {
        $this->$role = $role;
        $this->user = $user;
        $this->actionId = $action->exportToString();
        $this->resource = $resource;

        $this->childAuthorizations = new ArrayCollection();
    }

    /**
     * Crée une autorisation qui hérite de celle-ci.
     *
     * @param \User\Domain\ACL\Resource\Resource $resource
     * @return static
     */
    public function createChildAuthorization(Resource $resource)
    {
        /** @var self $authorization */
        $authorization = new static($this->role, $this->user, $this->getAction(), $resource);
        $authorization->parentAuthorization = $this;

        $this->childAuthorizations->add($authorization);

        return $authorization;
    }

    /**
     * @return User
     */
    public function getUser()
    {
        return $this->user;
    }

    /**
     * @return Action
     */
    public function getAction()
    {
        return Action::importFromString($this->actionId);
    }

    /**
     * @return Action
     */
    public function getActionId()
    {
        return $this->actionId;
    }

    /**
     * @return \User\Domain\ACL\Resource\Resource
     */
    public function getResource()
    {
        return $this->resource;
    }
}
