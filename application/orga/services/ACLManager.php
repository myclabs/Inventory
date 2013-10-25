<?php
/**
 * @package Orga
 */

use Doctrine\ORM\Event\OnFlushEventArgs;
use Doctrine\ORM\Event\PostFlushEventArgs;
use User\Domain\ACL\Action\DefaultAction;
use User\Domain\ACL\Role;
use User\Domain\ACL\ACLService;
use User\Domain\User;
use User\Domain\UserService;

/**
 * Classe permettant de construire les ACL relatives aux éléments d'Orga.
 * @author valentin.claras
 * @package Orga
 *
 */
class Orga_Service_ACLManager
{
    /**
     * @var UserService
     */
    protected $userService;

    /**
     * @var ACLService
     */
    protected $aclService;

    /**
     * Indique que l'Orga_ACLManager a détected des changements sur les ressources.
     *
     * @var bool
     */
    protected static $changesDetected = false;

    /**
     * Indique que l'Orga_ACLManager est en train de créer les ressources.
     *
     * @var bool
     */
    protected static $processing = false;

    /**
     * Ensemble des nouveaux Organization.
     *
     * @var Orga_Model_Organization[]
     */
    protected $newOrganizations = [];

    /**
     * Ensemble des nouveaux Cell.
     *
     * @var Orga_Model_Cell[]
     */
    protected $newCells = [];

    /**
     * Ensemble des nouveaux Report.
     *
     * @var DW_Model_Report[]
     */
    protected $newReports = [];

    /**
     * Ensemble des nouvelles Ressources.
     *
     * @var array
     */
    protected $newResources = [ 'organization' => [], 'cell' => [], 'report' => [] ];

    /**
     * Ensemble des nouveaux Role.
     *
     * @var Role[]
     */
    protected $newRoles = [];


    /**
     * @param UserService $userService
     * @param ACLService $aclService
     */
    public function __construct(UserService $userService, ACLService $aclService)
    {
        $this->userService = $userService;
        $this->aclService = $aclService;
    }


    /*
     * Hierarchie des ressources Cell.
     */

    /**
     * Trouve les ressources parent d'une ressource
     *
     * @param EntityResource $resource
     *
     * @return EntityResource[] Tableau indexé par l'ID de chaque ressource pour éviter les doublons
     */
    public function getAllParentResources(EntityResource $resource)
    {
        $entity = $resource->getEntity();
        if ($entity instanceof DW_Model_Report) {
            return $this->getDWReportParentResources($entity);
        } elseif ($entity instanceof Orga_Model_Cell) {
            return $this->getCellParentResources($entity);
        }
        return [];
    }

    /**
     * @param DW_Model_Report $report
     * @return EntityResource[]
     */
    protected function getDWReportParentResources(DW_Model_Report $report)
    {
        if (Orga_Model_GranularityReport::isDWReportCopiedFromGranularityDWReport($report)) {
            $reportCell = Orga_Model_Cell::loadByDWCube($report->getCube());
            return array_merge([EntityResource::loadByEntity($reportCell)], $this->getCellParentResources($reportCell));
        }
        return [];
    }

    /**
     * @param Orga_Model_Cell $cell
     * @return EntityResource[]
     */
    protected function getCellParentResources(Orga_Model_Cell $cell)
    {
        $parentResources = [];

        try {
            // Si la cellule a été supprimée, il n'y a plus de parents
            $parentCells = $cell->getParentCells();
        } catch (Core_Exception_NotFound $e) {
            return [];
        }

        foreach ($parentCells as $parentCell) {
            if (isset($this->newResources['cell'][$parentCell->getId()])) {
                $parentCellResource = $this->newResources['cell'][$parentCell->getId()];
            } else {
                $parentCellResource = EntityResource::loadByEntity($parentCell);
            }
            if ($parentCellResource !== null) {
                $parentResources[] = $parentCellResource;
            }
        }

        return $parentResources;
    }

