<?php
/**
 * @package Orga
 */

use Doctrine\ORM\Event\OnFlushEventArgs;
use Doctrine\ORM\Event\PostFlushEventArgs;

/**
 * Classe permettant de construire les ACL relatives aux éléments d'Orga.
 * @author valentin.claras
 * @package Orga
 *
 */
class Orga_Service_ACLManager implements User_Service_ACL_ResourceTreeTraverser
{
    /**
     * @var User_Service_User
     */
    protected $userService;

    /**
     * @var User_Service_ACL
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
     * @var User_Model_Role[]
     */
    protected $newRoles = [];


    /**
     * @param User_Service_User $userService
     * @param User_Service_ACL $aclService
     */
    public function __construct(User_Service_User $userService, User_Service_ACL $aclService)
    {
        $this->userService = $userService;
        $this->aclService = $aclService;
    }

    /**
     * @param OnFlushEventArgs $eventArgs
     */
    public function onFlush(OnFlushEventArgs $eventArgs)
    {
        if (self::$processing === true) {
            return;
        }

        $entityManager = $eventArgs->getEntityManager();
        $unitOfWork = $entityManager->getUnitOfWork();

        // Créations
        foreach ($unitOfWork->getScheduledEntityInsertions() as $entity) {
            // Organization
            if ($entity instanceof Orga_Model_Organization) {
                $this->newOrganizations[] = $entity;
            }
            // Cell
            if ($entity instanceof Orga_Model_Cell) {
                $this->newCells[] = $entity;
            }
            // Report
            if ($entity instanceof DW_Model_Report) {
                $this->newReports[] = $entity;
            }
        }

        // Suppressions
        foreach ($unitOfWork->getScheduledEntityDeletions() as $entity) {
            // Organization
            if ($entity instanceof Orga_Model_Organization) {
                $this->processOldOrganization(User_Model_Resource_Entity::loadByEntity($entity));
            }
            // Cell
            if ($entity instanceof Orga_Model_Cell) {
                $this->processOldCell(User_Model_Resource_Entity::loadByEntity($entity));
            }
            // Report
            if ($entity instanceof DW_Model_Report) {
                $this->processOldReport(User_Model_Resource_Entity::loadByEntity($entity));
            }
        }

        if (!empty($this->newOrganizations) || !empty($this->newCells) || !empty($this->newReports)) {
            self::$changesDetected = true;
        }
    }

    /**
     * @param PostFlushEventArgs $eventArgs
     */
    public function postFlush(PostFlushEventArgs $eventArgs)
    {
        if ((self::$changesDetected === false) || (self::$processing === true)) {
            return;
        }
        self::$processing = true;


        foreach ($this->newOrganizations as $organization) {
            $this->processNewOrganization($organization);
        }
        $this->newOrganizations = [];

        foreach ($this->newCells as $cell) {
            $this->processNewCell($cell);
        }
        $this->newCells = [];

        foreach ($this->newReports as $report) {
            $this->processNewReport($report);
        }
        $this->newReports = [];


        $eventArgs->getEntityManager()->flush();

        $this->newResources = ['organization' => [], 'cell' => [], 'report' => []];
        $this->newRoles = [];

        self::$changesDetected = false;
        self::$processing = false;
    }

    /**
     * Créer la ressource et roles d'un Organization et génère les authorisations.
     *
     * @param Orga_Model_Organization $organization
     */
    protected function processNewOrganization(Orga_Model_Organization $organization)
    {
        // Création de la ressource projet donné.
        $organizationResource = new User_Model_Resource_Entity();
        $organizationResource->setEntity($organization);
        $organizationResource->save();
        $this->newResources['organization'][$organization->getId()] = $organizationResource;

        // Création du rôle administrateur du projet donné.
        $organizationAdministrator = new User_Model_Role();
        $organizationAdministrator->setRef('organizationAdministrator_'.$organization->getId());
        $organizationAdministrator->setName('organizationAdministrator');
        $organizationAdministrator->save();
        $this->newRoles[$organizationAdministrator->getRef()] = $organizationAdministrator;

        // Ajout des autorisations du rôle administrateur sur la ressource.
        $this->aclService->allow(
            $organizationAdministrator,
            User_Model_Action_Default::VIEW(),
            $organizationResource
        );
        $this->aclService->allow(
            $organizationAdministrator,
            User_Model_Action_Default::EDIT(),
            $organizationResource
        );
        $this->aclService->allow(
            $organizationAdministrator,
            User_Model_Action_Default::DELETE(),
            $organizationResource
        );
    }

