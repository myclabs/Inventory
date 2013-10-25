<?php

namespace User\Domain\ACL;

use Core_Model_Entity;
use User\Domain\ACL\Action\Action;
use User\Domain\User;

/**
 * Autorisation d'accès à une ressource.
 *
 * @author matthieu.napoli
 */
abstract class Authorization extends Core_Model_Entity
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
     * @var Action
     */
    protected $action;

    /**
     * @return User
     */
    public function getUser()
    {
        return $this->user;
    }

    /**
     * @return Action
     */
    public function getAction()
    {
        return $this->action;
    }
}
