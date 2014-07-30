<?php

namespace Orga\Domain\ACL;

use Account\Domain\Account;
use MyCLabs\ACL\Model\ClassResource;
use MyCLabs\ACL\Model\ResourceInterface;
use MyCLabs\ACL\ResourceGraph\ResourceGraphTraverser;
use Orga\Domain\Cell;

class CellResourceGraphTraverser implements ResourceGraphTraverser
{
    public function getAllSubResources(ResourceInterface $resource)
    {
        if (! $resource instanceof Cell) {
            throw new \RuntimeException;
        }

        return $resource->getChildCells();
    }

    public function getAllParentResources(ResourceInterface $resource)
    {
        if (! $resource instanceof Cell) {
            throw new \RuntimeException;
        }

        // Cellules parent
        $parents = $resource->getParentCells();

        // Organisation
        $workspace = $resource->getWorkspace();
        $parents[] = $workspace;

        // Compte
        $parents[] = $workspace->getAccount();

        // Tous les comptes
        $parents[] = new ClassResource(Account::class);

        return $parents;
    }
}
