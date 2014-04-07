<?php

namespace Orga\Model\ACL;

use MyCLabs\ACL\ACL;
use User\Domain\ACL\Actions;

/**
 * Cell contributor.
 */
class CellContributorRole extends AbstractCellRole
{
    public function createAuthorizations(ACL $acl)
    {
        $acl->allow(
            $this,
            new Actions([
                Actions::TRAVERSE, // naviguer dans le compte
            ]),
            $this->cell->getOrganization()->getAccount(),
            false // pas de cascade sinon on pourrait naviguer dans toutes les organisations
        );

        $acl->allow(
            $this,
            new Actions([
                Actions::TRAVERSE, // naviguer dans l'organisation
            ]),
            $this->cell->getOrganization(),
            false // pas de cascade sinon on pourrait naviguer dans toutes les cellules
        );

        $acl->allow(
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
