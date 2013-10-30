<?php

namespace Orga\Model\ACL\Role;

use Orga\Model\ACL\CellAuthorization;
use Orga_Action_Cell;
use Orga_Model_Cell;
use User\Domain\ACL\Action;
use User\Domain\ACL\Role;

/**
 * Cell administrator.
 */
class CellAdminRole extends AbstractCellRole
{
    protected function getCellAuthorizations(Orga_Model_Cell $cell)
    {
        return [
            new CellAuthorization($this->user, Action::VIEW(), $cell),
            new CellAuthorization($this->user, Action::EDIT(), $cell),
            new CellAuthorization($this->user, Action::ALLOW(), $cell),
            new CellAuthorization($this->user, Orga_Action_Cell::COMMENT(), $cell),
            new CellAuthorization($this->user, Orga_Action_Cell::INPUT(), $cell),
        ];
    }
}
