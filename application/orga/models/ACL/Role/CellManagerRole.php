<?php

namespace Orga\Model\ACL\Role;

use Orga\Model\ACL\OrganizationAuthorization;
use Orga_Model_Cell;
use User\Domain\ACL\Action;
use User\Domain\ACL\Role;
use User\Domain\User;

/**
 * Cell manager.
 */
class CellManagerRole extends Role
{
    /**
     * @var Orga_Model_Cell
     */
    protected $cell;

    public function __construct(User $user, Orga_Model_Cell $cell)
    {
        $this->user = $user;
        $this->cell = $cell;
    }

    public function getAuthorizations()
    {
        $authorizations = [];

        $authorizations[] = new OrganizationAuthorization($this->user, Action::VIEW(), $this->cell->getOrganization());

        // TODO

        return $authorizations;
    }
}
