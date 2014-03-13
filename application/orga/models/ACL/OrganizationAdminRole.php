<?php

namespace Orga\Model\ACL;

use Doctrine\ORM\EntityManager;
use MyCLabs\ACL\Model\Actions;
use MyCLabs\ACL\Model\Authorization;
use MyCLabs\ACL\Model\Resource;
use MyCLabs\ACL\Model\Role;
use Orga\Model\ACL\Action\CellAction;
use Orga_Model_Organization;
use User\Domain\User;

/**
 * Organization administrator.
 */
class OrganizationAdminRole extends Role
{
    /**
     * @var Orga_Model_Organization
     */
    protected $organization;

    public function __construct(User $user, Orga_Model_Organization $organization)
    {
        $this->organization = $organization;
        $organization->addAdminRole($this);

        parent::__construct($user);
    }

    /**
     * @param EntityManager $entityManager
     * @return Authorization[]
     */
    public function createAuthorizations(EntityManager $entityManager)
    {
        return [
            Authorization::create($this, Actions::all(), Resource::fromEntity($this->organization)),
        ];
    }

    public function buildAuthorizations()
    {
        $this->authorizations->clear();

        OrganizationAuthorization::createMany($this, $this->organization, [
            Action::VIEW(),
            Action::EDIT(),
            Action::DELETE(),
            Action::ALLOW(),
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
