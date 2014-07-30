<?php

namespace Orga\Domain\Service;

use Core_Exception_InvalidArgument;
use Doctrine\ORM\EntityManager;
use InvalidArgumentException;
use Mnapoli\Translated\Translator;
use MyCLabs\ACL\ACL;
use Orga\Domain\Cell;
use Orga\Domain\Granularity;
use Orga\Domain\Workspace;
use MyCLabs\ACL\Model\Role;
use Orga\Domain\ACL\WorkspaceAdminRole;
use Orga\Domain\ACL\AbstractCellRole;
use User\Domain\User;
use User\Domain\UserService;

/**
 * Classe permettant de construire les ACL relatives aux éléments d'Orga.
 *
 * @author valentin.claras
 * @author matthieu.napoli
 */
class OrgaACLManager
{
    /**
     * @var UserService
     */
    private $userService;

    /**
     * @var ACL
     */
    private $acl;

    /**
     * @var EntityManager
     */
    private $entityManager;

    /**
     * @var Translator
     */
    private $translator;

    public function __construct(
        UserService $userService,
        ACL $acl,
        EntityManager $entityManager,
        Translator $translator
    ) {
        $this->userService = $userService;
        $this->acl = $acl;
        $this->entityManager = $entityManager;
        $this->translator = $translator;
    }

    /**
     * Ajoute au projet donné, l'utilisateur comme administrateur.
     *
     * @throws Core_Exception_InvalidArgument email invalide
     * @param Workspace $workspace
     * @param string $email
     * @param bool $sendMail
     */
    public function addWorkspaceAdministrator(Workspace $workspace, $email, $sendMail = true)
    {
        $user = $this->userService->getOrInvite($email);
        $this->entityManager->flush();

        $this->acl->grant($user, new WorkspaceAdminRole($user, $workspace));

        if ($sendMail) {
            $this->userService->sendEmail(
                $user,
                __('User', 'email', 'subjectAccessRightsChange'),
                __(
                    'Orga',
                    'email',
                    'userWorkspaceAdministratorRoleAdded',
                    [
                        'WORKSPACE' => $this->translator->get($workspace->getLabel())
                    ]
                )
            );
        }
    }

    /**
     * Retire au projet donné, l'utilisateur comme administrateur.
     *
     * @param Workspace $workspace
     * @param Role $role
     * @param bool $sendMail
     */
    public function removeWorkspaceAdministrator(Workspace $workspace, Role $role, $sendMail = true)
    {
        /** @var User $user */
        $user = $role->getSecurityIdentity();
        $this->acl->revoke($user, $role);

        if ($sendMail) {
            $this->userService->sendEmail(
                $user,
                __('User', 'email', 'subjectAccessRightsChange'),
                __(
                    'Orga',
                    'email',
                    'userWorkspaceAdministratorRoleRemoved',
                    [
                        'WORKSPACE' => $this->translator->get($workspace->getLabel())
                    ]
                )
            );
        }
    }

    /**
     * @param Cell $cell
     * @param string $roleClass
     * @param string $email
     * @param bool $sendMail
     * @throws InvalidArgumentException
     * @throws Core_Exception_InvalidArgument email invalide
     */
    public function addCellRole(Cell $cell, $roleClass, $email, $sendMail = true)
    {
        $user = $this->userService->getOrInvite($email);
        $this->entityManager->flush();

        if (!class_exists($roleClass)) {
            throw new InvalidArgumentException("Unknown role $roleClass");
        }

        /** @var \Orga\Domain\ACL\AbstractCellRole $role */
        $role = new $roleClass($user, $cell);
        $this->acl->grant($user, $role);

        if ($sendMail) {
            $this->userService->sendEmail(
                $user,
                __('User', 'email', 'subjectAccessRightsChange'),
                __(
                    'Orga',
                    'email',
                    'userRoleAdded',
                    [
                        'CELL' => $this->translator->get($cell->getExtendedLabel()),
                        'ROLE' => $role->getLabel(),
                    ]
                )
            );
        }
    }

    /**
     * @param User $user
     * @param AbstractCellRole $role
     * @param bool $sendMail
     */
    public function removeCellRole(User $user, AbstractCellRole $role, $sendMail = true)
    {
        $this->acl->revoke($user, $role);

        if ($sendMail) {
            $this->userService->sendEmail(
                $user,
                __('User', 'email', 'subjectAccessRightsChange'),
                __(
                    'Orga',
                    'email',
                    'userRoleRemoved',
                    [
                        'CELL' => $this->translator->get($role->getCell()->getExtendedLabel()),
                        'ROLE' => $role->getLabel()
                    ]
                )
            );
        }
    }

    /**
     * @param Granularity $granularity
     */
    public function clearCellsRolesFromGranularity(Granularity $granularity)
    {
        foreach ($granularity->getCells() as $cell) {
            foreach ($cell->getAllRoles() as $role) {
                $this->acl->revoke($role->getSecurityIdentity(), $role);
            }
        }
    }
}
