<?php

namespace User\Domain\ACL;

use Core_Model_Entity;
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
     * @return Authorization[]
     */
    abstract public function getAuthorizations();

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
}
