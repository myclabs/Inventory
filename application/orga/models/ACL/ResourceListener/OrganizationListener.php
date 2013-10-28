<?php

namespace Orga\Model\ACL\ResourceListener;

use Orga\Model\ACL\OrganizationAuthorization;
use Orga_Model_Organization;

/**
 * Listens to events on the Organization resource.
 */
class OrganizationListener
{
    public function preRemove(Orga_Model_Organization $organization)
    {
        // Cascade remove sur les autorisations portant sur cette ressource
        foreach (OrganizationAuthorization::loadByResource($organization) as $authorization) {
            $authorization->delete();
        }
    }
}
