<?php

namespace Orga\Model\ACL\Role;

use Orga\Model\ACL\Action\CellAction;
use Orga\Model\ACL\Action\OrganizationAction;
use Orga\Model\ACL\CellAuthorization;
use Orga\Model\ACL\OrganizationAuthorization;
use Orga_Model_Organization;
use User\Domain\ACL\Action;
use User\Domain\ACL\Role\Role;
use User\Domain\User;

/**
 * Organization administrator.
 */
class OrganizationAdminRole extends Role
{
    protected $organization;

    public function __construct(User $user, Orga_Model_Organization $organization)
    {
        $this->organization = $organization;
        $organization->addAdminRole($this);

        parent::__construct($user);
    }

    public function buildAuthorizations()
    {
        $this->authorizations->clear();

        OrganizationAuthorization::createMany($this, $this->organization, [
            Action::VIEW(),
            Action::EDIT(),
            Action::DELETE(),
            Action::ALLOW(),
            OrganizationAction::EDIT_GRANULARITY_REPORTS(),
        ]);

        // Admin sur la cellule globale
        $globalCell = $this->organization->getGranularityByRef('global')->getCellByMembers([]);

        $authorizations = CellAuthorization::createMany($this, $globalCell, [
            Action::VIEW(),
            Action::EDIT(),
            Action::ALLOW(),
            CellAction::COMMENT(),
            CellAction::INPUT(),
            CellAction::VIEW_REPORTS(),
        ]);

        // Cellules filles
        foreach ($globalCell->getChildCells() as $childCell) {
            foreach ($authorizations as $authorization) {
                CellAuthorization::createChildAuthorization($authorization, $childCell);
            }
        }
    }

    /**
     * @return Orga_Model_Organization
     */
    public function getOrganization()
    {
        return $this->organization;
    }

    public static function getLabel()
    {
        return __('Orga', 'role', 'organizationAdministrator');
    }
}
