<?php

namespace User\Domain\ACL;

use Core_Exception_NotFound;
use Core_Model_Query;
use Doctrine\Common\Collections\Collection;
use Doctrine\Common\Collections\ArrayCollection;
use User\Domain\ACL\SecurityIdentity;
use User\Domain\User;

/**
 * Role des ACL : regroupe plusieurs autorisations.
 *
 * @author matthieu.napoli
 */
class Role extends SecurityIdentity
{
    /**#@+
     * Constante de tri et filtre
     */
    const QUERY_REF = 'ref';
    const QUERY_NAME = 'name';
    /**#@-*/

    /**
     * @var string
     */
    protected $ref;

    /**
     * @var string
     */
    protected $name;

    /**
     * Utilisateurs qui ont ce rôle
     * @var \User\Domain\User[]|Collection
     */
    protected $users;


    /**
     * @param string $ref
     * @param string $name
     */
    public function __construct($ref = null, $name = null)
    {
        parent::__construct();
        $this->ref = $ref;
        $this->name = $name;
        $this->users = new ArrayCollection();
    }

    /**
     * Renvoie les utilisateurs possédant ce rôle
     * @return User[]
     */
    public function getUsers()
    {
        return $this->users;
    }

    /**
     * @param User $user
     * @return bool
     */
    public function hasUser(User $user)
    {
        return $this->users->contains($user);
    }

    public function addUser(User $user)
    {
        if (!$this->hasUser($user)) {
            $this->users->add($user);
            $user->addRole($this);
        }
    }

    public function removeUser(User $user)
    {
        if ($this->hasUser($user)) {
            $this->users->removeElement($user);
            $user->removeRole($this);
        }
    }

    /**
     * Charge un rôle par son ref
     * @param string $ref
     * @return Role
     * @throws Core_Exception_NotFound
     */
    public static function loadByRef($ref)
    {
        $query = new Core_Model_Query();
        $query->filter->addCondition(self::QUERY_REF, $ref);
        $list = self::loadList($query);
        if (count($list) == 0) {
            throw new Core_Exception_NotFound("Role not found matching ref '$ref'");
        }
        return current($list);
    }

    /**
     * @return string
     */
    public function getRef()
    {
        return $this->ref;
    }

    /**
     * @param string $ref
     */
    public function setRef($ref)
    {
        $this->ref = $ref;
    }

    /**
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @param string $name
     */
    public function setName($name)
    {
        $this->name = $name;
    }
}
