<?php

use Doctrine\ORM\EntityManager;
use Mnapoli\Translated\Translator;
use MyCLabs\ACL\ACL;
use User\Domain\ACL\Actions;
use MyCLabs\ACL\Model\Role;
use Orga\Model\ACL\OrganizationAdminRole;
use Orga\Model\ACL\AbstractCellRole;
use Orga\Model\ACL\CellAdminRole;
use Orga\Model\ACL\CellManagerRole;
use Orga\Model\ACL\CellContributorRole;
use Orga\Model\ACL\CellObserverRole;
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
     * @param Orga_Model_Organization $organization
     * @param string                  $email
     * @param bool                    $sendMail
     */
    public function addOrganizationAdministrator(Orga_Model_Organization $organization, $email, $sendMail = true)
    {
        $user = $this->userService->getOrInvite($email);
        $this->entityManager->flush();

        $this->acl->grant($user, new OrganizationAdminRole($user, $organization));

        if ($sendMail) {
            $this->userService->sendEmail(
                $user,
                __('User', 'email', 'subjectAccessRightsChange'),
                __('Orga', 'email', 'userOrganizationAdministratorRoleAdded', [
                    'ORGANIZATION' => $this->translator->get($organization->getLabel())
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
        /** @var User $user */
        $user = $role->getSecurityIdentity();
        $this->acl->unGrant($user, $role);

        if ($sendMail) {
            $this->userService->sendEmail(
                $user,
                __('User', 'email', 'subjectAccessRightsChange'),
                __('Orga', 'email', 'userOrganizationAdministratorRoleRemoved', [
                    'ORGANIZATION' => $this->translator->get($organization->getLabel())
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
        $user = $this->userService->getOrInvite($email);
        $this->entityManager->flush();

        if (! class_exists($roleClass)) {
            throw new InvalidArgumentException("Unknown role $roleClass");
        }

        /** @var AbstractCellRole $role */
        $role = new $roleClass($user, $cell);
        $this->acl->grant($user, $role);

        if ($sendMail) {
            $this->userService->sendEmail(
                $user,
                __('User', 'email', 'subjectAccessRightsChange'),
                __('Orga', 'email', 'userRoleAdded', [
                    'CELL' => $this->translator->get($cell->getExtendedLabel()),
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
        $this->acl->unGrant($user, $role);

        if ($sendMail) {
            $this->userService->sendEmail(
                $user,
                __('User', 'email', 'subjectAccessRightsChange'),
                __('Orga', 'email', 'userRoleRemoved', [
                    'CELL' => $this->translator->get($role->getCell()->getExtendedLabel()),
                    'ROLE' => $role->getLabel()
                ])
            );
        }
    }

    /**
     * TODO refactoriser
     *
     * @param User $user
     * @param Orga_Model_Organization $organization
     * @param array $askedRoles
     *
     * @throws Core_Exception_InvalidArgument
     * @return array ['cells' => Orga_Model_Cell[], 'access' => string]
     */
    public function getTopCellsWithAccessForOrganization(
        User $user,
        Orga_Model_Organization $organization,
        $askedRoles = []
    ) {
        if ($this->acl->isAllowed($user, Actions::EDIT, $organization)) {
            return [
                'cells' => [$organization->getGranularityByRef('global')->getCellByMembers([])],
            ];
        }

        $cellRoles = [
            CellAdminRole::class,
            CellManagerRole::class,
            CellContributorRole::class,
            CellObserverRole::class,
        ];
        if (empty($askedRoles)) {
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
            function (Orga_Model_Cell $a, Orga_Model_Cell $b) {
                return strcmp($a->getTag(), $b->getTag());
            }
        );
        return ['cells' => $cellsWithAccess, 'accesses' => $cellsAccess];
    }

    /**
     * @param User $user
     * @param Orga_Model_Organization $organization
     * @return Orga_Model_Granularity[]
     */
    public function getGranularitiesCanEdit(User $user, Orga_Model_Organization $organization)
    {
        /** @var Orga_Model_Granularity[] $granularitiesCanEdit */
        $granularitiesCanEdit = [];

        /** @var Orga_Model_Cell[] $topCellsWithEditAccess */
        $topCellsWithEditAccess = $this->getTopCellsWithAccessForOrganization(
            $user,
            $organization,
            [CellAdminRole::class]
        )['cells'];

        foreach ($organization->getGranularities() as $granularity) {
            foreach ($topCellsWithEditAccess as $cell) {
                if ($cell->getGranularity()->isBroaderThan($granularity)) {
                    $granularitiesCanEdit[] = $granularity;
                }
            }
        }

        return array_unique($granularitiesCanEdit);
    }

    /**
     * @param User $user
     * @param Orga_Model_Organization $organization
     * @return Orga_Model_Axis[]
     */
    public function getAxesCanEdit(User $user, Orga_Model_Organization $organization)
    {
        /** @var Orga_Model_Axis[] $axesCanEdit */
        $axesCanEdit = [];

        foreach ($organization->getOrderedGranularities() as $granularity) {
            $aclCellQuery = new Core_Model_Query();
            $aclCellQuery->aclFilter->enabled = true;
            $aclCellQuery->aclFilter->user = $user;
            $aclCellQuery->aclFilter->action = Actions::EDIT;
            $aclCellQuery->filter->addCondition(Orga_Model_Cell::QUERY_GRANULARITY, $granularity);

            $numberCellsUserCanEdit = Orga_Model_Cell::countTotal($aclCellQuery);
            if ($numberCellsUserCanEdit > 0) {
                foreach ($organization->getLastOrderedAxes() as $axis) {
                    if (!in_array($axis, $axesCanEdit)
                        && (!$granularity->hasAxes() || !$axis->isTransverse($granularity->getAxes()))
                    ) {
                        foreach ($granularity->getAxes() as $granularityAxis) {
                            if (!$axis->isNarrowerThan($granularityAxis)) {
                                continue 2;
                            }
                        }
                        $axesCanEdit[] = $axis;
                        foreach ($axis->getAllNarrowers() as $narrowerAxis) {
                            if (!in_array($narrowerAxis, $axesCanEdit)) {
                                $axesCanEdit[] = $narrowerAxis;
                            }
                        }
                    }
                }
            }
        }

        return array_unique($axesCanEdit);
    }
}
