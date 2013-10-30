<?php

namespace Orga\Model\ACL\Role;

use Orga\Model\ACL\CellAuthorization;
use Orga\Model\ACL\OrganizationAuthorization;
use Orga_Action_Cell;
use Orga_Model_Cell;
use User\Domain\ACL\Action;
use User\Domain\ACL\Role;
use User\Domain\User;

/**
 * Cell administrator.
 */
class CellAdminRole extends AbstractCellRole
{
    public function __construct(User $user, Orga_Model_Cell $cell)
    {
        $cell->addAdminRole($this);

        parent::__construct($user, $cell);
    }

    public function buildAuthorizations()
    {
        $this->authorizations->clear();

        // Voir l'organisation
        OrganizationAuthorization::create($this, $this->user, Action::VIEW(), $this->cell->getOrganization());

        CellAuthorization::create($this, $this->user, Action::VIEW(), $this->cell);
        CellAuthorization::create($this, $this->user, Action::EDIT(), $this->cell);
        CellAuthorization::create($this, $this->user, Action::ALLOW(), $this->cell);
        CellAuthorization::create($this, $this->user, Orga_Action_Cell::COMMENT(), $this->cell);
        CellAuthorization::create($this, $this->user, Orga_Action_Cell::INPUT(), $this->cell);

        // Cellules filles
        foreach ($this->cell->getChildCells() as $childCell) {
            CellAuthorization::create($this, $this->user, Action::VIEW(), $childCell);
            CellAuthorization::create($this, $this->user, Action::EDIT(), $childCell);
            CellAuthorization::create($this, $this->user, Action::ALLOW(), $childCell);
            CellAuthorization::create($this, $this->user, Orga_Action_Cell::COMMENT(), $childCell);
            CellAuthorization::create($this, $this->user, Orga_Action_Cell::INPUT(), $childCell);
        }
    }
}
