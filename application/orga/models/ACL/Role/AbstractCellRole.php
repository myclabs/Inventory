<?php

namespace Orga\Model\ACL\Role;

use Orga_Model_Cell;
use User\Domain\ACL\Role;
use User\Domain\User;

/**
 * Classe abstraite pour factoriser du code.
 */
abstract class AbstractCellRole extends Role
{
    /**
     * @var Orga_Model_Cell
     */
    protected $cell;

    public function __construct(User $user, Orga_Model_Cell $cell)
    {
        $this->cell = $cell;

        parent::__construct($user);
    }

    /**
     * @return Orga_Model_Cell
     */
    public function getCell()
    {
        return $this->cell;
    }
}
