<?php

namespace User\Domain\ACL\Authorization;

use Core_Model_Entity;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use User\Domain\ACL\Action;
use User\Domain\ACL\Resource\Resource;
use User\Domain\ACL\Role\Role;
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
     * @var Authorization
     */
    protected $parentAuthorization;

    /**
     * @var Authorization[]|Collection
     */
    protected $childAuthorizations;

    /**
     * Crée des autorisations.
     *
     * @param Role                               $role
     * @param \User\Domain\ACL\Resource\Resource $resource
     * @param Action[]                           $actions
     * @return static[]
     */
    public static function createMany(Role $role, Resource $resource, array $actions)
    {
        $user = $role->getUser();

        $authorizations = [];

        foreach ($actions as $action) {
            /** @var self $authorization */
            $authorization = new static($role, $user, $action, $resource);

            // Ajoute au role
            $role->addAuthorization($authorization);

            // Ajoute à l'utilisateur
            $user->addAuthorization($authorization);

            $authorizations[] = $authorization;
        }

        // Ajoute à la ressource
        $resource->addToACL($authorizations);

        return $authorizations;
    }

    /**
     * Crée une autorisation.
     *
     * @param Role                               $role
     * @param Action                             $action
     * @param \User\Domain\ACL\Resource\Resource $resource
     * @param bool                               $addInverseCollections If true, will add in inverse-side
     *                                                                  collections
     * @return static
     */
    public static function create(Role $role, Action $action, Resource $resource, $addInverseCollections = true)
    {
        $user = $role->getUser();

        /** @var self $authorization */
        $authorization = new static($role, $user, $action, $resource);

        if ($addInverseCollections) {
            // Ajoute au role
            $role->addAuthorization($authorization);

            // Ajoute à l'utilisateur
            $user->addAuthorization($authorization);

            // Ajoute à la ressource
            $resource->addToACL([$authorization]);
        }

        return $authorization;
    }

    /**
     * Crée une autorisation qui hérite d'une autre.
     *
     * @param Authorization                      $parentAuthorization
     * @param \User\Domain\ACL\Resource\Resource $resource Nouvelle ressource
     * @param Action|null                        $action
     * @param bool                               $addInverseCollections If true, will add in inverse-side
     *                                                                  collections
     * @return static
     */
    public static function createChildAuthorization(
        Authorization $parentAuthorization,
        Resource $resource,
        Action $action = null,
        $addInverseCollections = true
    ) {
        $action = $action ?: $parentAuthorization->getAction();

        /** @var self $authorization */
        $authorization = self::create($parentAuthorization->role, $action, $resource, $addInverseCollections);

        $authorization->parentAuthorization = $parentAuthorization;
        if ($addInverseCollections) {
            $parentAuthorization->childAuthorizations->add($authorization);
        }

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
        $this->role = $role;
        $this->user = $user;
        $this->actionId = $action->exportToString();
        $this->resource = $resource;

        $this->childAuthorizations = new ArrayCollection();
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

    /**
     * @return Authorization
     */
    public function getParentAuthorization()
    {
        return $this->parentAuthorization;
    }

    /**
     * @param Authorization $parentAuthorization
     */
    public function setParentAuthorization(Authorization $parentAuthorization)
    {
        $this->parentAuthorization = $parentAuthorization;
    }

    /**
     * @return static[]
     */
    public function getChildAuthorizations()
    {
        return $this->childAuthorizations;
    }

    /**
     * @return bool
     */
    public function isRoot()
    {
        return ($this->parentAuthorization === null);
    }

    /**
     * @return Role
     */
    public function getRole()
    {
        return $this->role;
    }

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     *
     *
     * @param int $id
     */
    public function setId($id)
    {
        $this->id = $id;
    }
}