    /**
     * Créer la ressource et roles d'une Cell et génère les authorisations.
     *
     * @param Orga_Model_Cell $cell
     */
    protected function processNewCell(Orga_Model_Cell $cell)
    {
        $organization = $cell->getGranularity()->getOrganization();

        if (isset($this->newResources['organization'][$organization->getId()])) {
            $organizationResource = $this->newResources['organization'][$organization->getId()];
        } else {
            $organizationResource = User_Model_Resource_Entity::loadByEntity($organization);
        }

        // Création de la ressource cellule donnée.
        $cellResource = new User_Model_Resource_Entity();
        $cellResource->setEntity($cell);
        $cellResource->save();
        $this->newResources['cell'][$cell->getId()] = $cellResource;


        // Création du rôle administrateur de la cellule donnée.
        $cellAdministrator = new User_Model_Role();
        $cellAdministrator->setRef('cellAdministrator_'.$cell->getId());
        $cellAdministrator->setName('cellAdministrator');
        $cellAdministrator->save();
        $this->newRoles[$cellAdministrator->getRef()] = $cellAdministrator;

        // Ajout des autorisations du rôle administrateur sur la ressource.
        $this->aclService->allow(
            $cellAdministrator,
            User_Model_Action_Default::VIEW(),
            $organizationResource
        );
        $this->aclService->allow(
            $cellAdministrator,
            User_Model_Action_Default::VIEW(),
            $cellResource
        );
        $this->aclService->allow(
            $cellAdministrator,
            User_Model_Action_Default::EDIT(),
            $cellResource
        );
        $this->aclService->allow(
            $cellAdministrator,
            User_Model_Action_Default::ALLOW(),
            $cellResource
        );
        $this->aclService->allow(
            $cellAdministrator,
            Orga_Action_Cell::COMMENT(),
            $cellResource
        );
        $this->aclService->allow(
            $cellAdministrator,
            Orga_Action_Cell::INPUT(),
            $cellResource
        );


        // Création du rôle contributeur de la cellule donnée.
        $cellContributor = new User_Model_Role();
        $cellContributor->setRef('cellContributor_'.$cell->getId());
        $cellContributor->setName('cellContributor');
        $cellContributor->save();
        $this->newRoles[$cellContributor->getRef()] = $cellContributor;

        // Ajout des autorisations du rôle administrateur sur la ressource.
        $this->aclService->allow(
            $cellContributor,
            User_Model_Action_Default::VIEW(),
            $organizationResource
        );
        $this->aclService->allow(
            $cellContributor,
            User_Model_Action_Default::VIEW(),
            $cellResource
        );
        $this->aclService->allow(
            $cellContributor,
            Orga_Action_Cell::COMMENT(),
            $cellResource
        );
        $this->aclService->allow(
            $cellContributor,
            Orga_Action_Cell::INPUT(),
            $cellResource
        );


        // Création du rôle observateur de la cellule donnée.
        $cellObserver = new User_Model_Role();
        $cellObserver->setRef('cellObserver_'.$cell->getId());
        $cellObserver->setName('cellObserver');
        $cellObserver->save();
        $this->newRoles[$cellObserver->getRef()] = $cellObserver;

        // Ajout des autorisations du rôle observateur sur la ressource.
        $this->aclService->allow(
            $cellObserver,
            User_Model_Action_Default::VIEW(),
            $organizationResource
        );
        $this->aclService->allow(
            $cellObserver,
            User_Model_Action_Default::VIEW(),
            $cellResource
        );
        $this->aclService->allow(
            $cellObserver,
            Orga_Action_Cell::COMMENT(),
            $cellResource
        );
    }

    /**
     * Créer la ressource d'un Report et génère les authorisations.
     *
     * @param DW_Model_Report $dWReport
     */
    protected function processNewReport(DW_Model_Report $dWReport)
    {
        // Création de la ressource Report donné.
        $reportResource = new User_Model_Resource_Entity();
        $reportResource->setEntity($dWReport);
        $reportResource->save();
        $this->newResources['report'][$dWReport->getId()] = $reportResource;


        // Cas spécifique d'un Report de Cell copié depuis le Cube d'une Granularity.
        if (Orga_Model_GranularityReport::isDWReportCopiedFromGranularityDWReport($dWReport)) {
            return;
        }


        // Vérification de l'origine du report pour déterminer l'identité de celui qui possède les droits.
        try {
            Orga_Model_GranularityReport::loadByGranularityDWReport($dWReport);
            $granularity = Orga_Model_Granularity::loadByDWCube($dWReport->getCube());
            $organizationAdministratorRoleRef = 'organizationAdministrator_'.$granularity->getOrganization()->getId();
            if (isset($this->newRoles[$organizationAdministratorRoleRef])) {
                $identity = $this->newRoles[$organizationAdministratorRoleRef];
            } else {
                $identity = User_Model_Role::loadByRef($organizationAdministratorRoleRef);
            }
        } catch (Core_Exception_NotFound $e) {
            // Le Report n'est pas issue d'un Cube de DW de Granularity.
            $identity = User_Model_User::load(Zend_Auth::getInstance()->getIdentity());
        }

        $this->aclService->allow(
            $identity,
            User_Model_Action_Default::VIEW(),
            $reportResource
        );
        $this->aclService->allow(
            $identity,
            Orga_Action_Report::EDIT(),
            $reportResource
        );
        $this->aclService->allow(
            $identity,
            User_Model_Action_Default::DELETE(),
            $reportResource
        );
    }

