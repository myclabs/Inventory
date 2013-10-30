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

        // Admin sur toutes les cellules
        foreach ($this->organization->getGranularities() as $granularity) {
            foreach ($granularity->getCells() as $cell) {
                CellAuthorization::create($this, $this->user, Action::VIEW(), $cell);
                CellAuthorization::create($this, $this->user, Action::EDIT(), $cell);
                CellAuthorization::create($this, $this->user, Action::ALLOW(), $cell);
                CellAuthorization::create($this, $this->user, Orga_Action_Cell::COMMENT(), $cell);
                CellAuthorization::create($this, $this->user, Orga_Action_Cell::INPUT(), $cell);
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
}
