<?php

namespace User\Domain\ACL\Role;

use Orga\Model\ACL\OrganizationAuthorization;
use Orga_Model_Organization;
use User\Domain\ACL\Action;
use User\Domain\ACL\Authorization\NamedResourceAuthorization;
use User\Domain\ACL\Authorization\UserAuthorization;
use User\Domain\ACL\Resource\NamedResource;
use User\Domain\ACL\Role;
use User\Domain\User;

/**
 * Application administrator.
 */
class AdminRole extends Role
{
    public function buildAuthorizations()
    {
        $this->authorizations->clear();

        // Admin can edit the repository
        $repository = NamedResource::loadByName('repository');
        NamedResourceAuthorization::create($this, $this->user, Action::EDIT(), $repository);

        // Admin can create, view, edit, delete, undelete, allow everyone
        $allUsersResource = NamedResource::loadByName(User::class);
        NamedResourceAuthorization::create($this, $this->user, Action::CREATE(), $allUsersResource);
        $view = NamedResourceAuthorization::create($this, $this->user, Action::VIEW(), $allUsersResource);
        $edit = NamedResourceAuthorization::create($this, $this->user, Action::EDIT(), $allUsersResource);
        $delete = NamedResourceAuthorization::create($this, $this->user, Action::DELETE(), $allUsersResource);
        $undelete = NamedResourceAuthorization::create($this, $this->user, Action::UNDELETE(), $allUsersResource);
        $allow = NamedResourceAuthorization::create($this, $this->user, Action::ALLOW(), $allUsersResource);

        // Inheritance
        $allUsers = User::loadList();
        foreach ($allUsers as $target) {
            /** @var User $target */
            UserAuthorization::createChildAuthorization($view, $target);
            UserAuthorization::createChildAuthorization($edit, $target);
            UserAuthorization::createChildAuthorization($delete, $target);
            UserAuthorization::createChildAuthorization($undelete, $target);
            UserAuthorization::createChildAuthorization($allow, $target);
        }

        // Admin can create, view, edit, delete, allow on all organizations
        $organizationsResource = NamedResource::loadByName(Orga_Model_Organization::class);
        NamedResourceAuthorization::create($this, $this->user, Action::CREATE(), $organizationsResource);
        $view = NamedResourceAuthorization::create($this, $this->user, Action::VIEW(), $organizationsResource);
        $edit = NamedResourceAuthorization::create($this, $this->user, Action::EDIT(), $organizationsResource);
        $delete = NamedResourceAuthorization::create($this, $this->user, Action::DELETE(), $organizationsResource);
        $undelete = NamedResourceAuthorization::create($this, $this->user, Action::UNDELETE(), $organizationsResource);
        $allow = NamedResourceAuthorization::create($this, $this->user, Action::ALLOW(), $organizationsResource);

        // Inheritance
        $organizations = Orga_Model_Organization::loadList();
        foreach ($organizations as $organization) {
            /** @var Orga_Model_Organization $organization */
            OrganizationAuthorization::createChildAuthorization($view, $organization);
            OrganizationAuthorization::createChildAuthorization($edit, $organization);
            OrganizationAuthorization::createChildAuthorization($delete, $organization);
            OrganizationAuthorization::createChildAuthorization($undelete, $organization);
            OrganizationAuthorization::createChildAuthorization($allow, $organization);
        }
    }

    public static function getLabel()
    {
        return __('User', 'role', 'roleAdmin');
    }
}
