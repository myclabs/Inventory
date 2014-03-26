<?php

namespace Orga\Model\ACL;

use Account\Domain\Account;
use MyCLabs\ACL\Model\ClassResource;
use MyCLabs\ACL\Model\ResourceInterface;
use MyCLabs\ACL\ResourceGraph\ResourceGraphTraverser;
use Orga_Model_Cell;

class CellResourceGraphTraverser implements ResourceGraphTraverser
{
    public function getAllSubResources(ResourceInterface $resource)
    {
        if (! $resource instanceof Orga_Model_Cell) {
            throw new \RuntimeException;
        }

        return $resource->getChildCells();
    }

    public function getAllParentResources(ResourceInterface $resource)
    {
        if (! $resource instanceof Orga_Model_Cell) {
            throw new \RuntimeException;
        }

        // Cellules parent
        $parents = $resource->getParentCells();

        // Organisation
        $organization = $resource->getOrganization();
        $parents[] = $organization;

        // Compte
        $parents[] = $organization->getAccount();

        // Tous les comptes
        $parents[] = new ClassResource(Account::class);

        return $parents;
    }
}