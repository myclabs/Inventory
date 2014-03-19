<?php

namespace Orga\Model\ACL;

use MyCLabs\ACL\ACLManager;
use MyCLabs\ACL\Model\Actions;

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
        $input = $this->cell->getAFInputSetPrimary();
        if ($input) {
            $aclManager->allow($this, new Actions([ Actions::EDIT ]), $input);
        }
    }

    public static function getLabel()
    {
        return __('Orga', 'role', 'cellContributor');
    }
}