    /**
     * Trouve les ressources filles d'une ressource
     *
     * @param EntityResource $resource
     *
     * @return EntityResource[] Tableau indexé par l'ID de chaque ressource pour éviter les doublons
     */
    public function getAllChildResources(EntityResource $resource)
    {
        $entity = $resource->getEntity();
        if ($entity instanceof Orga_Model_Cell) {
            return $this->getCellChildResources($entity);
        }
        return [];
    }

    /**
     * @param Orga_Model_Cell $cell
     * @return EntityResource[]
     */
    protected function getCellChildResources(Orga_Model_Cell $cell)
    {
        $childResources = $this->getCellDWReportResources($cell);

        foreach ($cell->getChildCells() as $childCell) {
            if (isset($this->newResources['cell'][$childCell->getId()])) {
                $childCellResource = $this->newResources['cell'][$childCell->getId()];
            } else {
                $childCellResource = EntityResource::loadByEntity($childCell);
            }
            if ($childCellResource !== null) {
                $childResources[] = $childCellResource;
            }
            $childResources = array_merge($childResources, $this->getCellDWReportResources($childCell));
        }

        return $childResources;
    }

    /**
     * @param Orga_Model_Cell $cell
     * @return EntityResource[]
     */
    protected function getCellDWReportResources(Orga_Model_Cell $cell)
    {
        if (!$cell->getGranularity()->getCellsGenerateDWCubes()) {
            return [];
        }

        $dWReportResources = [];

        foreach ($cell->getDWCube()->getReports() as $dWReport) {
            if (Orga_Model_GranularityReport::isDWReportCopiedFromGranularityDWReport($dWReport)) {
                if (isset($this->newResources['report'][$dWReport->getId()])) {
                    $dWReportResource = $this->newResources['report'][$dWReport->getId()];
                } else {
                    $dWReportResource = EntityResource::loadByEntity($dWReport);
                }
                if ($dWReportResource !== null) {
                    $dWReportResources[] = $dWReportResource;
                }
            }
        }

        return $dWReportResources;
    }


    /*
     * Gestion des roles sur les utilisateurs.
     */

    /**
     * @param User $user
     * @param string $functionName
     * @param Orga_Model_Organization|Orga_Model_Cell $orgaElement
     */
    public function createUserAndAddRole(User $user, $functionName, $orgaElement)
    {
        $user->addRole(Role::loadByRef('user'));

        call_user_func_array(['Orga_Service_ACLManager', $functionName], [$orgaElement, $user, false]);
    }

