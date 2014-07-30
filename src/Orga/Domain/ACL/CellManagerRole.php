<?php

namespace Orga\Domain\ACL;

use MyCLabs\ACL\ACL;
use Orga\Domain\ACL\AbstractCellRole;
use User\Domain\ACL\Actions;

/**
 * Cell manager.
 */
class CellManagerRole extends AbstractCellRole
{
    public function createAuthorizations(ACL $acl)
    {
        $acl->allow(
            $this,
            new Actions([
                Actions::TRAVERSE, // naviguer dans le compte
            ]),
            $this->cell->getWorkspace()->getAccount(),
            false // pas de cascade sinon on pourrait naviguer dans toutes les organisations
        );

        $acl->allow(
            $this,
            new Actions([
                Actions::TRAVERSE, // naviguer dans l'organisation
            ]),
            $this->cell->getWorkspace(),
            false // pas de cascade sinon on pourrait naviguer dans toutes les cellules
        );

        $acl->allow(
            $this,
            new Actions([
                Actions::VIEW, // voir la cellule
                Actions::ALLOW, // donner des droits d'accès
                Actions::INPUT, // saisir
                Actions::ANALYZE, // analyser les données
                Actions::MANAGE_INVENTORY, // gérer les inventaires
            ]),
            $this->cell
        );
    }

    public static function getLabel()
    {
        return __('Orga', 'role', 'cellManager');
    }
}
