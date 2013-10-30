<?php

namespace Orga\Model\ACL;

use Orga_Model_Cell;
use User\Domain\ACL\Action;
use User\Domain\ACL\Authorization\Authorization;
use User\Domain\User;

/**
 * Autorisation d'accès à une cellule.
 *
 * @author matthieu.napoli
 */
class CellAuthorization extends Authorization
{
    /**
     * @var Orga_Model_Cell
     */
    protected $resource;

    /**
     * @param User            $user
     * @param Action          $action
     * @param Orga_Model_Cell $resource
     */
    public function __construct(User $user, Action $action, Orga_Model_Cell $resource)
    {
        $this->user = $user;
        $this->setAction($action);
        $this->resource = $resource;

        $this->resource->addToACL($this);
    }

    /**
     * @return Orga_Model_Cell
     */
    public function getResource()
    {
        return $this->resource;
    }
}
