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
        $aclManager->allow(
            $this,
            new Actions([
                Actions::TRAVERSE, // naviguer dans le compte
            ]),
            $this->cell->getOrganization()->getAccount(),
            false // pas de cascade sinon on pourrait naviguer dans toutes les organisations
        );

        $aclManager->allow(
            $this,
            new Actions([
                Actions::TRAVERSE, // naviguer dans l'organisation
            ]),
            $this->cell->getOrganization(),
            false // pas de cascade sinon on pourrait naviguer dans toutes les cellules
        );

        $aclManager->allow(
            $this,
            new Actions([
                Actions::VIEW, // voir la cellule (et donc la saisie)
                Actions::ANALYZE, // analyser les données
            ]),
            $this->cell
        );
    }

    public static function getLabel()
    {
        return __('Orga', 'role', 'cellObserver');
    }
}
