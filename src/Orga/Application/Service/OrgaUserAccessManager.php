<?php

namespace Orga\Application\Service;

use Core_Exception_InvalidArgument;
use Core_Model_Query;
use MyCLabs\ACL\ACL;
use Orga\Domain\Axis;
use Orga\Domain\Cell;
use Orga\Domain\Granularity;
use Orga\Domain\Workspace;
use User\Domain\ACL\Actions;
use Orga\Domain\ACL\CellAdminRole;
use Orga\Domain\ACL\CellManagerRole;
use Orga\Domain\ACL\CellContributorRole;
use Orga\Domain\ACL\CellObserverRole;
use User\Domain\User;

/**
 * @author valentin.claras
 */
class OrgaUserAccessManager
{
    /**
     * @var ACL
     */
    private $acl;


    /**
     * @param ACL $acl
     */
    public function __construct(ACL $acl) {
        $this->acl = $acl;
    }


    /**
     * @param User $user
     * @param Workspace $workspace
     * @param array $askedRoles
     * @throws Core_Exception_InvalidArgument
     * @return array ['cells' => Cell[], 'access' => string]
     */
    public function getTopCellsWithAccessForWorkspace(
        User $user,
        Workspace $workspace,
        $askedRoles = []
    ) {
        // Si l'utilisateur peut éditer le Workspace, la plus haute cellule est la cellule globale.
        if ($this->acl->isAllowed($user, Actions::EDIT, $workspace)) {
            return [
                'cells' => [$workspace->getGranularityByRef('global')->getCellByMembers([])],
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
        // Vérification des roles demandés et préparation du tableau pour chaque role.
        foreach ($askedRoles as $askedRole) {
            if (!in_array($askedRole, $cellRoles)) {
                throw new Core_Exception_InvalidArgument('Invalid role "' . $askedRoles . '" given');
            }
            $var = 'cellsWith' . $askedRole . 'Access';
            $$var = [];
        }

        $cellsWithAccess = [];
        $cellsAccess = [];
        // Recherche des cellules au travers des roles de l'utilisateur.
        foreach ($user->getRoles() as $role) {
            foreach ($askedRoles as $askedRole) {
                /** @var \Orga\Domain\ACL\AbstractCellRole $role */
                if (($role instanceof $askedRole) && ($role->getCell()->getWorkspace() === $workspace)) {
                    $cell = $role->getCell();
                    $var = 'cellsWith' . $askedRole . 'Access';
                    /** @var Cell $cellWithAccess */
                    foreach ($$var as $cellWithAccess) {
                        // Suppression des cellules reliées hiérarchiquement.
                        if ($cell->isChildOf($cellWithAccess)) {
                            continue 3;
                        } else if ($cellWithAccess->isChildOf($cell)) {
                            unset($cellsWithAccess[$cellWithAccess->getId()]);
                            unset($cellsAccess[$cellWithAccess->getId()]);
                        }
                    }
                    array_push($$var, $cell);
                    $cellsWithAccess[$cell->getId()] = $cell;
                    $cellsAccess[$cell->getId()] = $role->getLabel();
                }
            }
        }

        // Tri des cellules.
        usort(
            $cellsWithAccess,
            function (Cell $a, Cell $b) {
                return strcmp($a->getTag(), $b->getTag());
            }
        );
        return ['cells' => $cellsWithAccess, 'accesses' => $cellsAccess];
    }

    /**
     * @param User $user
     * @param Workspace $workspace
     * @return Granularity[]
     */
    public function getGranularitiesCanEdit(User $user, Workspace $workspace)
    {
        /** @var Granularity[] $granularitiesCanEdit */
        $granularitiesCanEdit = [];

        /** @var Cell[] $topCellsWithEditAccess */
        $topCellsWithEditAccess = $this->getTopCellsWithAccessForWorkspace(
            $user,
            $workspace,
            [CellAdminRole::class]
        )['cells'];

        foreach ($workspace->getGranularities() as $granularity) {
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
     * @param Workspace $workspace
     * @return Axis[]
     */
    public function getAxesCanEdit(User $user, Workspace $workspace)
    {
        /** @var Axis[] $axesCanEdit */
        $axesCanEdit = [];

        foreach ($workspace->getOrderedGranularities() as $granularity) {
            $aclCellQuery = new Core_Model_Query();
            $aclCellQuery->aclFilter->enabled = true;
            $aclCellQuery->aclFilter->user = $user;
            $aclCellQuery->aclFilter->action = Actions::EDIT;
            $aclCellQuery->filter->addCondition(Cell::QUERY_GRANULARITY, $granularity);

            $numberCellsUserCanEdit = Cell::countTotal($aclCellQuery);
            if ($numberCellsUserCanEdit > 0) {
                foreach ($workspace->getLastOrderedAxes() as $axis) {
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
