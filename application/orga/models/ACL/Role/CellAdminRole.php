<?php

namespace Orga\Model\ACL\Role;

use Orga\Model\ACL\CellAuthorization;
use Orga\Model\ACL\OrganizationAuthorization;
use Orga_Action_Cell;
use Orga_Model_Cell;
use Orga_Model_Organization;
use User\Domain\ACL\Action;
use User\Domain\ACL\Role;
use User\Domain\User;

/**
 * Cell administrator.
 */
class CellAdminRole extends Role
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

        $authorizations[] = new CellAuthorization($this->user, Action::VIEW(), $this->cell);
        $authorizations[] = new CellAuthorization($this->user, Action::EDIT(), $this->cell);
        $authorizations[] = new CellAuthorization($this->user, Action::ALLOW(), $this->cell);
        $authorizations[] = new CellAuthorization($this->user, Orga_Action_Cell::COMMENT(), $this->cell);
        $authorizations[] = new CellAuthorization($this->user, Orga_Action_Cell::INPUT(), $this->cell);

        return $authorizations;
    }
}
