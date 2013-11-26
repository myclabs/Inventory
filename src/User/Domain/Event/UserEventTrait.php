<?php

namespace User\Domain\Event;

use User\Domain\User;

/**
 * Interface implementation for an event that was triggered by a user.
 *
 * @author matthieu.napoli
 */
trait UserEventTrait
{
    /**
     * @var User|null
     */
    protected $user;

    /**
     * @param User $user
     * @return mixed
     */
    public function setUser(User $user)
    {
        $this->user = $user;
    }

    /**
     * @return User|null
     */
    public function getUser()
    {
        return $this->user;
    }
}
