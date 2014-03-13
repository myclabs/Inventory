<?php

namespace User\Domain\ACL;

use Doctrine\ORM\EntityManager;
use MyCLabs\ACL\Model\Actions;
use MyCLabs\ACL\Model\Authorization;
use MyCLabs\ACL\Model\Resource;
use MyCLabs\ACL\Model\Role;
use Orga\Model\ACL\Action\CellAction;
use Orga\Model\ACL\CellAuthorization;
use Orga\Model\ACL\OrganizationAuthorization;
use Orga_Model_Organization;
use User\Domain\User;

/**
 * Application administrator.
 */
class AdminRole extends Role
{
    /**
     * @param EntityManager $entityManager
     * @return Authorization[]
     */
    public function createAuthorizations(EntityManager $entityManager)
    {
        $rootAuthorization = Authorization::create($this, new Actions([
            Actions::CREATE,
            Actions::VIEW,
            Actions::EDIT,
            Actions::DELETE,
            Actions::UNDELETE,
            Actions::ALLOW,
        ]), Resource::fromEntityClass(User::class));

        // Cascade authorizations
        $usersRepository = $entityManager->getRepository(User::class);
        $authorizations = [$rootAuthorization];
        foreach ($usersRepository->findAll() as $user) {
            $authorizations[] = $rootAuthorization->createChildAuthorization(Resource::fromEntity($user));
        }

        return $authorizations;
    }

    /**
     * @todo Delete
     */
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
        foreach ($allUsers as $user) {
            /** @var User $user */
            foreach ($userAuthorizations as $userAuthorization) {
                yield UserAuthorization::createChildAuthorization($userAuthorization, $user, null, false);
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
                yield OrganizationAuthorization::createChildAuthorization(
                    $organizationAuthorization,
                    $organization,
                    null,
                    false
                );
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
