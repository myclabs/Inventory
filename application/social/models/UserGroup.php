<?php
/**
 * @package Social
 */

use Doctrine\Common\Collections\Collection;
use Doctrine\Common\Collections\ArrayCollection;
use User\Domain\User;

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
     * @var Collection|User[]
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
     * @return User[]
     */
    public function getUsers()
    {
        return $this->users;
    }

    /**
     * Ajoute un utilisateur au groupe
     * @param User $user
     */
    public function addUser(User $user)
    {
        if (! $this->hasUser($user)) {
            $this->users->add($user);
        }
    }

    /**
     * Supprime un utilisateur du groupe
     * @param User $user
     */
    public function removeUser(User $user)
    {
        if ($this->hasUser($user)) {
            $this->users->removeElement($user);
        }
    }

    /**
     * @param User $user
     * @return boolean
     */
    public function hasUser(User $user)
    {
        return $this->users->contains($user);
    }

}
