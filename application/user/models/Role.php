<?php
/**
 * @author     matthieu.napoli
 * @package    User
 * @subpackage Model
 */

use Doctrine\Common\Collections\Collection;
use Doctrine\Common\Collections\ArrayCollection;

/**
 * Classe Role
 * @package    User
 * @subpackage Model
 */
class User_Model_Role extends User_Model_SecurityIdentity
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
     * @var User_Model_User[]|Collection
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
     * @return User_Model_User[]
     */
    public function getUsers()
    {
        return $this->users;
    }

    /**
     * @param User_Model_User $user
     * @return bool
     */
    public function hasUser(User_Model_User $user)
    {
        return $this->users->contains($user);
    }

    /**
     * @param User_Model_User $user
     */
    public function addUser(User_Model_User $user)
    {
        if (!$this->hasUser($user)) {
            $this->users->add($user);
            $user->addRole($this);
        }
    }

    /**
     * @param User_Model_User $user
     */
    public function removeUser(User_Model_User $user)
    {
        if ($this->hasUser($user)) {
            $this->users->removeElement($user);
            $user->removeRole($this);
        }
    }

    /**
     * Charge un rôle par son ref
     * @param string $ref
     * @return User_Model_Role
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
