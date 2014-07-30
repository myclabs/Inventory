<?php

namespace Orga\Domain\ACL;

use MyCLabs\ACL\ACL;
use User\Domain\ACL\Actions;
use MyCLabs\ACL\Model\Role;
use Orga\Domain\Workspace;
use User\Domain\User;

/**
 * Workspace administrator.
 */
class WorkspaceAdminRole extends Role
{
    /**
     * @var Workspace
     */
    protected $workspace;

    public function __construct(User $user, Workspace $workspace)
    {
        $this->workspace = $workspace;
        $workspace->addAdminRole($this);

        parent::__construct($user);
    }

    public function createAuthorizations(ACL $acl)
    {
        $acl->allow(
            $this,
            new Actions([
                Actions::TRAVERSE, // naviguer dans le compte
            ]),
            $this->workspace->getAccount(),
            false // pas de cascade sinon on pourrait naviguer dans toutes les organisations
        );

        $acl->allow(
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
            $this->workspace
        );
    }

    /**
     * @return \Orga\Domain\Workspace
     */
    public function getWorkspace()
    {
        return $this->workspace;
    }

    public static function getLabel()
    {
        return __('Orga', 'role', 'workspaceAdministrator');
    }
}
