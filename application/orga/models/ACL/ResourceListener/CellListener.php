<?php

namespace Orga\Model\ACL\ResourceListener;

use Orga\Model\ACL\CellAuthorization;
use Orga_Model_Cell;

/**
 * Listens to events on the Cell resource.
 */
class CellListener
{
    public function preRemove(Orga_Model_Cell $cell)
    {
        // Cascade remove sur les autorisations portant sur cette ressource
        foreach (CellAuthorization::loadByResource($cell) as $authorization) {
            $authorization->delete();
        }
    }
}
