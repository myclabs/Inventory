<?php

namespace Orga\Model\ACL;

use Orga_Model_Organization;
use User\Domain\ACL\Action;
use User\Domain\ACL\Authorization\Authorization;
use User\Domain\User;

/**
 * Autorisation d'accès à une organisation.
 *
 * @author matthieu.napoli
 */
class OrganizationAuthorization extends Authorization
{
    /**
     * @var Orga_Model_Organization
     */
    protected $resource;

    /**
     * @param User                    $user
     * @param Action                  $action
     * @param Orga_Model_Organization $resource
     */
    public function __construct(User $user, Action $action, Orga_Model_Organization $resource)
    {
        $this->user = $user;
        $this->setAction($action);
        $this->resource = $resource;

        $this->resource->addToACL($this);
    }

    /**
     * @return Orga_Model_Organization
     */
    public function getResource()
    {
        return $this->resource;
    }
}
