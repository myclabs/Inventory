<?php

namespace Orga\Model\ACL;

use MyCLabs\ACL\ACLManager;
use MyCLabs\ACL\Model\Actions;

/**
 * Cell administrator.
 */
class CellAdminRole extends AbstractCellRole
{
    public function createAuthorizations(ACLManager $aclManager)
    {
        $aclManager->allow(
            $this,
            new Actions([
                Actions::VIEW,
                Actions::EDIT,
                Actions::ALLOW,
            ]),
            $this->cell
        );

        // TODO Modifier la saisie
    }

    public function buildAuthorizations()
    {
        $this->authorizations->clear();

        // Voir l'organisation
        OrganizationAuthorization::create($this, Action::VIEW(), $this->cell->getOrganization());

        $authorizations = CellAuthorization::createMany($this, $this->cell, [
            Action::VIEW(),
            Action::EDIT(),
            Action::ALLOW(),
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

    public static function getLabel()
    {
        return __('Orga', 'role', 'cellAdministrator');
    }
}
