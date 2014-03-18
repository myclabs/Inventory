<?php

namespace Orga\Model\ACL;

use MyCLabs\ACL\ACLManager;
use MyCLabs\ACL\Model\Actions;
use Orga\Model\ACL\Action\CellAction;

/**
 * Cell contributor.
 */
class CellContributorRole extends AbstractCellRole
{
    public function createAuthorizations(ACLManager $aclManager)
    {
        // Voir la cellule et la saisie
        $aclManager->allow(
            $this,
            new Actions([ Actions::VIEW ]),
            $this->cell
        );

        // Modifier la saisie
        $aclManager->allow(
            $this,
            new Actions([ Actions::VIEW ]),
            $this->cell->getAFInputSetPrimary()
        );
    }

    public function buildAuthorizations()
    {
        $this->authorizations->clear();

        // Voir l'organisation
        OrganizationAuthorization::create($this, Actions::VIEW, $this->cell->getOrganization());

        $authorizations = CellAuthorization::createMany($this, $this->cell, [
            Actions::VIEW,
            CellAction::COMMENT(),
            CellAction::INPUT(),
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
        return __('Orga', 'role', 'cellContributor');
    }
}