    /**
     * Supprime la ressource et roles d'un Organization.
     *
     * @param User_Model_Resource_Entity $organizationResource
     */
    protected function processOldOrganization(User_Model_Resource_Entity $organizationResource)
    {
        $idOrganization = $organizationResource->getEntityIdentifier();

        $organizationResource->delete();

        $this->deleteRole(User_Model_Role::loadByRef('organizationAdministrator_'.$idOrganization));
        self::$changesDetected = true;
    }

    /**
     * Supprime la ressource et roles d'une Cell.
     *
     * @param User_Model_Resource_Entity $cellResource
     */
    protected function processOldCell(User_Model_Resource_Entity $cellResource)
    {
        $idCell = $cellResource->getEntityIdentifier();

        $cellResource->delete();

        $this->deleteRole(User_Model_Role::loadByRef('cellAdministrator_'.$idCell));
        $this->deleteRole(User_Model_Role::loadByRef('cellContributor_'.$idCell));
        $this->deleteRole(User_Model_Role::loadByRef('cellObserver_'.$idCell));
        self::$changesDetected = true;
    }

    /**
     * Supprime la ressource d'un Report.
     *
     * @param User_Model_Resource_Entity $reportResource
     */
    protected function processOldReport(User_Model_Resource_Entity $reportResource)
    {
        $idReport = $reportResource->getEntityIdentifier();

        $reportResource->delete();
        self::$changesDetected = true;
    }

    /**
     * @param User_Model_Role $role
     */
    protected function deleteRole(User_Model_Role $role)
    {
        foreach ($role->getUsers() as $user) {
            $user->removeRole($role);
        }
        $role->delete();
    }


    /*
     * Hierarchie des ressources Cell.
     */

    /**
     * Trouve les ressources parent d'une ressource
     *
     * @param User_Model_Resource_Entity $resource
     *
     * @return User_Model_Resource_Entity[] Tableau indexé par l'ID de chaque ressource pour éviter les doublons
     */
    public function getAllParentResources(User_Model_Resource_Entity $resource)
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
     * @return User_Model_Resource_Entity[]
     */
    protected function getDWReportParentResources(DW_Model_Report $report)
    {
        if (Orga_Model_GranularityReport::isDWReportCopiedFromGranularityDWReport($report)) {
            $reportCell = Orga_Model_Cell::loadByDWCube($report->getCube());
            return array_merge([User_Model_Resource_Entity::loadByEntity($reportCell)], $this->getCellParentResources($reportCell));
        }
        return [];
    }

    /**
     * @param Orga_Model_Cell $cell
     * @return User_Model_Resource_Entity[]
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
                $parentCellResource = User_Model_Resource_Entity::loadByEntity($parentCell);
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
     * @param User_Model_Resource_Entity $resource
     *
     * @return User_Model_Resource_Entity[] Tableau indexé par l'ID de chaque ressource pour éviter les doublons
     */
    public function getAllChildResources(User_Model_Resource_Entity $resource)
    {
        $entity = $resource->getEntity();
        if ($entity instanceof Orga_Model_Cell) {
            return $this->getCellChildResources($entity);
        }
        return [];
    }

