<?php

namespace User\Domain\ACL\Authorization;

use Core_Model_Entity;
use User\Domain\ACL\Action;
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
     * On ne peut pas utiliser le type Action, les Criterias ne filtrent pas sur des VO (comparaison de type ===).
     * @var string
     */
    protected $actionId;

    /**
     * @return User
     */
    public function getUser()
    {
        return $this->user;
    }

    /**
     * @param Action $action
     */
    protected function setAction(Action $action)
    {
        $this->actionId = $action->exportToString();
    }

    /**
     * @return Action
     */
    public function getAction()
    {
        return Action::importFromString($this->actionId);
    }

    /**
     * @return Action
     */
    public function getActionId()
    {
        return $this->actionId;
    }

    /**
     * @return Resource
     */
    abstract public function getResource();
}
