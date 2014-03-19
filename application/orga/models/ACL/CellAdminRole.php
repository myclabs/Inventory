<?php

namespace Orga\Model\ACL;

use MyCLabs\ACL\ACLManager;
use User\Domain\ACL\Actions;

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
                Actions::VIEW, // voir la cellule
                Actions::EDIT, // modifier la structure organisationelle sous cette cellule
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
        return __('Orga', 'role', 'cellAdministrator');
    }
}
