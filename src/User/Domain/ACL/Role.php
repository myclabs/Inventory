<?php

namespace User\Domain\ACL;

use Core_Model_Entity;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use User\Domain\ACL\Authorization\Authorization;
use User\Domain\User;

/**
 * Role
 */
abstract class Role extends Core_Model_Entity
{
    /**
     * @var int
     */
    protected $id;

    /**
     * @var User
     */
    protected $user;

    /**
     * @var Authorization[]|Collection
     */
    protected $authorizations;

    public function __construct(User $user)
    {
        $this->authorizations = new ArrayCollection();
        $this->user = $user;

        $this->buildAuthorizations();
    }

    /**
     * @return Authorization[]
     */
    abstract public function buildAuthorizations();

    /**
     * Destroy all the role's authorizations.
     */
    public function destroyAuthorizations()
    {
        foreach ($this->authorizations as $authorization) {
            $authorization->getUser()->removeAuthorizations($authorization);
            $authorization->getResource()->removeFromACL($authorization);
        }
        $this->authorizations->clear();
    }

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @return User
     */
    public function getUser()
    {
        return $this->user;
    }

    /**
     * @return Authorization[]
     */
    public function getAuthorizations()
    {
        return $this->authorizations;
    }

    /**
     * Méthode utilisée uniquement par Authorization::create()
     *
     * @param Authorization $authorization
     */
    public function addAuthorization(Authorization $authorization)
    {
        $this->authorizations->add($authorization);
    }

    /**
     * Retourne le nom du rôle.
     *
     * @return string
     */
    public static function getLabel()
    {
        return '';
    }
}