    /**
     * @param Orga_Model_Cell $cell
     * @return User_Model_Resource_Entity[]
     */
    protected function getCellChildResources(Orga_Model_Cell $cell)
    {
        $childResources = $this->getCellDWReportResources($cell);

        foreach ($cell->getChildCells() as $childCell) {
            if (isset($this->newResources['cell'][$childCell->getId()])) {
                $childCellResource = $this->newResources['cell'][$childCell->getId()];
            } else {
                $childCellResource = User_Model_Resource_Entity::loadByEntity($childCell);
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
     * @return User_Model_Resource_Entity[]
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
                    $dWReportResource = User_Model_Resource_Entity::loadByEntity($dWReport);
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
     * @param string $userEmail
     * @param string $functionName
     * @param array $parameters
     */
    public function createUserAndAddRole($userEmail, $functionName, $parameters)
    {
        $user = $this->userService->inviteUser(
            $userEmail
        );
        $user->addRole(User_Model_Role::loadByRef('user'));
        $this->entityManager->flush();

        call_user_func_array(['Orga_Service_ACLManager', $functionName], $parameters);
    }

    /**
     * Ajoute au projet donné, l'utilisateur comme administrateur.
     *
     * @param Orga_Model_Organization $organization
     * @param User_Model_User  $user
     * @param bool $sendMail
     */
    public function addOrganizationAdministrator(Orga_Model_Organization $organization, User_Model_User $user, $sendMail=true)
    {
        $user->addRole(User_Model_Role::loadByRef('organizationAdministrator_'.$organization->getId()));

        $globalCell = Orga_Model_Granularity::loadByRefAndOrganization('global', $organization)->getCells()[0];
        $user->addRole(
            User_Model_Role::loadByRef('cellAdministrator_'.$globalCell->getId())
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
     * @param User_Model_User $user
     * @param bool $sendMail
     */
    public function removeOrganizationAdministrator(Orga_Model_Organization $organization, User_Model_User $user, $sendMail=true)
    {
        $user->removeRole(User_Model_Role::loadByRef('organizationAdministrator_'.$organization->getId()));

        $globalCell = Orga_Model_Granularity::loadByRefAndOrganization('global', $organization)->getCellByMembers([]);
        $user->removeRole(
            User_Model_Role::loadByRef('cellAdministrator_'.$globalCell->getId())
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
     * @param User_Model_User  $user
     * @param bool $sendMail
     */
    public function addCellAdministrator(Orga_Model_Cell $cell, User_Model_User $user, $sendMail=true)
    {
        $this->addCellUser($cell, $user, User_Model_Role::loadByRef('cellAdministrator_'.$cell->getId()), $sendMail);
    }

    /**
     * Retire de la cellule donnée, l'utilisateur comme administrateur.
     *
     * @param Orga_Model_Cell $cell
     * @param User_Model_User  $user
     * @param bool $sendMail
     */
    public function removeCellAdministrator(Orga_Model_Cell $cell, User_Model_User $user, $sendMail=true)
    {
        $this->removeCellUser($cell, $user, User_Model_Role::loadByRef('cellAdministrator_'.$cell->getId()), $sendMail);
    }

    /**
     * Ajoute à la cellule donnée, l'utilisateur comme contributor.
     *
     * @param Orga_Model_Cell $cell
     * @param User_Model_User  $user
     * @param bool $sendMail
     */
    public function addCellContributor(Orga_Model_Cell $cell, User_Model_User $user, $sendMail=true)
    {
        $this->addCellUser($cell, $user, User_Model_Role::loadByRef('cellContributor_'.$cell->getId()), $sendMail);
    }

    /**
     * Retire de la cellule donnée, l'utilisateur comme contributor.
     *
     * @param Orga_Model_Cell $cell
     * @param User_Model_User  $user
     * @param bool $sendMail
     */
    public function removeCellContributor(Orga_Model_Cell $cell, User_Model_User $user, $sendMail=true)
    {
        $this->removeCellUser($cell, $user, User_Model_Role::loadByRef('cellContributor_'.$cell->getId()), $sendMail);
    }

    /**
     * Ajoute à la cellule donnée, l'utilisateur comme observateur.
     *
     * @param Orga_Model_Cell $cell
     * @param User_Model_User  $user
     * @param bool $sendMail
     */
    public function addCellObserver(Orga_Model_Cell $cell, User_Model_User $user, $sendMail=true)
    {
        $this->addCellUser($cell, $user, User_Model_Role::loadByRef('cellObserver_'.$cell->getId()), $sendMail);
    }

    /**
     * Retire de la cellule donnée, l'utilisateur comme observateur.
     *
     * @param Orga_Model_Cell $cell
     * @param User_Model_User  $user
     * @param bool $sendMail
     */
    public function removeCellObserver(Orga_Model_Cell $cell, User_Model_User $user, $sendMail=true)
    {
        $this->removeCellUser($cell, $user, User_Model_Role::loadByRef('cellObserver_'.$cell->getId()), $sendMail);
    }

    /**
     * @param Orga_Model_Cell $cell
     * @param User_Model_User $user
     * @param User_Model_Role $role
     * @param bool $sendMail
     */
    public function addCellUser(Orga_Model_Cell $cell, User_Model_User $user, User_Model_Role $role, $sendMail=true)
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
     * @param User_Model_User $user
     * @param User_Model_Role $role
     * @param bool $sendMail
     */
    public function removeCellUser(Orga_Model_Cell $cell, User_Model_User $user, User_Model_Role $role, $sendMail=true)
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