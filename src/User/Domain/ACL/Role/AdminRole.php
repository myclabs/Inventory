<?php

namespace User\Domain\ACL\Role;

use Orga_Model_Organization;
use User\Domain\ACL\Action;
use User\Domain\ACL\Authorization\NamedResourceAuthorization;
use User\Domain\ACL\Authorization\UserAuthorization;
use User\Domain\ACL\Resource\NamedResource;
use User\Domain\User;

/**
 * Application administrator.
 */
class AdminRole extends Role implements OptimizedRole
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

        // Admin can create organizations
        $organizationsResource = NamedResource::loadByName(Orga_Model_Organization::class);
        NamedResourceAuthorization::create($this, Action::CREATE(), $organizationsResource);
    }

    public function optimizedBuildAuthorizations()
    {
        // Admin can edit the repository
        $repository = NamedResource::loadByName('repository');
        yield NamedResourceAuthorization::create($this, Action::EDIT(), $repository, false);

        // Admin can create, view, edit, delete, undelete, allow everyone
        $allUsersResource = NamedResource::loadByName(User::class);
        yield NamedResourceAuthorization::create($this, Action::CREATE(), $allUsersResource, false);

        $userAuthorizations = [
            NamedResourceAuthorization::create($this, Action::VIEW(), $allUsersResource, false),
            NamedResourceAuthorization::create($this, Action::EDIT(), $allUsersResource, false),
            NamedResourceAuthorization::create($this, Action::DELETE(), $allUsersResource, false),
            NamedResourceAuthorization::create($this, Action::UNDELETE(), $allUsersResource, false),
            NamedResourceAuthorization::create($this, Action::ALLOW(), $allUsersResource, false),
        ];
        foreach ($userAuthorizations as $authorization) {
            yield $authorization;
        }

        // Inheritance
        $allUsers = User::loadList();
        foreach ($allUsers as $target) {
            /** @var User $target */
            foreach ($userAuthorizations as $userAuthorization) {
                yield UserAuthorization::createChildAuthorization($userAuthorization, $target, null, false);
            }
        }

        // Admin can create organizations
        $organizationsResource = NamedResource::loadByName(Orga_Model_Organization::class);
        yield NamedResourceAuthorization::create($this, Action::CREATE(), $organizationsResource, false);
    }

    public static function getLabel()
    {
        return __('User', 'role', 'roleAdmin');
    }
}
