<?php

namespace User\Domain\ACL\Role;

use Orga\Model\ACL\Action\CellAction;
use Orga\Model\ACL\CellAuthorization;
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
        foreach ($allUsers as $organization) {
            /** @var User $organization */
            foreach ($userAuthorizations as $userAuthorization) {
                UserAuthorization::createChildAuthorization($userAuthorization, $organization);
            }
        }

        // Admin can create, view, edit, delete, undelete, allow all organizations
        $allOrganizationsResource = NamedResource::loadByName(Orga_Model_Organization::class);
        NamedResourceAuthorization::create($this, Action::CREATE(), $allOrganizationsResource);
        $organizationAuthorizations = NamedResourceAuthorization::createMany($this, $allOrganizationsResource, [
            Action::VIEW(),
            Action::EDIT(),
            Action::DELETE(),
            Action::UNDELETE(),
            Action::ALLOW(),
        ]);

        // Inheritance
        $allOrganizations = Orga_Model_Organization::loadList();
        foreach ($allOrganizations as $organization) {
            /** @var Orga_Model_Organization $organization */
            foreach ($organizationAuthorizations as $organizationAuthorization) {
                UserAuthorization::createChildAuthorization($organizationAuthorization, $organization);
            }

            // Cellule global
            $globalCell = $organization->getGranularityByRef('global')->getCellByMembers([]);
            $cellAuthorizations = CellAuthorization::createMany($this, $globalCell, [
                Action::VIEW(),
                Action::EDIT(),
                Action::ALLOW(),
                CellAction::COMMENT(),
                CellAction::INPUT(),
                CellAction::VIEW_REPORTS(),
            ]);

            // Cellules filles
            foreach ($globalCell->getChildCells() as $childCell) {
                foreach ($cellAuthorizations as $authorization) {
                    CellAuthorization::createChildAuthorization($authorization, $childCell);
                }
            }
        }
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
        foreach ($allUsers as $organization) {
            /** @var User $organization */
            foreach ($userAuthorizations as $userAuthorization) {
                yield UserAuthorization::createChildAuthorization($userAuthorization, $organization, null, false);
            }
        }

        // Admin can create, view, edit, delete, undelete, allow all organizations
        $allOrganizationsResource = NamedResource::loadByName(Orga_Model_Organization::class);
        yield NamedResourceAuthorization::create($this, Action::CREATE(), $allOrganizationsResource, false);

        $organizationAuthorizations = [
            NamedResourceAuthorization::create($this, Action::VIEW(), $allOrganizationsResource, false),
            NamedResourceAuthorization::create($this, Action::EDIT(), $allOrganizationsResource, false),
            NamedResourceAuthorization::create($this, Action::DELETE(), $allOrganizationsResource, false),
            NamedResourceAuthorization::create($this, Action::UNDELETE(), $allOrganizationsResource, false),
            NamedResourceAuthorization::create($this, Action::ALLOW(), $allOrganizationsResource, false),
        ];
        foreach ($organizationAuthorizations as $authorization) {
            yield $authorization;
        }

        // Inheritance
        $allOrganizations = Orga_Model_Organization::loadList();
        foreach ($allOrganizations as $organization) {
            /** @var Orga_Model_Organization $organization */
            foreach ($organizationAuthorizations as $organizationAuthorization) {
                yield UserAuthorization::createChildAuthorization($organizationAuthorization, $organization, null, false);
            }

            // Admin sur la cellule globale
            $globalCell = $organization->getGranularityByRef('global')->getCellByMembers([]);

            $cellAuths = [
                CellAuthorization::create($this, Action::VIEW(), $globalCell, false),
                CellAuthorization::create($this, Action::EDIT(), $globalCell, false),
                CellAuthorization::create($this, Action::ALLOW(), $globalCell, false),
                CellAuthorization::create($this, CellAction::COMMENT(), $globalCell, false),
                CellAuthorization::create($this, CellAction::INPUT(), $globalCell, false),
                CellAuthorization::create($this, CellAction::VIEW_REPORTS(), $globalCell, false),
            ];
            foreach ($cellAuths as $authorization) {
                yield $authorization;
            }

            // Cellules filles
            foreach ($globalCell->getChildCells() as $childCell) {
                foreach ($cellAuths as $authorization) {
                    yield CellAuthorization::createChildAuthorization($authorization, $childCell, null, false);
                }
            }
        }
    }

    public static function getLabel()
    {
        return __('User', 'role', 'roleAdmin');
    }
}
