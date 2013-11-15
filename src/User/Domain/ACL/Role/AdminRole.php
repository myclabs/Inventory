<?php

namespace User\Domain\ACL\Role;

use Orga\Model\ACL\Action\CellAction;
use Orga\Model\ACL\Action\OrganizationAction;
use Orga\Model\ACL\CellAuthorization;
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

            // Admin sur la cellule globale
            $globalCell = $organization->getGranularityByRef('global')->getCellByMembers([]);

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

        // Admin can create, view, edit, delete, allow on all organizations
        $organizationsResource = NamedResource::loadByName(Orga_Model_Organization::class);
        yield NamedResourceAuthorization::create($this, Action::CREATE(), $organizationsResource, false);
        $organizationAuthorizations = [
            NamedResourceAuthorization::create($this, Action::VIEW(), $organizationsResource, false),
            NamedResourceAuthorization::create($this, Action::EDIT(), $organizationsResource, false),
            NamedResourceAuthorization::create($this, Action::DELETE(), $organizationsResource, false),
            NamedResourceAuthorization::create($this, Action::UNDELETE(), $organizationsResource, false),
            NamedResourceAuthorization::create($this, Action::ALLOW(), $organizationsResource, false),
            NamedResourceAuthorization::create($this, OrganizationAction::EDIT_GRANULARITY_REPORTS(), $organizationsResource, false),
        ];
        foreach ($organizationAuthorizations as $authorization) {
            yield $authorization;
        }

        // Inheritance on organizations
        $organizations = Orga_Model_Organization::loadList();
        foreach ($organizations as $organization) {
            /** @var Orga_Model_Organization $organization */
            foreach ($organizationAuthorizations as $organizationAuthorization) {
                yield OrganizationAuthorization::createChildAuthorization($organizationAuthorization, $organization, null, false);
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
