<?php
/**
 * @author matthieu.napoli
 */

namespace User\Event;

use User_Model_User;

/**
 * Interface implementation for an event that was triggered by a user
 */
trait UserEventTrait
{
    /**
     * @var User_Model_User|null
     */
    protected $user;

    /**
     * @param User_Model_User $user
     * @return mixed
     */
    public function setUser(User_Model_User $user)
    {
        $this->user = $user;
    }

    /**
     * @return User_Model_User|null
     */
    public function getUser()
    {
        return $this->user;
    }
}
