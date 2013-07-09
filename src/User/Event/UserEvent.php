<?php
/**
 * @author matthieu.napoli
 */

namespace User\Event;

use User_Model_User;

/**
 * Interface for an event that was triggered by a user
 */
interface UserEvent
{
    /**
     * @param User_Model_User $user
     * @return mixed
     */
    public function setUser(User_Model_User $user);

    /**
     * @return User_Model_User
     */
    public function getUser();
}
