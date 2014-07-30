<?php

namespace Orga\Domain\ACL;

use Account\Domain\Account;
use MyCLabs\ACL\Model\ClassResource;
use MyCLabs\ACL\Model\ResourceInterface;
use MyCLabs\ACL\ResourceGraph\ResourceGraphTraverser;
use Orga\Domain\Workspace;

class WorkspaceResourceGraphTraverser implements ResourceGraphTraverser
{
    public function getAllSubResources(ResourceInterface $resource)
    {
        if (! $resource instanceof Workspace) {
            throw new \RuntimeException;
        }

        $resources = [];
        foreach ($resource->getGranularities() as $granularity) {
            $resources = array_merge($resources, $granularity->getCells()->toArray());
        }

        return $resources;
    }

    public function getAllParentResources(ResourceInterface $resource)
    {
        if (! $resource instanceof Workspace) {
            throw new \RuntimeException;
        }

        // Compte
        $parents[] = $resource->getAccount();

        // Tous les comptes
        $parents[] = new ClassResource(Account::class);

        return $parents;
    }
}
