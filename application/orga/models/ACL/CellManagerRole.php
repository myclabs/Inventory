<?php

namespace Orga\Model\ACL;

use Doctrine\ORM\EntityManager;
use MyCLabs\ACL\Model\Actions;
use MyCLabs\ACL\Model\Authorization;
use MyCLabs\ACL\Model\Resource;
use Orga\Model\ACL\Action\CellAction;
use User\Domain\ACL\Action;

/**
 * Cell manager.
 */
class CellManagerRole extends AbstractCellRole
{
    public function createAuthorizations(EntityManager $entityManager)
    {
        $actions = new Actions([
            Actions::VIEW,
        ]);

        return [
            Authorization::create($this, $actions, Resource::fromEntity($this->cell)),
        ];
    }

    public function buildAuthorizations()
    {
        $this->authorizations->clear();

        // Voir l'organisation
        OrganizationAuthorization::create($this, Action::VIEW(), $this->cell->getOrganization());

        $authorizations = CellAuthorization::createMany($this, $this->cell, [
            Action::VIEW(),
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
        return __('Orga', 'role', 'cellManager');
    }
}
