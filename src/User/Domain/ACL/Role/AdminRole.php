<?php

namespace User\Domain\ACL\Role;

use Orga\Model\ACL\Action\OrganizationAction;
use Orga\Model\ACL\OrganizationAuthorization;
use Orga_Model_Organization;
use User\Domain\ACL\Action;
use User\Domain\ACL\Authorization\NamedResourceAuthorization;
use User\Domain\ACL\Authorization\UserAuthorization;
use User\Domain\ACL\Resource\NamedResource;
use User\Domain\ACL\Role\Role;
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
        NamedResourceAuthorization::create($this, Action::EDIT(), $repository);

        // Admin can create, view, edit, delete, undelete, allow everyone
        $allUsersResource = NamedResource::loadByName(User::class);
        NamedResourceAuthorization::create($this, Action::CREATE(), $allUsersResource);
        $userAuthorizations = NamedResourceAuthorization::createMany($this, $allUsersResource, [
            Action::VIEW(),
            Action::EDIT(),
            Action::DELETE(),
            Action::UNDELETE(),
            Action::ALLOW(),
        ]);

        // Inheritance
        $allUsers = User::loadList();
        foreach ($allUsers as $target) {
            /** @var User $target */
            foreach ($userAuthorizations as $userAuthorization) {
                UserAuthorization::createChildAuthorization($userAuthorization, $target);
            }
        }

        // Admin can create, view, edit, delete, allow on all organizations
        $organizationsResource = NamedResource::loadByName(Orga_Model_Organization::class);
        NamedResourceAuthorization::create($this, Action::CREATE(), $organizationsResource);
        $organizationAuthorizations = NamedResourceAuthorization::createMany($this, $organizationsResource, [
            Action::VIEW(),
            Action::EDIT(),
            Action::DELETE(),
            Action::UNDELETE(),
            Action::ALLOW(),
            OrganizationAction::EDIT_GRANULARITY_REPORTS(),
        ]);

        // Inheritance
        $organizations = Orga_Model_Organization::loadList();
        foreach ($organizations as $organization) {
            /** @var Orga_Model_Organization $organization */
            foreach ($organizationAuthorizations as $organizationAuthorization) {
                OrganizationAuthorization::createChildAuthorization($organizationAuthorization, $organization);
            }
        }
    }

    public static function getLabel()
    {
        return __('User', 'role', 'roleAdmin');
    }
}
