<?php

namespace Orga\Model\ACL;

use MyCLabs\ACL\ACLManager;
use User\Domain\ACL\Actions;

/**
 * Cell contributor.
 */
class CellContributorRole extends AbstractCellRole
{
    public function createAuthorizations(ACLManager $aclManager)
    {
        $aclManager->allow(
            $this,
            new Actions([
                Actions::VIEW, // voir la cellule
                Actions::INPUT, // saisir
            ]),
            $this->cell
        );
    }

    public static function getLabel()
    {
        return __('Orga', 'role', 'cellContributor');
    }
}
