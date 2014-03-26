<?php

namespace Orga\Model\ACL;

use MyCLabs\ACL\ACLManager;
use User\Domain\ACL\Actions;
use MyCLabs\ACL\Model\Role;
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

    public function createAuthorizations(ACLManager $aclManager)
    {
        $aclManager->allow(
            $this,
            new Actions([
                Actions::TRAVERSE, // naviguer dans le compte
            ]),
            $this->organization->getAccount(),
            false // pas de cascade sinon on pourrait naviguer dans toutes les organisations
        );

        $aclManager->allow(
            $this,
            new Actions([
                Actions::TRAVERSE, // naviguer dans l'organisation
                Actions::VIEW, // voir l'organisation, et par extension les cellules
                Actions::EDIT, // modifier l'organisation et les cellules
                Actions::ALLOW, // donner des droits d'accès
                Actions::INPUT, // saisir dans les cellules de l'organisation
                Actions::ANALYZE, // analyser les données dans les cellules de l'organisation
                Actions::MANAGE_INVENTORY, // gérer les inventaires dans les cellules de l'organisation
            ]),
            $this->organization
        );
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
