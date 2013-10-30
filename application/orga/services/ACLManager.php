<?php

use Orga\Model\ACL\Role\OrganizationAdminRole;
use User\Domain\ACL\Role;
use User\Domain\ACL\ACLService;
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
     * @param UserService $userService
     * @param ACLService $aclService
     */
    public function __construct(UserService $userService, ACLService $aclService)
    {
        $this->userService = $userService;
        $this->aclService = $aclService;
    }

    /**
     * Ajoute au projet donné, l'utilisateur comme administrateur.
     *
     * @param Orga_Model_Organization $organization
     * @param User  $user
     * @param bool $sendMail
     */
    public function addOrganizationAdministrator(Orga_Model_Organization $organization, User $user, $sendMail = true)
    {
        $user->addRole(new OrganizationAdminRole($user, $organization));

        if ($sendMail) {
            $this->userService->sendEmail(
                $user,
                __('User', 'email', 'subjectAccessRightsChange'),
                __('Orga', 'email', 'userOrganizationAdministratorRoleAdded',
                    [
                        'ORGANIZATION' => $organization->getLabel()
                    ]
                )
            );
        }
    }

    /**
     * Retire au projet donné, l'utilisateur comme administrateur.
     *
     * @param Orga_Model_Organization $organization
     * @param User $user
     * @param bool $sendMail
     */
    public function removeOrganizationAdministrator(Orga_Model_Organization $organization, User $user, $sendMail = true)
    {
        foreach ($user->getRoles() as $role) {
            if ($role instanceof OrganizationAdminRole && $role->getOrganization() === $organization) {
                $user->removeRole($role);
                break;
            }
        }

        if ($sendMail) {
            $this->userService->sendEmail(
                $user,
                __('User', 'email', 'subjectAccessRightsChange'),
                __('Orga', 'email', 'userOrganizationAdministratorRoleRemoved',
                    ['ORGANIZATION' => $organization->getLabel()])
            );
        }
    }
}