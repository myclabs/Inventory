<?php

namespace Account\Application\Service;

use Account\Domain\AccountRepository;
use Account\Domain\ACL\AccountAdminRole;
use Doctrine\ORM\EntityManager;
use MyCLabs\ACL\ACLManager;
use MyCLabs\ACL\Model\Role;
use User\Domain\UserService;

/**
 * Gère les roles d'un compte.
 *
 * @author matthieu.napoli
 */
class AccountRoleManager
{
    /**
     * @var EntityManager
     */
    private $entityManager;

    /**
     * @var AccountRepository
     */
    private $accountRepository;

    /**
     * @var ACLManager
     */
    private $aclManager;

    /**
     * @var UserService
     */
    private $userService;

    public function __construct(
        EntityManager $entityManager,
        AccountRepository $accountRepository,
        ACLManager $aclManager,
        UserService $userService
    ) {
        $this->entityManager = $entityManager;
        $this->accountRepository = $accountRepository;
        $this->aclManager = $aclManager;
        $this->userService = $userService;
    }

    public function addAdminRole($accountId, $email)
    {
        $account = $this->accountRepository->get($accountId);
        $user = $this->userService->getOrInvite($email);
        $this->entityManager->flush();

        $this->aclManager->grant($user, new AccountAdminRole($user, $account));
    }

    public function removeRole($roleId)
    {
        /** @var Role $role */
        $role = $this->entityManager->find(Role::class, $roleId);

        $this->aclManager->unGrant($role->getSecurityIdentity(), $role);
    }
}
