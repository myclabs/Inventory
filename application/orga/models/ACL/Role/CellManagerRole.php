<?php

namespace Orga\Model\ACL\Role;

use Orga_Model_Cell;
use User\Domain\ACL\Role;

/**
 * Cell manager.
 */
class CellManagerRole extends AbstractCellRole
{
    public function buildAuthorizations()
    {
        $this->authorizations->clear();

        // TODO
    }
}
