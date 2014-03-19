<?php

namespace Orga\Model\ACL;

use MyCLabs\ACL\ACLManager;
use MyCLabs\ACL\Model\Actions;
use Orga\Model\ACL\Action\CellAction;

/**
 * Cell observer.
 */
class CellObserverRole extends AbstractCellRole
{
    public function createAuthorizations(ACLManager $aclManager)
    {
        // Voir la cellule
        $aclManager->allow(
            $this,
            new Actions([ Actions::VIEW ]),
            $this->cell
        );

        // Il peut voir la saisie en cascade
    }

    public function buildAuthorizations()
    {
        $this->authorizations->clear();

        // Voir l'organisation
        OrganizationAuthorization::create($this, Actions::VIEW, $this->cell->getOrganization());

        $authorizations = CellAuthorization::createMany($this, $this->cell, [
            Actions::VIEW,
            CellAction::COMMENT(),
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
        return __('Orga', 'role', 'cellObserver');
    }
}
