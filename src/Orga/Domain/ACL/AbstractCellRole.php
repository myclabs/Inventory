<?php

namespace Orga\Domain\ACL;

use MyCLabs\ACL\Model\Role;
use Orga\Domain\Cell;
use User\Domain\User;

/**
 * Classe abstraite pour factoriser du code.
 */
abstract class AbstractCellRole extends Role
{
    /**
     * @var Cell
     */
    protected $cell;

    public function __construct(User $user, Cell $cell)
    {
        $this->cell = $cell;

        $cell->addRole($this);

        parent::__construct($user);
    }

    /**
     * @return \Orga\Domain\Cell
     */
    public function getCell()
    {
        return $this->cell;
    }

    public static function getLabel()
    {
        return '';
    }
}