    /**
     * Ajoute au projet donné, l'utilisateur comme administrateur.
     *
     * @param Orga_Model_Organization $organization
     * @param User  $user
     * @param bool $sendMail
     */
    public function addOrganizationAdministrator(Orga_Model_Organization $organization, User $user, $sendMail=true)
    {
        $user->addRole(Role::loadByRef('organizationAdministrator_'.$organization->getId()));

        $globalCell = Orga_Model_Granularity::loadByRefAndOrganization('global', $organization)->getCells()[0];
        $user->addRole(
            Role::loadByRef('cellAdministrator_'.$globalCell->getId())
        );

        if ($sendMail === true) {
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
    public function removeOrganizationAdministrator(Orga_Model_Organization $organization, User $user, $sendMail=true)
    {
        $user->removeRole(Role::loadByRef('organizationAdministrator_'.$organization->getId()));

        $globalCell = Orga_Model_Granularity::loadByRefAndOrganization('global', $organization)->getCells()[0];
        $user->removeRole(
            Role::loadByRef('cellAdministrator_'.$globalCell->getId())
        );

        if ($sendMail === true) {
            $this->userService->sendEmail(
                $user,
                __('User', 'email', 'subjectAccessRightsChange'),
                __('Orga', 'email', 'userOrganizationAdministratorRoleRemoved', ['ORGANIZATION' => $organization->getLabel()])
            );
        }
    }

    /**
     * Ajoute à la cellule donnée, l'utilisateur comme administrateur.
     *
     * @param Orga_Model_Cell $cell
     * @param User  $user
     * @param bool $sendMail
     */
    public function addCellAdministrator(Orga_Model_Cell $cell, User $user, $sendMail=true)
    {
        $this->addCellUser($cell, $user, Role::loadByRef('cellAdministrator_'.$cell->getId()), $sendMail);
    }

    /**
     * Retire de la cellule donnée, l'utilisateur comme administrateur.
     *
     * @param Orga_Model_Cell $cell
     * @param User  $user
     * @param bool $sendMail
     */
    public function removeCellAdministrator(Orga_Model_Cell $cell, User $user, $sendMail=true)
    {
        $this->removeCellUser($cell, $user, Role::loadByRef('cellAdministrator_'.$cell->getId()), $sendMail);
    }

    /**
     * Ajoute à la cellule donnée, l'utilisateur comme contributor.
     *
     * @param Orga_Model_Cell $cell
     * @param User  $user
     * @param bool $sendMail
     */
    public function addCellContributor(Orga_Model_Cell $cell, User $user, $sendMail=true)
    {
        $this->addCellUser($cell, $user, Role::loadByRef('cellContributor_'.$cell->getId()), $sendMail);
    }

    /**
     * Retire de la cellule donnée, l'utilisateur comme contributor.
     *
     * @param Orga_Model_Cell $cell
     * @param User  $user
     * @param bool $sendMail
     */
    public function removeCellContributor(Orga_Model_Cell $cell, User $user, $sendMail=true)
    {
        $this->removeCellUser($cell, $user, Role::loadByRef('cellContributor_'.$cell->getId()), $sendMail);
    }

    /**
     * Ajoute à la cellule donnée, l'utilisateur comme observateur.
     *
     * @param Orga_Model_Cell $cell
     * @param User  $user
     * @param bool $sendMail
     */
    public function addCellObserver(Orga_Model_Cell $cell, User $user, $sendMail=true)
    {
        $this->addCellUser($cell, $user, Role::loadByRef('cellObserver_'.$cell->getId()), $sendMail);
    }

    /**
     * Retire de la cellule donnée, l'utilisateur comme observateur.
     *
     * @param Orga_Model_Cell $cell
     * @param User  $user
     * @param bool $sendMail
     */
    public function removeCellObserver(Orga_Model_Cell $cell, User $user, $sendMail=true)
    {
        $this->removeCellUser($cell, $user, Role::loadByRef('cellObserver_'.$cell->getId()), $sendMail);
    }

    /**
     * @param Orga_Model_Cell $cell
     * @param User $user
     * @param Role $role
     * @param bool $sendMail
     */
    public function addCellUser(Orga_Model_Cell $cell, User $user, Role $role, $sendMail=true)
    {
        $user->addRole($role);

        if($sendMail) {
            $this->userService->sendEmail(
                $user,
                __('User', 'email', 'subjectAccessRightsChange'),
                __('Orga', 'email', 'userRoleAdded',
                    [
                        'CELL' => $cell->getLabelExtended(),
                        'ROLE' => __('Orga', 'role', $role->getName())
                    ]
                )
            );
        }
    }

    /**
     * @param Orga_Model_Cell $cell
     * @param User $user
     * @param Role $role
     * @param bool $sendMail
     */
    public function removeCellUser(Orga_Model_Cell $cell, User $user, Role $role, $sendMail=true)
    {
        $user->removeRole($role);

        if ($sendMail) {
            $this->userService->sendEmail(
                $user,
                __('User', 'email', 'subjectAccessRightsChange'),
                __('Orga', 'email', 'userRoleRemoved',
                    [
                        'CELL' => $cell->getLabelExtended(),
                        'ROLE' => __('Orga', 'role', $role->getName())
                    ]
                )
            );
        }
    }

}