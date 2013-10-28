<?php

namespace Orga\Model\ACL\Role;

use Orga\Model\ACL\CellAuthorization;
use Orga_Action_Cell;
use Orga_Model_Cell;
use User\Domain\ACL\Action;
use User\Domain\ACL\Role;

/**
 * Cell observer.
 */
class CellObserverRole extends AbstractCellRole
{
    protected function getCellAuthorizations(Orga_Model_Cell $cell)
    {
        return [
            new CellAuthorization($this->user, Action::VIEW(), $this->cell),
            new CellAuthorization($this->user, Orga_Action_Cell::COMMENT(), $this->cell),
        ];
    }
}
