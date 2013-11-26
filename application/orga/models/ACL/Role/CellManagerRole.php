<?php

namespace Orga\Model\ACL\Role;

use Orga\Model\ACL\Action\CellAction;
use Orga\Model\ACL\CellAuthorization;
use Orga\Model\ACL\OrganizationAuthorization;
use Orga_Model_Cell;
use User\Domain\ACL\Action;
use User\Domain\ACL\Role\OptimizedRole;
use User\Domain\User;

/**
 * Cell manager.
 */
class CellManagerRole extends AbstractCellRole implements OptimizedRole
{
    public function __construct(User $user, Orga_Model_Cell $cell)
    {
        $cell->addManagerRole($this);

        parent::__construct($user, $cell);
    }

    public function buildAuthorizations()
    {
        $this->authorizations->clear();

        // Voir l'organisation
        OrganizationAuthorization::create($this, Action::VIEW(), $this->cell->getOrganization());

        $authorizations = CellAuthorization::createMany($this, $this->cell, [
            Action::VIEW(),
            CellAction::COMMENT(),
            CellAction::INPUT(),
            CellAction::VIEW_REPORTS(),
        ]);

        // Cellules filles
        foreach ($this->cell->getChildCells() as $childCell) {
            foreach ($authorizations as $authorization) {
                CellAuthorization::createChildAuthorization($authorization, $childCell);
            }
        }
    }

    public function optimizedBuildAuthorizations()
    {
        // Voir l'organisation
        yield OrganizationAuthorization::create($this, Action::VIEW(), $this->cell->getOrganization(), false);

        $cellAuths = [
            CellAuthorization::create($this, Action::VIEW(), $this->cell, false),
            CellAuthorization::create($this, CellAction::COMMENT(), $this->cell, false),
            CellAuthorization::create($this, CellAction::INPUT(), $this->cell, false),
            CellAuthorization::create($this, CellAction::VIEW_REPORTS(), $this->cell, false),
        ];
        foreach ($cellAuths as $authorization) {
            yield $authorization;
        }

        // Cellules filles
        foreach ($this->cell->getChildCells() as $childCell) {
            foreach ($cellAuths as $authorization) {
                yield CellAuthorization::createChildAuthorization($authorization, $childCell, null, false);
            }
        }
    }

    public static function getLabel()
    {
        return __('Orga', 'role', 'cellManager');
    }
}
