<?php

namespace Orga\Model\ACL;

use Account\Domain\Account;
use MyCLabs\ACL\Model\ClassResource;
use MyCLabs\ACL\Model\ResourceInterface;
use MyCLabs\ACL\ResourceGraph\ResourceGraphTraverser;
use Orga_Model_Organization;

class OrganizationResourceGraphTraverser implements ResourceGraphTraverser
{
    public function getAllSubResources(ResourceInterface $resource)
    {
        if (! $resource instanceof Orga_Model_Organization) {
            throw new \RuntimeException;
        }

        $globalCell = $resource->getGlobalCell();

        $resources = $globalCell->getChildCells();
        $resources[] = $globalCell;

        return $resources;
    }

    public function getAllParentResources(ResourceInterface $resource)
    {
        if (! $resource instanceof Orga_Model_Organization) {
            throw new \RuntimeException;
        }

        // Compte
        $parents[] = $resource->getAccount();

        // Tous les comptes
        $parents[] = new ClassResource(Account::class);

        return $parents;
    }
}
