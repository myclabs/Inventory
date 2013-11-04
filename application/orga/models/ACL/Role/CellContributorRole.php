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
 * Cell contributor.
 */
class CellContributorRole extends AbstractCellRole
{
    public function __construct(User $user, Orga_Model_Cell $cell)
    {
        $cell->addContributorRole($this);

        parent::__construct($user, $cell);
    }

    public function buildAuthorizations()
    {
        $this->authorizations->clear();

        // Voir l'organisation
        OrganizationAuthorization::create($this, $this->user, Action::VIEW(), $this->cell->getOrganization());

        $view = CellAuthorization::create($this, $this->user, Action::VIEW(), $this->cell);
        $comment = CellAuthorization::create($this, $this->user, Orga_Action_Cell::COMMENT(), $this->cell);
        $input = CellAuthorization::create($this, $this->user, Orga_Action_Cell::INPUT(), $this->cell);

        // Cellules filles
        foreach ($this->cell->getChildCells() as $childCell) {
            CellAuthorization::createChildAuthorization($view, $childCell);
            CellAuthorization::createChildAuthorization($comment, $childCell);
            CellAuthorization::createChildAuthorization($input, $childCell);
        }
    }

    public static function getLabel()
    {
        return __('Orga', 'role', 'cellContributor');
    }
}
