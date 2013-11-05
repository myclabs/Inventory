<?php

use Doctrine\ORM\EntityManager;
use Orga\Model\ACL\Role\AbstractCellRole;
use Orga\Model\ACL\Role\OrganizationAdminRole;
use User\Domain\ACL\Role;
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
     * @var EntityManager
     */
    private $entityManager;

    /**
     * @param UserService $userService
     * @param EntityManager $entityManager
     */
    public function __construct(UserService $userService, EntityManager $entityManager)
    {
        $this->userService = $userService;
        $this->entityManager = $entityManager;
    }

    /**
     * Ajoute au projet donné, l'utilisateur comme administrateur.
     *
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

        $user->addRole(new OrganizationAdminRole($user, $organization));
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
        $user->removeRole($role);
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
        $user->addRole($role);
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
}
