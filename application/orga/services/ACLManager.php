<?php

use Doctrine\ORM\EntityManager;
use Orga\Model\ACL\Role\OrganizationAdminRole;
use Orga\Model\ACL\Role\AbstractCellRole;
use Orga\Model\ACL\Role\CellAdminRole;
use Orga\Model\ACL\Role\CellManagerRole;
use Orga\Model\ACL\Role\CellContributorRole;
use Orga\Model\ACL\Role\CellObserverRole;
use User\Domain\ACL\ACLService;
use User\Domain\ACL\Role\Role;
use User\Domain\User;
use User\Domain\UserService;

/**
 * Classe permettant de construire les ACL relatives aux éléments d'Orga.
 *
 * @author valentin.claras
 */
class Orga_Service_ACLManager
{
    /**
     * @var UserService
     */
    private $userService;

    /**
     * @var ACLService
     */
    private $aclService;

    /**
     * @var EntityManager
     */
    private $entityManager;

    /**
     * @param UserService   $userService
     * @param ACLService    $aclService
     * @param EntityManager $entityManager
     */
    public function __construct(UserService $userService, ACLService $aclService, EntityManager $entityManager)
    {
        $this->userService = $userService;
        $this->aclService = $aclService;
        $this->entityManager = $entityManager;
    }

    /**
     * Ajoute au projet donné, l'utilisateur comme administrateur.
     *
     * @throws Core_Exception_InvalidArgument email invalide
     * @param Orga_Model_Organization $organization
     * @param string                  $email
     * @param bool                    $sendMail
     */
    public function addOrganizationAdministrator(Orga_Model_Organization $organization, $email, $sendMail = true)
    {
        if (User::isEmailUsed($email)) {
            $user = User::loadByEmail($email);
        } else {
            $user = $this->userService->inviteUser($email);
        }

        $this->aclService->addRole($user, new OrganizationAdminRole($user, $organization));
        $this->entityManager->flush();

        if ($sendMail) {
            $this->userService->sendEmail(
                $user,
                __('User', 'email', 'subjectAccessRightsChange'),
                __('Orga', 'email', 'userOrganizationAdministratorRoleAdded', [
                    'ORGANIZATION' => $organization->getLabel()
                ])
            );
        }
    }

    /**
     * Retire au projet donné, l'utilisateur comme administrateur.
     *
     * @param Orga_Model_Organization $organization
     * @param Role $role
     * @param bool $sendMail
     */
    public function removeOrganizationAdministrator(Orga_Model_Organization $organization, Role $role, $sendMail = true)
    {
        $user = $role->getUser();
        $this->aclService->removeRole($user, $role);
        $this->entityManager->flush();

        if ($sendMail) {
            $this->userService->sendEmail(
                $user,
                __('User', 'email', 'subjectAccessRightsChange'),
                __('Orga', 'email', 'userOrganizationAdministratorRoleRemoved', [
                    'ORGANIZATION' => $organization->getLabel()
                ])
            );
        }
    }

    /**
     * @param Orga_Model_Cell $cell
     * @param string          $roleClass
     * @param string          $email
     * @param bool            $sendMail
     * @throws InvalidArgumentException
     * @throws Core_Exception_InvalidArgument email invalide
     */
    public function addCellRole(Orga_Model_Cell $cell, $roleClass, $email, $sendMail = true)
    {
        if (User::isEmailUsed($email)) {
            $user = User::loadByEmail($email);
        } else {
            $user = $this->userService->inviteUser($email);
        }

        if (! class_exists($roleClass)) {
            throw new InvalidArgumentException("Unknown role $roleClass");
        }

        /** @var AbstractCellRole $role */
        $role = new $roleClass($user, $cell);
        $this->aclService->addRole($user, $role);
        $this->entityManager->flush();

        if ($sendMail) {
            $this->userService->sendEmail(
                $user,
                __('User', 'email', 'subjectAccessRightsChange'),
                __('Orga', 'email', 'userRoleAdded', [
                    'CELL' => $cell->getExtendedLabel(),
                    'ROLE' => $role->getLabel(),
                ])
            );
        }
    }

    /**
     * @param User             $user
     * @param AbstractCellRole $role
     * @param bool             $sendMail
     */
    public function removeCellRole(User $user, AbstractCellRole $role, $sendMail = true)
    {
        $this->aclService->removeRole($user, $role);
        $this->entityManager->flush();

        if ($sendMail) {
            $this->userService->sendEmail(
                $user,
                __('User', 'email', 'subjectAccessRightsChange'),
                __('Orga', 'email', 'userRoleRemoved', [
                    'CELL' => $role->getCell()->getExtendedLabel(),
                    'ROLE' => $role->getLabel()
                ])
            );
        }
    }

    /**
     * @param User $user
     * @param Orga_Model_Organization $organization
     * @param array $askedRoles
     *
     * @throws Core_Exception_InvalidArgument
     * @return array ['cells' => Orga_Model_Cell[], 'access' => string]
     */
    public function getTopCellsWithAccessForOrganization(User $user, Orga_Model_Organization $organization, $askedRoles=[])
    {
        foreach ($organization->getAdminRoles() as $role) {
            if ($role->getUser() === $user) {
                return [
                    'cells' => [$organization->getGranularityByRef('global')->getCellByMembers([])],
                    'accesses' => [$role->getLabel()]
                ];
            }
        }

        $cellRoles = [CellAdminRole::class, CellManagerRole::class, CellContributorRole::class, CellObserverRole::class];
        if (empty($askedRole)) {
            $askedRoles = $cellRoles;
        }
        foreach ($askedRoles as $askedRole) {
            if (!in_array($askedRole, $cellRoles)) {
                throw new Core_Exception_InvalidArgument('Invalid role "'.$askedRoles.'" given');
            }
            $var = 'cellsWith'.$askedRole.'Access';
            $$var = [];
        }

        $cellsWithAccess = [];
        $cellsAccess = [];
        foreach ($user->getRoles() as $role) {
            foreach ($askedRoles as $askedRole) {
                /** @var AbstractCellRole $role */
                if (($role instanceof $askedRole) && ($role->getCell()->getOrganization() === $organization)) {
                    $cell = $role->getCell();
                    $var = 'cellsWith'.$askedRole.'Access';
                    foreach ($$var as $cellWithAccess) {
                        if ($cell->isChildOf($cellWithAccess)) {
                            continue 3;
                        }
                    }
                    array_push($$var, $cell);
                    $cellsWithAccess[$cell->getId()] = $cell;
                    $cellsAccess[$cell->getId()] = $role->getLabel();
                }
            }
        }

        usort(
            $cellsWithAccess,
            function (Orga_Model_Cell $a, Orga_Model_Cell $b) { return strcmp($a->getTag(), $b->getTag()); }
        );
        return ['cells' => $cellsWithAccess, 'accesses' => $cellsAccess];
    }

}
