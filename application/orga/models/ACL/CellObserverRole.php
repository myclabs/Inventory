<?php

namespace Orga\Model\ACL;

use MyCLabs\ACL\ACLManager;
use User\Domain\ACL\Actions;

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
            new Actions([
                Actions::VIEW, // voir la cellule
                Actions::ANALYZE, // analyser les données
            ]),
            $this->cell
        );

        // Il peut voir la saisie en cascade
    }

    public static function getLabel()
    {
        return __('Orga', 'role', 'cellObserver');
    }
}
