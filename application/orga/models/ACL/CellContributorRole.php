<?php

namespace Orga\Model\ACL;

use Doctrine\ORM\EntityManager;
use MyCLabs\ACL\Model\Actions;
use MyCLabs\ACL\Model\Authorization;
use MyCLabs\ACL\Model\Resource;
use Orga\Model\ACL\Action\CellAction;

/**
 * Cell contributor.
 */
class CellContributorRole extends AbstractCellRole
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
