<?php

namespace User\Domain\Event;

use User\Domain\User;

/**
 * Interface for an event that was triggered by a user.
 *
 * @author matthieu.napoli
 */
interface UserEvent
{
    /**
     * @param \User\Domain\User $user
     * @return mixed
     */
    public function setUser(User $user);

    /**
     * @return User
     */
    public function getUser();
}
