<?php

namespace Orga\Model\ACL\Role;

use Orga_Model_Cell;
use User\Domain\ACL\Role\Role;
use User\Domain\User;

/**
 * Cell manager.
 */
class CellManagerRole extends AbstractCellRole
{
    public function __construct(User $user, Orga_Model_Cell $cell)
    {
        $cell->addManagerRole($this);

        parent::__construct($user, $cell);
    }

    public function buildAuthorizations()
    {
        $this->authorizations->clear();

        // TODO
    }

    public static function getLabel()
    {
        return __('Orga', 'role', 'cellManager');
    }
}
