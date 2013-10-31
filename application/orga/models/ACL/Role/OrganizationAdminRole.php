<?php

namespace Orga\Model\ACL\Role;

use Orga\Model\ACL\CellAuthorization;
use Orga\Model\ACL\OrganizationAuthorization;
use Orga_Action_Cell;
use Orga_Model_Organization;
use User\Domain\ACL\Action;
use User\Domain\ACL\Role;
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

        OrganizationAuthorization::create($this, $this->user, Action::VIEW(), $this->organization);
        OrganizationAuthorization::create($this, $this->user, Action::EDIT(), $this->organization);
        OrganizationAuthorization::create($this, $this->user, Action::DELETE(), $this->organization);

        // Admin sur la cellule globale
        $globalCell = $this->organization->getGranularityByRef('global')->getCellByMembers([]);

        $view = CellAuthorization::create($this, $this->user, Action::VIEW(), $globalCell);
        $edit = CellAuthorization::create($this, $this->user, Action::EDIT(), $globalCell);
        $allow = CellAuthorization::create($this, $this->user, Action::ALLOW(), $globalCell);
        $comment = CellAuthorization::create($this, $this->user, Orga_Action_Cell::COMMENT(), $globalCell);
        $input = CellAuthorization::create($this, $this->user, Orga_Action_Cell::INPUT(), $globalCell);

        // Cellules filles
        foreach ($globalCell->getChildCells() as $childCell) {
            CellAuthorization::createChildAuthorization($view, $childCell);
            CellAuthorization::createChildAuthorization($edit, $childCell);
            CellAuthorization::createChildAuthorization($allow, $childCell);
            CellAuthorization::createChildAuthorization($comment, $childCell);
            CellAuthorization::createChildAuthorization($input, $childCell);
        }
    }

    /**
     * @return Orga_Model_Organization
     */
    public function getOrganization()
    {
        return $this->organization;
    }

    public function getLabel()
    {
        return __('Orga', 'role', 'organizationAdministrator');
    }
}
