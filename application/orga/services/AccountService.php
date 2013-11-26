<?php

namespace Orga\Service;

use Doctrine\ORM\EntityManager;
use Orga\Model\Account;
use Orga\Model\ACL\Role\AccountAdminRole;
use Orga_Service_OrganizationService;
use User\Domain\ACL\ACLService;
use User\Domain\UserService;

/**
 * Service de gestion d'un compte client.
 */
class AccountService
{
    /**
     * @var EntityManager
     */
    private $entityManager;

    /**
     * @var UserService
     */
    private $userService;

    /**
     * @var Orga_Service_OrganizationService
     */
    private $organizationService;

    /**
     * @var ACLService
     */
    private $aclService;

    public function __construct(
        EntityManager $entityManager,
        UserService $userService,
        Orga_Service_OrganizationService $organizationService,
        ACLService $aclService
    ) {
        $this->entityManager = $entityManager;
        $this->userService = $userService;
        $this->organizationService = $organizationService;
        $this->aclService = $aclService;
    }

    public function createUserAndAccount($userEmail, $password)
    {
        // Crée l'utilisateur
        $user = $this->userService->createUser($userEmail, $password);

        // Crée le compte
        $account = new Account($userEmail);

        // Ajout l'utilisateur comme admin du compte
        $this->aclService->addRole($user, new AccountAdminRole($user, $account));

        // Crée une organisation vide dans le compte
        $this->organizationService->createOrganization($account, $user, __('Orga', 'workspace', 'defaultLabel'));
    }
}
