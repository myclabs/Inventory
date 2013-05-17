<?php
/**
 * @package Social
 */

use Doctrine\Common\Collections\Collection;
use Doctrine\Common\Collections\ArrayCollection;

/**
 * @author  joseph.rouffet
 * @author  matthieu.napoli
 * @package Social
 */
class Social_Model_UserGroup extends Core_Model_Entity
{

    const QUERY_LABEL = 'label';

    /**
     * @var int
     */
    protected $id;

    /**
     * @var string
     */
    protected $ref;

    /**
     * @var string
     */
    protected $label;

    /**
     * @var Collection|User_Model_User[]
     */
    protected $users;


    /**
     * @param string $ref
     * @param string $label
     */
    public function __construct($ref, $label)
    {
        $this->users = new ArrayCollection();
        $this->ref = (string) $ref;
        $this->label = (string) $label;
    }

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @return string
     */
    public function getRef()
    {
        return $this->ref;
    }

    /**
     * @return string
     */
    public function getLabel()
    {
        return $this->label;
    }

    /**
     * @return User_Model_User[]
     */
    public function getUsers()
    {
        return $this->users;
    }

    /**
     * Ajoute un utilisateur au groupe
     * @param User_Model_User $user
     */
    public function addUser(User_Model_User $user)
    {
        if (! $this->hasUser($user)) {
            $this->users->add($user);
        }
    }

    /**
     * Supprime un utilisateur du groupe
     * @param User_Model_User $user
     */
    public function removeUser(User_Model_User $user)
    {
        if ($this->hasUser($user)) {
            $this->users->removeElement($user);
        }
    }

    /**
     * @param User_Model_User $user
     * @return boolean
     */
    public function hasUser(User_Model_User $user)
    {
        return $this->users->contains($user);
    }

}
