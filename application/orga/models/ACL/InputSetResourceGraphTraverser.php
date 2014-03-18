<?php

namespace Orga\Model\ACL;

use AF\Domain\InputSet\PrimaryInputSet;
use Core_Exception_NotFound;
use MyCLabs\ACL\Model\ResourceInterface;
use MyCLabs\ACL\ResourceGraph\ResourceGraphTraverser;
use Orga_Model_Cell;

class InputSetResourceGraphTraverser implements ResourceGraphTraverser
{
    /**
     * @var CellResourceGraphTraverser
     */
    private $cellResourceGraphTraverser;

    public function __construct(CellResourceGraphTraverser $cellResourceGraphTraverser)
    {
        $this->cellResourceGraphTraverser = $cellResourceGraphTraverser;
    }

    public function getAllSubResources(ResourceInterface $resource)
    {
        if (! $resource instanceof PrimaryInputSet) {
            throw new \RuntimeException;
        }

        return [];
    }

    public function getAllParentResources(ResourceInterface $resource)
    {
        if (! $resource instanceof PrimaryInputSet) {
            throw new \RuntimeException;
        }

        try {
            // Cellule
            $cell = Orga_Model_Cell::loadByAFInputSetPrimary($resource);
        } catch (Core_Exception_NotFound $e) {
            return [];
        }

        // Récupère toutes les resources parentes de la cellule
        $parents = $this->cellResourceGraphTraverser->getAllParentResources($cell);

        $parents[] = $cell;

        return $parents;
    }
}
